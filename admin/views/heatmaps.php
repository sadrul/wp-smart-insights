<?php
/**
 * Heatmaps View
 * 
 * Displays user interaction heatmaps and statistics
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get posts with heatmap data
$posts_with_heatmaps = get_posts(array(
    'post_type' => array('post', 'page'),
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => array(
        array('key' => '_wpsi_heatmap_data', 'compare' => 'EXISTS')
    )
));

$selected_post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$selected_post = null;

if ($selected_post_id > 0) {
    $selected_post = get_post($selected_post_id);
}
?>

<div class="wrap">
    <h1><?php _e('Smart Insights - Heatmaps', 'wp-smart-insights'); ?></h1>
    
    <div class="wpsi-heatmaps-container">
        <!-- Post Selection -->
        <div class="wpsi-section">
            <h2><?php _e('Select Page/Post', 'wp-smart-insights'); ?></h2>
            
            <form method="get" action="">
                <input type="hidden" name="page" value="wp-smart-insights-heatmaps" />
                <select name="post_id" id="wpsi-post-select">
                    <option value=""><?php _e('-- Select a page or post --', 'wp-smart-insights'); ?></option>
                    <?php foreach ($posts_with_heatmaps as $post): ?>
                        <?php
                        $heatmap_data = get_post_meta($post->ID, '_wpsi_heatmap_data', true);
                        $session_count = is_array($heatmap_data) ? count($heatmap_data) : 0;
                        ?>
                        <option value="<?php echo $post->ID; ?>" <?php selected($selected_post_id, $post->ID); ?>>
                            <?php echo esc_html($post->post_title); ?> (<?php echo $session_count; ?> sessions)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="<?php _e('Load Heatmap', 'wp-smart-insights'); ?>" />
            </form>
        </div>
        
        <?php if ($selected_post): ?>
            <?php
            $heatmap_data = get_post_meta($selected_post->ID, '_wpsi_heatmap_data', true);
            $heatmap_tracker = new WPSI_Heatmap_Tracker();
            $stats = $heatmap_tracker->get_heatmap_stats($selected_post->ID);
            $warnings = $heatmap_tracker->generate_ux_warnings($selected_post->ID);
            ?>
            
            <!-- Heatmap Statistics -->
            <div class="wpsi-section">
                <h2><?php _e('Heatmap Statistics', 'wp-smart-insights'); ?></h2>
                
                <div class="wpsi-stats-grid">
                    <div class="wpsi-stat-card">
                        <div class="wpsi-stat-icon dashicons dashicons-groups"></div>
                        <div class="wpsi-stat-content">
                            <h3><?php echo $stats['total_sessions']; ?></h3>
                            <p><?php _e('Total Sessions', 'wp-smart-insights'); ?></p>
                        </div>
                    </div>
                    
                    <div class="wpsi-stat-card">
                        <div class="wpsi-stat-icon dashicons dashicons-mouse"></div>
                        <div class="wpsi-stat-content">
                            <h3><?php echo $stats['total_clicks']; ?></h3>
                            <p><?php _e('Total Clicks', 'wp-smart-insights'); ?></p>
                        </div>
                    </div>
                    
                    <div class="wpsi-stat-card">
                        <div class="wpsi-stat-icon dashicons dashicons-move"></div>
                        <div class="wpsi-stat-content">
                            <h3><?php echo $stats['total_hovers']; ?></h3>
                            <p><?php _e('Total Hovers', 'wp-smart-insights'); ?></p>
                        </div>
                    </div>
                    
                    <div class="wpsi-stat-card">
                        <div class="wpsi-stat-icon dashicons dashicons-arrow-down-alt"></div>
                        <div class="wpsi-stat-content">
                            <h3><?php echo $stats['avg_scroll_depth']; ?>%</h3>
                            <p><?php _e('Avg Scroll Depth', 'wp-smart-insights'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- UX Warnings -->
            <?php if (!empty($warnings)): ?>
                <div class="wpsi-section">
                    <h2><?php _e('UX Insights & Warnings', 'wp-smart-insights'); ?></h2>
                    
                    <div class="wpsi-warnings">
                        <?php foreach ($warnings as $warning): ?>
                            <div class="wpsi-warning-item <?php echo esc_attr($warning['type']); ?>">
                                <div class="wpsi-warning-icon">
                                    <?php if ($warning['type'] === 'warning'): ?>
                                        <span class="dashicons dashicons-warning"></span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-info"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="wpsi-warning-content">
                                    <h4><?php echo esc_html($warning['message']); ?></h4>
                                    <p><?php echo esc_html($warning['suggestion']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Heatmap Visualization -->
            <div class="wpsi-section">
                <h2><?php _e('Heatmap Visualization', 'wp-smart-insights'); ?></h2>
                
                <div class="wpsi-heatmap-controls">
                    <button type="button" class="button" id="wpsi-show-clicks"><?php _e('Show Clicks', 'wp-smart-insights'); ?></button>
                    <button type="button" class="button" id="wpsi-show-hovers"><?php _e('Show Hovers', 'wp-smart-insights'); ?></button>
                    <button type="button" class="button" id="wpsi-show-scroll"><?php _e('Show Scroll Depth', 'wp-smart-insights'); ?></button>
                    <button type="button" class="button" id="wpsi-clear-heatmap"><?php _e('Clear', 'wp-smart-insights'); ?></button>
                    <button type="button" class="button button-secondary" id="wpsi-clear-data"><?php _e('Clear All Data', 'wp-smart-insights'); ?></button>
                </div>
                
                <div class="wpsi-heatmap-container">
                    <div class="wpsi-heatmap-preview">
                        <iframe src="<?php echo get_permalink($selected_post->ID); ?>" width="100%" height="600" frameborder="0"></iframe>
                        <canvas id="wpsi-heatmap-canvas"></canvas>
                    </div>
                </div>
                
                <div class="wpsi-heatmap-legend">
                    <div class="wpsi-legend-item">
                        <div class="wpsi-legend-color clicks"></div>
                        <span><?php _e('Clicks', 'wp-smart-insights'); ?></span>
                    </div>
                    <div class="wpsi-legend-item">
                        <div class="wpsi-legend-color hovers"></div>
                        <span><?php _e('Hovers', 'wp-smart-insights'); ?></span>
                    </div>
                    <div class="wpsi-legend-item">
                        <div class="wpsi-legend-color scroll"></div>
                        <span><?php _e('Scroll Depth', 'wp-smart-insights'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Hotspots -->
            <?php if (!empty($stats['hotspots'])): ?>
                <div class="wpsi-section">
                    <h2><?php _e('Click Hotspots', 'wp-smart-insights'); ?></h2>
                    
                    <div class="wpsi-hotspots">
                        <?php foreach ($stats['hotspots'] as $position => $count): ?>
                            <?php
                            $coords = explode(',', $position);
                            $x = intval($coords[0]) * 10;
                            $y = intval($coords[1]) * 10;
                            ?>
                            <div class="wpsi-hotspot-item">
                                <div class="wpsi-hotspot-position">
                                    <strong><?php _e('Position:', 'wp-smart-insights'); ?></strong> <?php echo $x; ?>%, <?php echo $y; ?>%
                                </div>
                                <div class="wpsi-hotspot-count">
                                    <strong><?php _e('Clicks:', 'wp-smart-insights'); ?></strong> <?php echo $count; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="wpsi-section">
                <div class="wpsi-no-data">
                    <div class="wpsi-no-data-icon dashicons dashicons-chart-area"></div>
                    <h3><?php _e('No Heatmap Data Available', 'wp-smart-insights'); ?></h3>
                    <p><?php _e('Select a page or post from the dropdown above to view heatmap data. Make sure tracking is enabled and users have visited the page.', 'wp-smart-insights'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wp-smart-insights-settings'); ?>" class="button button-primary">
                        <?php _e('Check Settings', 'wp-smart-insights'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.wpsi-heatmaps-container {
    max-width: 1200px;
}

.wpsi-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wpsi-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
}

.wpsi-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.wpsi-stat-card {
    background: #f9f9f9;
    border: 1px solid #e1e1e1;
    border-radius: 6px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.wpsi-stat-icon {
    font-size: 2em;
    color: #0073aa;
}

.wpsi-stat-content h3 {
    margin: 0;
    font-size: 1.5em;
    color: #333;
}

.wpsi-stat-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.9em;
}

.wpsi-warnings {
    display: grid;
    gap: 15px;
}

.wpsi-warning-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid;
}

.wpsi-warning-item.warning {
    background: #fff3cd;
    border-color: #ffc107;
}

.wpsi-warning-item.info {
    background: #d1ecf1;
    border-color: #17a2b8;
}

.wpsi-warning-icon {
    font-size: 1.5em;
    color: #ffc107;
}

.wpsi-warning-item.info .wpsi-warning-icon {
    color: #17a2b8;
}

.wpsi-warning-content h4 {
    margin: 0 0 8px 0;
    color: #333;
}

.wpsi-warning-content p {
    margin: 0;
    color: #666;
}

.wpsi-heatmap-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.wpsi-heatmap-container {
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
}

.wpsi-heatmap-preview {
    position: relative;
}

.wpsi-heatmap-preview iframe {
    display: block;
}

#wpsi-heatmap-canvas {
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
    z-index: 10;
}

.wpsi-heatmap-legend {
    display: flex;
    gap: 20px;
    margin-top: 15px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
}

.wpsi-legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.wpsi-legend-color {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.wpsi-legend-color.clicks {
    background: rgba(255, 0, 0, 0.8);
}

.wpsi-legend-color.hovers {
    background: rgba(0, 255, 0, 0.6);
}

.wpsi-legend-color.scroll {
    background: rgba(0, 0, 255, 0.7);
}

.wpsi-hotspots {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.wpsi-hotspot-item {
    background: #f9f9f9;
    border: 1px solid #e1e1e1;
    border-radius: 6px;
    padding: 15px;
}

.wpsi-hotspot-position,
.wpsi-hotspot-count {
    margin-bottom: 8px;
}

.wpsi-no-data {
    text-align: center;
    padding: 60px 20px;
}

.wpsi-no-data-icon {
    font-size: 4em;
    color: #ddd;
    margin-bottom: 20px;
}

.wpsi-no-data h3 {
    margin: 0 0 15px 0;
    color: #666;
}

.wpsi-no-data p {
    margin: 0 0 25px 0;
    color: #999;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

@media (max-width: 768px) {
    .wpsi-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .wpsi-heatmap-controls {
        flex-direction: column;
    }
    
    .wpsi-heatmap-legend {
        flex-direction: column;
        gap: 10px;
    }
    
    .wpsi-hotspots {
        grid-template-columns: 1fr;
    }
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var heatmapCanvas = document.getElementById('wpsi-heatmap-canvas');
    var ctx = heatmapCanvas.getContext('2d');
    
    // Initialize heatmap
    function initHeatmap() {
        if (!heatmapCanvas) return;
        
        var container = $('.wpsi-heatmap-preview');
        var iframe = container.find('iframe');
        
        iframe.on('load', function() {
            var rect = container[0].getBoundingClientRect();
            heatmapCanvas.width = rect.width;
            heatmapCanvas.height = rect.height;
            heatmapCanvas.style.position = 'absolute';
            heatmapCanvas.style.top = '0';
            heatmapCanvas.style.left = '0';
            heatmapCanvas.style.pointerEvents = 'none';
            heatmapCanvas.style.zIndex = '10';
        });
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
            nonce: '<?php echo wp_create_nonce('wpsi_nonce'); ?>',
            post_id: <?php echo $selected_post_id ?: 0; ?>,
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
            nonce: '<?php echo wp_create_nonce('wpsi_nonce'); ?>',
            post_id: <?php echo $selected_post_id ?: 0; ?>,
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
            nonce: '<?php echo wp_create_nonce('wpsi_nonce'); ?>',
            post_id: <?php echo $selected_post_id ?: 0; ?>,
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
    
    $('#wpsi-clear-data').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to clear all heatmap data for this page?', 'wp-smart-insights'); ?>')) {
            $.post(ajaxurl, {
                action: 'wpsi_clear_heatmap_data',
                nonce: '<?php echo wp_create_nonce('wpsi_nonce'); ?>',
                post_id: <?php echo $selected_post_id ?: 0; ?>
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
    });
    
    // Initialize when page loads
    setTimeout(initHeatmap, 1000);
});
</script> 