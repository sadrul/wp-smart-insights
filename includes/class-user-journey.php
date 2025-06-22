<?php
/**
 * User Journey Tracker Class
 * 
 * Handles recording and playback of anonymous user journeys for UX optimization
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPSI_User_Journey {
    
    public function __construct() {
        add_action('wp_footer', array($this, 'inject_journey_tracking'));
        add_action('wp_ajax_wpsi_get_journey_data', array($this, 'get_journey_data'));
        add_action('wp_ajax_wpsi_play_journey', array($this, 'play_journey'));
        add_action('wp_ajax_wpsi_clear_journey_data', array($this, 'clear_journey_data'));
    }
    
    public function inject_journey_tracking() {
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
            
            var wpsiJourney = {
                events: [],
                sessionId: '<?php echo esc_js($this->generate_session_id()); ?>',
                postId: <?php echo intval($post_id); ?>,
                startTime: Date.now(),
                isRecording: true
            };
            
            // Record mouse movements (throttled)
            var mouseTimeout;
            document.addEventListener('mousemove', function(e) {
                clearTimeout(mouseTimeout);
                mouseTimeout = setTimeout(function() {
                    if (wpsiJourney.isRecording) {
                        wpsiJourney.events.push({
                            type: 'mousemove',
                            x: e.clientX,
                            y: e.clientY,
                            timestamp: Date.now() - wpsiJourney.startTime
                        });
                    }
                }, 100); // Record every 100ms
            });
            
            // Record clicks
            document.addEventListener('click', function(e) {
                if (wpsiJourney.isRecording) {
                    wpsiJourney.events.push({
                        type: 'click',
                        x: e.clientX,
                        y: e.clientY,
                        element: e.target.tagName.toLowerCase(),
                        text: e.target.textContent ? e.target.textContent.substring(0, 50) : '',
                        timestamp: Date.now() - wpsiJourney.startTime
                    });
                }
            });
            
            // Record scroll events
            var scrollTimeout;
            document.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(function() {
                    if (wpsiJourney.isRecording) {
                        wpsiJourney.events.push({
                            type: 'scroll',
                            scrollY: window.scrollY,
                            scrollPercent: Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100),
                            timestamp: Date.now() - wpsiJourney.startTime
                        });
                    }
                }, 200);
            });
            
            // Record form interactions
            document.addEventListener('focus', function(e) {
                if (wpsiJourney.isRecording && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT')) {
                    wpsiJourney.events.push({
                        type: 'focus',
                        element: e.target.tagName.toLowerCase(),
                        name: e.target.name || '',
                        timestamp: Date.now() - wpsiJourney.startTime
                    });
                }
            });
            
            // Record page visibility changes
            document.addEventListener('visibilitychange', function() {
                if (wpsiJourney.isRecording) {
                    wpsiJourney.events.push({
                        type: 'visibility',
                        visible: !document.hidden,
                        timestamp: Date.now() - wpsiJourney.startTime
                    });
                }
            });
            
            // Send journey data when user leaves or after 2 minutes
            var sendTimeout = setTimeout(sendJourneyData, 120000);
            
            window.addEventListener('beforeunload', function() {
                clearTimeout(sendTimeout);
                wpsiJourney.isRecording = false;
                sendJourneyData();
            });
            
            function sendJourneyData() {
                if (wpsiJourney.events.length === 0) {
                    return;
                }
                
                // Limit events to prevent overwhelming the server
                var eventsToSend = wpsiJourney.events.slice(-1000); // Last 1000 events
                
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        // Data sent successfully
                    }
                };
                
                var data = 'action=wpsi_save_user_journey' +
                          '&nonce=<?php echo esc_js(wp_create_nonce('wpsi_frontend_nonce')); ?>' +
                          '&post_id=' + wpsiJourney.postId +
                          '&journey_data=' + encodeURIComponent(JSON.stringify(eventsToSend));
                
                xhr.send(data);
            }
        })();
        </script>
        <?php
    }
    
    public function get_journey_data() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        $limit = intval($_POST['limit']) ?: 10;
        
        $journeys = get_post_meta($post_id, '_wpsi_user_journeys', true);
        if (!is_array($journeys)) {
            wp_send_json_success(array());
        }
        
        // Sort by timestamp (newest first)
        usort($journeys, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        // Limit results
        $journeys = array_slice($journeys, 0, $limit);
        
        wp_send_json_success($journeys);
    }
    
    public function play_journey() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $journey_data = sanitize_text_field($_POST['journey_data']);
        $events = json_decode($journey_data, true);
        
        if (!is_array($events)) {
            wp_send_json_error('Invalid journey data');
        }
        
        // Return journey data for playback
        wp_send_json_success(array(
            'events' => $events,
            'duration' => $this->calculate_journey_duration($events)
        ));
    }
    
    public function clear_journey_data() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        delete_post_meta($post_id, '_wpsi_user_journeys');
        
        wp_send_json_success();
    }
    
    public function get_journey_stats($post_id) {
        $journeys = get_post_meta($post_id, '_wpsi_user_journeys', true);
        if (!is_array($journeys)) {
            return array(
                'total_journeys' => 0,
                'avg_duration' => 0,
                'avg_events' => 0,
                'completion_rate' => 0,
                'common_paths' => array()
            );
        }
        
        $total_journeys = count($journeys);
        $total_duration = 0;
        $total_events = 0;
        $completed_journeys = 0;
        $paths = array();
        
        foreach ($journeys as $journey) {
            $events = json_decode($journey['journey_data'], true);
            if (is_array($events)) {
                $duration = $this->calculate_journey_duration($events);
                $total_duration += $duration;
                $total_events += count($events);
                
                // Check if journey was completed (scrolled to bottom or spent > 2 minutes)
                if ($duration > 120 || $this->reached_bottom($events)) {
                    $completed_journeys++;
                }
                
                // Track common paths
                $path = $this->extract_journey_path($events);
                $path_key = implode('->', $path);
                $paths[$path_key] = isset($paths[$path_key]) ? $paths[$path_key] + 1 : 1;
            }
        }
        
        // Get most common paths
        arsort($paths);
        $common_paths = array_slice($paths, 0, 5, true);
        
        return array(
            'total_journeys' => $total_journeys,
            'avg_duration' => $total_journeys > 0 ? round($total_duration / $total_journeys, 1) : 0,
            'avg_events' => $total_journeys > 0 ? round($total_events / $total_journeys, 1) : 0,
            'completion_rate' => $total_journeys > 0 ? round(($completed_journeys / $total_journeys) * 100, 1) : 0,
            'common_paths' => $common_paths
        );
    }
    
    public function generate_ux_insights($post_id) {
        $stats = $this->get_journey_stats($post_id);
        $insights = array();
        
        // Low completion rate
        if ($stats['completion_rate'] < 50) {
            $insights[] = array(
                'type' => 'warning',
                'title' => __('Low Completion Rate', 'wp-smart-insights'),
                // translators: %s is the completion rate percentage
                'message' => sprintf(__('Only %s%% of users complete their journey on this page.', 'wp-smart-insights'), $stats['completion_rate']),
                'suggestion' => __('Consider improving content engagement or reducing page length.', 'wp-smart-insights')
            );
        }
        
        // Short average duration
        if ($stats['avg_duration'] < 30) {
            $insights[] = array(
                'type' => 'info',
                'title' => __('Quick Bounces', 'wp-smart-insights'),
                // translators: %s is the average duration in seconds
                'message' => sprintf(__('Users spend an average of %s seconds on this page.', 'wp-smart-insights'), $stats['avg_duration']),
                'suggestion' => __('Consider improving the first impression or adding more engaging content.', 'wp-smart-insights')
            );
        }
        
        // High event count but low completion
        if ($stats['avg_events'] > 50 && $stats['completion_rate'] < 70) {
            $insights[] = array(
                'type' => 'info',
                'title' => __('High Interaction, Low Completion', 'wp-smart-insights'),
                'message' => __('Users are interacting but not completing their journey.', 'wp-smart-insights'),
                'suggestion' => __('Consider simplifying the user flow or adding clearer calls-to-action.', 'wp-smart-insights')
            );
        }
        
        return $insights;
    }
    
    private function calculate_journey_duration($events) {
        if (empty($events)) {
            return 0;
        }
        
        $first_event = $events[0];
        $last_event = end($events);
        
        return ($last_event['timestamp'] - $first_event['timestamp']) / 1000; // Convert to seconds
    }
    
    private function reached_bottom($events) {
        foreach ($events as $event) {
            if ($event['type'] === 'scroll' && $event['scrollPercent'] >= 90) {
                return true;
            }
        }
        return false;
    }
    
    private function extract_journey_path($events) {
        $path = array();
        $last_action = '';
        
        foreach ($events as $event) {
            $action = '';
            
            switch ($event['type']) {
                case 'click':
                    $action = 'clicked_' . $event['element'];
                    break;
                case 'focus':
                    $action = 'focused_' . $event['element'];
                    break;
                case 'scroll':
                    if ($event['scrollPercent'] > 50) {
                        $action = 'scrolled_deep';
                    }
                    break;
            }
            
            if ($action && $action !== $last_action) {
                $path[] = $action;
                $last_action = $action;
            }
        }
        
        return array_slice($path, 0, 10); // Limit to first 10 actions
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
    
    public function render_journey_player($journey_data) {
        ?>
        <div class="wpsi-journey-player">
            <div class="wpsi-player-controls">
                <button type="button" class="button" id="wpsi-play-journey"><?php _e('Play Journey', 'wp-smart-insights'); ?></button>
                <button type="button" class="button" id="wpsi-pause-journey"><?php _e('Pause', 'wp-smart-insights'); ?></button>
                <button type="button" class="button" id="wpsi-reset-journey"><?php _e('Reset', 'wp-smart-insights'); ?></button>
                <div class="wpsi-speed-control">
                    <label><?php _e('Speed:', 'wp-smart-insights'); ?></label>
                    <select id="wpsi-journey-speed">
                        <option value="0.5">0.5x</option>
                        <option value="1" selected>1x</option>
                        <option value="2">2x</option>
                        <option value="5">5x</option>
                    </select>
                </div>
            </div>
            
            <div class="wpsi-player-timeline">
                <div class="wpsi-timeline-bar">
                    <div class="wpsi-timeline-progress"></div>
                </div>
                <div class="wpsi-timeline-info">
                    <span class="wpsi-current-time">0:00</span>
                    <span class="wpsi-total-time">0:00</span>
                </div>
            </div>
            
            <div class="wpsi-journey-events">
                <h4><?php _e('Journey Events', 'wp-smart-insights'); ?></h4>
                <div class="wpsi-events-list"></div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var journeyPlayer = {
                events: <?php echo json_encode($journey_data); ?>,
                currentIndex: 0,
                isPlaying: false,
                speed: 1,
                cursor: null
            };
            
            // Initialize player
            function initPlayer() {
                journeyPlayer.cursor = $('<div class="wpsi-journey-cursor"></div>');
                $('body').append(journeyPlayer.cursor);
                
                updateTimeline();
                renderEventsList();
            }
            
            // Play journey
            function playJourney() {
                if (journeyPlayer.isPlaying) return;
                
                journeyPlayer.isPlaying = true;
                $('#wpsi-play-journey').prop('disabled', true);
                $('#wpsi-pause-journey').prop('disabled', false);
                
                playNextEvent();
            }
            
            // Play next event
            function playNextEvent() {
                if (!journeyPlayer.isPlaying || journeyPlayer.currentIndex >= journeyPlayer.events.length) {
                    pauseJourney();
                    return;
                }
                
                var event = journeyPlayer.events[journeyPlayer.currentIndex];
                
                // Animate cursor
                journeyPlayer.cursor.css({
                    left: event.x + 'px',
                    top: event.y + 'px'
                });
                
                // Handle different event types
                switch (event.type) {
                    case 'click':
                        journeyPlayer.cursor.addClass('wpsi-clicking');
                        setTimeout(function() {
                            journeyPlayer.cursor.removeClass('wpsi-clicking');
                        }, 200);
                        break;
                    case 'scroll':
                        $('html, body').animate({
                            scrollTop: event.scrollY
                        }, 500);
                        break;
                }
                
                // Update timeline
                updateTimeline();
                
                // Schedule next event
                var nextEvent = journeyPlayer.events[journeyPlayer.currentIndex + 1];
                if (nextEvent) {
                    var delay = (nextEvent.timestamp - event.timestamp) / (1000 * journeyPlayer.speed);
                    setTimeout(function() {
                        journeyPlayer.currentIndex++;
                        playNextEvent();
                    }, delay);
                } else {
                    pauseJourney();
                }
            }
            
            // Pause journey
            function pauseJourney() {
                journeyPlayer.isPlaying = false;
                $('#wpsi-play-journey').prop('disabled', false);
                $('#wpsi-pause-journey').prop('disabled', true);
            }
            
            // Reset journey
            function resetJourney() {
                pauseJourney();
                journeyPlayer.currentIndex = 0;
                journeyPlayer.cursor.css({ left: '0px', top: '0px' });
                updateTimeline();
            }
            
            // Update timeline
            function updateTimeline() {
                var progress = journeyPlayer.currentIndex / journeyPlayer.events.length * 100;
                $('.wpsi-timeline-progress').css('width', progress + '%');
                
                var currentTime = journeyPlayer.currentIndex > 0 ? 
                    Math.floor(journeyPlayer.events[journeyPlayer.currentIndex - 1].timestamp / 1000) : 0;
                var totalTime = journeyPlayer.events.length > 0 ? 
                    Math.floor(journeyPlayer.events[journeyPlayer.events.length - 1].timestamp / 1000) : 0;
                
                $('.wpsi-current-time').text(formatTime(currentTime));
                $('.wpsi-total-time').text(formatTime(totalTime));
            }
            
            // Render events list
            function renderEventsList() {
                var eventsList = $('.wpsi-events-list');
                eventsList.empty();
                
                journeyPlayer.events.forEach(function(event, index) {
                    var eventItem = $('<div class="wpsi-event-item" data-index="' + index + '"></div>');
                    eventItem.html('<span class="wpsi-event-time">' + formatTime(event.timestamp / 1000) + '</span>' +
                                 '<span class="wpsi-event-type">' + event.type + '</span>' +
                                 '<span class="wpsi-event-details">' + getEventDetails(event) + '</span>');
                    eventsList.append(eventItem);
                });
            }
            
            // Get event details
            function getEventDetails(event) {
                switch (event.type) {
                    case 'click':
                        return event.element + (event.text ? ': ' + event.text : '');
                    case 'scroll':
                        return event.scrollPercent + '%';
                    case 'focus':
                        return event.element + (event.name ? ': ' + event.name : '');
                    default:
                        return '';
                }
            }
            
            // Format time
            function formatTime(seconds) {
                var mins = Math.floor(seconds / 60);
                var secs = seconds % 60;
                return mins + ':' + (secs < 10 ? '0' : '') + secs;
            }
            
            // Event handlers
            $('#wpsi-play-journey').on('click', playJourney);
            $('#wpsi-pause-journey').on('click', pauseJourney);
            $('#wpsi-reset-journey').on('click', resetJourney);
            $('#wpsi-journey-speed').on('change', function() {
                journeyPlayer.speed = parseFloat($(this).val());
            });
            
            // Initialize
            initPlayer();
        });
        </script>
        <?php
    }
} 