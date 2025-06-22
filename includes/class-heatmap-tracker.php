<?php
/**
 * Heatmap Tracker Class
 * 
 * Handles tracking of user interactions (clicks, scrolls, hovers) and generates visual heatmaps
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPSI_Heatmap_Tracker {
    
    public function __construct() {
        add_action('wp_footer', array($this, 'inject_tracking_code'));
        add_action('admin_footer', array($this, 'inject_admin_preview'));
        add_action('wp_ajax_wpsi_get_heatmap_data', array($this, 'get_heatmap_data'));
        add_action('wp_ajax_wpsi_clear_heatmap_data', array($this, 'clear_heatmap_data'));
    }
    
    public function inject_tracking_code() {
        // Only track if enabled and not in admin
        if (!get_option('wpsi_tracking_enabled', false) || is_admin()) {
            return;
        }
        
        // Check privacy compliance
        if (get_option('wpsi_privacy_compliant', true) && !$this->has_user_consent()) {
            return;
        }
        
        $post_id = get_the_ID();
        if (!$post_id) {
            return;
        }
        
        ?>
        <script type="text/javascript">
        (function() {
            'use strict';
            
            var wpsiData = {
                clicks: [],
                scrolls: [],
                hovers: [],
                sessionId: '<?php echo esc_js($this->generate_session_id()); ?>',
                postId: <?php echo intval($post_id); ?>,
                startTime: Date.now()
            };
            
            // Track clicks
            document.addEventListener('click', function(e) {
                var rect = e.target.getBoundingClientRect();
                wpsiData.clicks.push({
                    x: Math.round((e.clientX - rect.left) / rect.width * 100),
                    y: Math.round((e.clientY - rect.top) / rect.height * 100),
                    element: e.target.tagName.toLowerCase(),
                    text: e.target.textContent ? e.target.textContent.substring(0, 50) : '',
                    timestamp: Date.now() - wpsiData.startTime
                });
            });
            
            // Track scroll depth
            var maxScroll = 0;
            document.addEventListener('scroll', function() {
                var scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
                if (scrollPercent > maxScroll) {
                    maxScroll = scrollPercent;
                    wpsiData.scrolls.push({
                        depth: scrollPercent,
                        timestamp: Date.now() - wpsiData.startTime
                    });
                }
            });
            
            // Track hovers (debounced)
            var hoverTimeout;
            document.addEventListener('mouseover', function(e) {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(function() {
                    var rect = e.target.getBoundingClientRect();
                    wpsiData.hovers.push({
                        x: Math.round((e.clientX - rect.left) / rect.width * 100),
                        y: Math.round((e.clientY - rect.top) / rect.height * 100),
                        element: e.target.tagName.toLowerCase(),
                        duration: 1000
                    });
                }, 500);
            });
            
            // Send data when user leaves or after 30 seconds
            var sendTimeout = setTimeout(sendHeatmapData, 30000);
            
            window.addEventListener('beforeunload', function() {
                clearTimeout(sendTimeout);
                sendHeatmapData();
            });
            
            function sendHeatmapData() {
                if (wpsiData.clicks.length === 0 && wpsiData.scrolls.length === 0 && wpsiData.hovers.length === 0) {
                    return;
                }
                
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        // Data sent successfully
                    }
                };
                
                var data = 'action=wpsi_save_heatmap_data' +
                          '&nonce=<?php echo esc_js(wp_create_nonce('wpsi_frontend_nonce')); ?>' +
                          '&post_id=' + wpsiData.postId +
                          '&click_data=' + encodeURIComponent(JSON.stringify(wpsiData.clicks)) +
                          '&scroll_data=' + encodeURIComponent(JSON.stringify(wpsiData.scrolls)) +
                          '&hover_data=' + encodeURIComponent(JSON.stringify(wpsiData.hovers));
                
                xhr.send(data);
            }
        })();
        </script>
        <?php
    }
    
    public function inject_admin_preview() {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'smart-insights-content-intelligence-ux-heatmap-heatmaps') === false) {
            return;
        }
        
        ?>
        <div id="wpsi-heatmap-preview" style="display: none;">
            <div class="wpsi-heatmap-overlay">
                <div class="wpsi-heatmap-controls">
                    <button type="button" class="button" id="wpsi-show-clicks"><?php esc_html_e('Show Clicks', 'wp-smart-insights'); ?></button>
                    <button type="button" class="button" id="wpsi-show-hovers"><?php esc_html_e('Show Hovers', 'wp-smart-insights'); ?></button>
                    <button type="button" class="button" id="wpsi-show-scroll"><?php esc_html_e('Show Scroll Depth', 'wp-smart-insights'); ?></button>
                    <button type="button" class="button" id="wpsi-clear-heatmap"><?php esc_html_e('Clear', 'wp-smart-insights'); ?></button>
                </div>
                <canvas id="wpsi-heatmap-canvas"></canvas>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var heatmapCanvas = document.getElementById('wpsi-heatmap-canvas');
            var ctx = heatmapCanvas.getContext('2d');
            var heatmapData = [];
            
            // Initialize heatmap
            function initHeatmap() {
                var container = $('.wpsi-heatmap-container');
                if (container.length === 0) return;
                
                var rect = container[0].getBoundingClientRect();
                heatmapCanvas.width = rect.width;
                heatmapCanvas.height = rect.height;
                heatmapCanvas.style.position = 'absolute';
                heatmapCanvas.style.top = rect.top + 'px';
                heatmapCanvas.style.left = rect.left + 'px';
                heatmapCanvas.style.pointerEvents = 'none';
                heatmapCanvas.style.zIndex = '9999';
                
                document.body.appendChild(heatmapCanvas);
            }
            
            // Draw heatmap
            function drawHeatmap(data, type) {
                if (!heatmapCanvas) return;
                
                ctx.clearRect(0, 0, heatmapCanvas.width, heatmapCanvas.height);
                
                data.forEach(function(point) {
                    var x = (point.x / 100) * heatmapCanvas.width;
                    var y = (point.y / 100) * heatmapCanvas.height;
                    
                    var gradient = ctx.createRadialGradient(x, y, 0, x, y, 50);
                    
                    if (type === 'clicks') {
                        gradient.addColorStop(0, 'rgba(255, 0, 0, 0.8)');
                        gradient.addColorStop(1, 'rgba(255, 0, 0, 0)');
                    } else if (type === 'hovers') {
                        gradient.addColorStop(0, 'rgba(0, 255, 0, 0.6)');
                        gradient.addColorStop(1, 'rgba(0, 255, 0, 0)');
                    } else if (type === 'scroll') {
                        gradient.addColorStop(0, 'rgba(0, 0, 255, 0.7)');
                        gradient.addColorStop(1, 'rgba(0, 0, 255, 0)');
                    }
                    
                    ctx.fillStyle = gradient;
                    ctx.fillRect(x - 25, y - 25, 50, 50);
                });
            }
            
            // Event handlers
            $('#wpsi-show-clicks').on('click', function() {
                $.post(ajaxurl, {
                    action: 'wpsi_get_heatmap_data',
                    nonce: '<?php echo esc_js(wp_create_nonce('wpsi_nonce')); ?>',
                    post_id: $('#wpsi-post-select').val(),
                    type: 'clicks'
                }, function(response) {
                    if (response.success) {
                        drawHeatmap(response.data, 'clicks');
                    }
                });
            });
            
            $('#wpsi-show-hovers').on('click', function() {
                $.post(ajaxurl, {
                    action: 'wpsi_get_heatmap_data',
                    nonce: '<?php echo esc_js(wp_create_nonce('wpsi_nonce')); ?>',
                    post_id: $('#wpsi-post-select').val(),
                    type: 'hovers'
                }, function(response) {
                    if (response.success) {
                        drawHeatmap(response.data, 'hovers');
                    }
                });
            });
            
            $('#wpsi-show-scroll').on('click', function() {
                $.post(ajaxurl, {
                    action: 'wpsi_get_heatmap_data',
                    nonce: '<?php echo esc_js(wp_create_nonce('wpsi_nonce')); ?>',
                    post_id: $('#wpsi-post-select').val(),
                    type: 'scroll'
                }, function(response) {
                    if (response.success) {
                        drawHeatmap(response.data, 'scroll');
                    }
                });
            });
            
            $('#wpsi-clear-heatmap').on('click', function() {
                if (heatmapCanvas) {
                    ctx.clearRect(0, 0, heatmapCanvas.width, heatmapCanvas.height);
                }
            });
            
            // Initialize when page loads
            setTimeout(initHeatmap, 1000);
        });
        </script>
        <?php
    }
    
    public function get_heatmap_data() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        $type = sanitize_text_field($_POST['type']);
        
        $heatmap_data = get_post_meta($post_id, '_wpsi_heatmap_data', true);
        if (!is_array($heatmap_data)) {
            wp_send_json_success(array());
        }
        
        $aggregated_data = array();
        
        foreach ($heatmap_data as $session) {
            if ($type === 'clicks' && !empty($session['clicks'])) {
                $clicks = json_decode($session['clicks'], true);
                if (is_array($clicks)) {
                    $aggregated_data = array_merge($aggregated_data, $clicks);
                }
            } elseif ($type === 'hovers' && !empty($session['hovers'])) {
                $hovers = json_decode($session['hovers'], true);
                if (is_array($hovers)) {
                    $aggregated_data = array_merge($aggregated_data, $hovers);
                }
            } elseif ($type === 'scroll' && !empty($session['scrolls'])) {
                $scrolls = json_decode($session['scrolls'], true);
                if (is_array($scrolls)) {
                    $aggregated_data = array_merge($aggregated_data, $scrolls);
                }
            }
        }
        
        wp_send_json_success($aggregated_data);
    }
    
    public function clear_heatmap_data() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        delete_post_meta($post_id, '_wpsi_heatmap_data');
        
        wp_send_json_success();
    }
    
    public function get_heatmap_stats($post_id) {
        $heatmap_data = get_post_meta($post_id, '_wpsi_heatmap_data', true);
        if (!is_array($heatmap_data)) {
            return array(
                'total_sessions' => 0,
                'total_clicks' => 0,
                'total_hovers' => 0,
                'avg_scroll_depth' => 0,
                'hotspots' => array()
            );
        }
        
        $total_clicks = 0;
        $total_hovers = 0;
        $scroll_depths = array();
        $click_positions = array();
        
        foreach ($heatmap_data as $session) {
            if (!empty($session['clicks'])) {
                $clicks = json_decode($session['clicks'], true);
                if (is_array($clicks)) {
                    $total_clicks += count($clicks);
                    foreach ($clicks as $click) {
                        $key = round($click['x'] / 10) . ',' . round($click['y'] / 10);
                        $click_positions[$key] = isset($click_positions[$key]) ? $click_positions[$key] + 1 : 1;
                    }
                }
            }
            
            if (!empty($session['hovers'])) {
                $hovers = json_decode($session['hovers'], true);
                if (is_array($hovers)) {
                    $total_hovers += count($hovers);
                }
            }
            
            if (!empty($session['scrolls'])) {
                $scrolls = json_decode($session['scrolls'], true);
                if (is_array($scrolls)) {
                    $max_depth = 0;
                    foreach ($scrolls as $scroll) {
                        $max_depth = max($max_depth, $scroll['depth']);
                    }
                    $scroll_depths[] = $max_depth;
                }
            }
        }
        
        // Find hotspots (areas with most clicks)
        arsort($click_positions);
        $hotspots = array_slice($click_positions, 0, 5, true);
        
        return array(
            'total_sessions' => count($heatmap_data),
            'total_clicks' => $total_clicks,
            'total_hovers' => $total_hovers,
            'avg_scroll_depth' => !empty($scroll_depths) ? round(array_sum($scroll_depths) / count($scroll_depths), 1) : 0,
            'hotspots' => $hotspots
        );
    }
    
    public function generate_ux_warnings($post_id) {
        $stats = $this->get_heatmap_stats($post_id);
        $warnings = array();
        
        // High bounce areas
        if ($stats['avg_scroll_depth'] < 30) {
            $warnings[] = array(
                'type' => 'warning',
                'message' => __('Low scroll depth detected. Content may not be engaging enough.', 'wp-smart-insights'),
                'suggestion' => __('Consider adding more engaging content or improving the introduction.', 'wp-smart-insights')
            );
        }
        
        // Non-clicked CTAs
        if ($stats['total_clicks'] < 5 && $stats['total_sessions'] > 10) {
            $warnings[] = array(
                'type' => 'warning',
                'message' => __('Low click engagement. CTAs may not be prominent enough.', 'wp-smart-insights'),
                'suggestion' => __('Consider making call-to-action buttons more visible or compelling.', 'wp-smart-insights')
            );
        }
        
        // Too much scrolling without engagement
        if ($stats['avg_scroll_depth'] > 80 && $stats['total_clicks'] < 10) {
            $warnings[] = array(
                'type' => 'info',
                'message' => __('Users are scrolling but not clicking. Content may be too long.', 'wp-smart-insights'),
                'suggestion' => __('Consider breaking content into shorter sections or adding more interactive elements.', 'wp-smart-insights')
            );
        }
        
        return $warnings;
    }
    
    private function generate_session_id() {
        return wp_generate_uuid4();
    }
    
    private function has_user_consent() {
        if (!get_option('wpsi_cookie_consent', true)) {
            return true;
        }
        
        // Check for consent cookie
        return isset($_COOKIE['wpsi_consent']) && $_COOKIE['wpsi_consent'] === '1';
    }
} 