<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('User Journeys', 'wp-smart-insights'); ?></h1>
    <p class="description"><?php esc_html_e('View recorded user interactions and analyze user behavior patterns.', 'wp-smart-insights'); ?></p>
    
    <div class="wpsi-journeys-container">
        <!-- Journey Selection -->
        <div class="wpsi-journey-selector">
            <h2><?php esc_html_e('Select User Journey', 'wp-smart-insights'); ?></h2>
            <div class="wpsi-journey-filters">
                <select id="wpsi-post-filter">
                    <option value=""><?php esc_html_e('All Posts', 'wp-smart-insights'); ?></option>
                    <?php
                    $posts_with_journeys = get_posts(array(
                        'post_type' => 'post',
                        'post_status' => 'publish',
                        'numberposts' => -1,
                        'meta_query' => array(
                            array(
                                'key' => '_wpsi_user_journeys',
                                'compare' => 'EXISTS'
                            )
                        )
                    ));
                    
                    foreach ($posts_with_journeys as $post) {
                        echo '<option value="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</option>';
                    }
                    ?>
                </select>
                
                <select id="wpsi-date-filter">
                    <option value=""><?php esc_html_e('All Dates', 'wp-smart-insights'); ?></option>
                    <option value="today"><?php esc_html_e('Today', 'wp-smart-insights'); ?></option>
                    <option value="yesterday"><?php esc_html_e('Yesterday', 'wp-smart-insights'); ?></option>
                    <option value="week"><?php esc_html_e('Last 7 Days', 'wp-smart-insights'); ?></option>
                    <option value="month"><?php esc_html_e('Last 30 Days', 'wp-smart-insights'); ?></option>
                </select>
                
                <button id="wpsi-load-journeys" class="button button-primary"><?php esc_html_e('Load Journeys', 'wp-smart-insights'); ?></button>
            </div>
        </div>
        
        <!-- Journey List -->
        <div class="wpsi-journey-list">
            <h2><?php esc_html_e('Recorded Journeys', 'wp-smart-insights'); ?></h2>
            <div id="wpsi-journeys-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date/Time', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Post', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Duration', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Actions', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Interactions', 'wp-smart-insights'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wpsi-journeys-tbody">
                        <tr>
                            <td colspan="5"><?php esc_html_e('No journeys found. Select filters and click "Load Journeys".', 'wp-smart-insights'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Journey Playback -->
        <div id="wpsi-journey-playback" class="wpsi-journey-playback" style="display: none;">
            <h2><?php esc_html_e('Journey Playback', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-playback-controls">
                <button id="wpsi-play-pause" class="button"><?php esc_html_e('Play', 'wp-smart-insights'); ?></button>
                <button id="wpsi-stop" class="button"><?php esc_html_e('Stop', 'wp-smart-insights'); ?></button>
                <button id="wpsi-reset" class="button"><?php esc_html_e('Reset', 'wp-smart-insights'); ?></button>
                
                <div class="wpsi-speed-control">
                    <label><?php esc_html_e('Speed:', 'wp-smart-insights'); ?></label>
                    <select id="wpsi-playback-speed">
                        <option value="0.5">0.5x</option>
                        <option value="1" selected>1x</option>
                        <option value="2">2x</option>
                        <option value="5">5x</option>
                    </select>
                </div>
                
                <div class="wpsi-progress">
                    <span id="wpsi-current-time">00:00</span>
                    <div class="wpsi-progress-bar">
                        <div id="wpsi-progress-fill"></div>
                    </div>
                    <span id="wpsi-total-time">00:00</span>
                </div>
            </div>
            
            <div class="wpsi-playback-container">
                <div class="wpsi-page-preview">
                    <h3><?php esc_html_e('Page Preview', 'wp-smart-insights'); ?></h3>
                    <div id="wpsi-page-content" class="wpsi-page-content">
                        <!-- Page content will be loaded here -->
                    </div>
                </div>
                
                <div class="wpsi-interaction-log">
                    <h3><?php esc_html_e('Interaction Log', 'wp-smart-insights'); ?></h3>
                    <div id="wpsi-interaction-list" class="wpsi-interaction-list">
                        <!-- Interaction events will be displayed here -->
                    </div>
                </div>
            </div>
            
            <div class="wpsi-journey-stats">
                <h3><?php esc_html_e('Journey Statistics', 'wp-smart-insights'); ?></h3>
                <div class="wpsi-stats-grid">
                    <div class="wpsi-stat-item">
                        <span class="wpsi-stat-label"><?php esc_html_e('Total Clicks', 'wp-smart-insights'); ?></span>
                        <span id="wpsi-total-clicks" class="wpsi-stat-value">0</span>
                    </div>
                    <div class="wpsi-stat-item">
                        <span class="wpsi-stat-label"><?php esc_html_e('Scroll Events', 'wp-smart-insights'); ?></span>
                        <span id="wpsi-total-scrolls" class="wpsi-stat-value">0</span>
                    </div>
                    <div class="wpsi-stat-item">
                        <span class="wpsi-stat-label"><?php esc_html_e('Form Interactions', 'wp-smart-insights'); ?></span>
                        <span id="wpsi-total-forms" class="wpsi-stat-value">0</span>
                    </div>
                    <div class="wpsi-stat-item">
                        <span class="wpsi-stat-label"><?php esc_html_e('Time on Page', 'wp-smart-insights'); ?></span>
                        <span id="wpsi-time-on-page" class="wpsi-stat-value">0s</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Loading State -->
        <div id="wpsi-journeys-loading" class="wpsi-loading" style="display: none;">
            <div class="wpsi-spinner"></div>
            <p><?php esc_html_e('Loading journeys...', 'wp-smart-insights'); ?></p>
        </div>
    </div>
</div>

<style>
.wpsi-journeys-container {
    max-width: 1400px;
    margin: 20px 0;
}

.wpsi-journey-selector {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.wpsi-journey-filters {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.wpsi-journey-filters select {
    min-width: 200px;
}

.wpsi-journey-list {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.wpsi-journey-playback {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wpsi-playback-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    flex-wrap: wrap;
}

.wpsi-speed-control {
    display: flex;
    align-items: center;
    gap: 8px;
}

.wpsi-progress {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 300px;
}

.wpsi-progress-bar {
    flex: 1;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.wpsi-progress-fill {
    height: 100%;
    background: #667eea;
    width: 0%;
    transition: width 0.3s ease;
}

.wpsi-playback-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.wpsi-page-preview, .wpsi-interaction-log {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
}

.wpsi-page-preview h3, .wpsi-interaction-log h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.wpsi-page-content {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    min-height: 400px;
    max-height: 600px;
    overflow-y: auto;
}

.wpsi-interaction-list {
    max-height: 400px;
    overflow-y: auto;
}

.wpsi-interaction-item {
    background: #fff;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 4px;
    border-left: 3px solid #667eea;
    font-size: 13px;
}

.wpsi-interaction-item.click {
    border-left-color: #28a745;
}

.wpsi-interaction-item.scroll {
    border-left-color: #ffc107;
}

.wpsi-interaction-item.form {
    border-left-color: #dc3545;
}

.wpsi-journey-stats {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
}

.wpsi-journey-stats h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.wpsi-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.wpsi-stat-item {
    background: #fff;
    padding: 15px;
    border-radius: 6px;
    text-align: center;
}

.wpsi-stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.wpsi-stat-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #667eea;
}

.wpsi-loading {
    text-align: center;
    padding: 40px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wpsi-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.wpsi-highlight {
    background: rgba(255, 193, 7, 0.3);
    border: 2px solid #ffc107;
    border-radius: 3px;
    transition: all 0.3s ease;
}
</style>

<script>
jQuery(document).ready(function($) {
    var currentJourney = null;
    var playbackInterval = null;
    var currentTime = 0;
    var isPlaying = false;
    
    $('#wpsi-load-journeys').on('click', function() {
        var postId = $('#wpsi-post-filter').val();
        var dateFilter = $('#wpsi-date-filter').val();
        
        // Show loading
        $('#wpsi-journeys-loading').show();
        
        // Load journeys
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_get_user_journeys',
                post_id: postId,
                date_filter: dateFilter,
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                $('#wpsi-journeys-loading').hide();
                
                if (response.success) {
                    displayJourneys(response.data);
                } else {
                    alert('<?php esc_html_e('Error loading journeys.', 'wp-smart-insights'); ?>');
                }
            },
            error: function() {
                $('#wpsi-journeys-loading').hide();
                alert('<?php esc_html_e('Error loading journeys.', 'wp-smart-insights'); ?>');
            }
        });
    });
    
    function displayJourneys(journeys) {
        var tbody = $('#wpsi-journeys-tbody');
        tbody.empty();
        
        if (journeys.length === 0) {
            tbody.append('<tr><td colspan="5"><?php esc_html_e('No journeys found for the selected criteria.', 'wp-smart-insights'); ?></td></tr>');
            return;
        }
        
        journeys.forEach(function(journey) {
            var row = '<tr>';
            row += '<td>' + journey.timestamp + '</td>';
            row += '<td>' + journey.post_title + '</td>';
            row += '<td>' + journey.duration + '</td>';
            row += '<td><button class="button wpsi-play-journey" data-journey-id="' + journey.id + '"><?php esc_html_e('Play', 'wp-smart-insights'); ?></button></td>';
            row += '<td>' + journey.interaction_count + '</td>';
            row += '</tr>';
            tbody.append(row);
        });
    }
    
    $(document).on('click', '.wpsi-play-journey', function() {
        var journeyId = $(this).data('journey-id');
        loadJourneyForPlayback(journeyId);
    });
    
    function loadJourneyForPlayback(journeyId) {
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_get_journey_details',
                journey_id: journeyId,
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    currentJourney = response.data;
                    setupPlayback();
                    $('#wpsi-journey-playback').show();
                } else {
                    alert('<?php esc_html_e('Error loading journey details.', 'wp-smart-insights'); ?>');
                }
            },
            error: function() {
                alert('<?php esc_html_e('Error loading journey details.', 'wp-smart-insights'); ?>');
            }
        });
    }
    
    function setupPlayback() {
        // Load page content
        $('#wpsi-page-content').html(currentJourney.page_content);
        
        // Setup interaction log
        var interactionHtml = '';
        currentJourney.interactions.forEach(function(interaction) {
            interactionHtml += '<div class="wpsi-interaction-item ' + interaction.type + '">';
            interactionHtml += '<strong>' + interaction.type.toUpperCase() + '</strong> at ' + formatTime(interaction.timestamp);
            interactionHtml += '<br>' + interaction.description;
            interactionHtml += '</div>';
        });
        $('#wpsi-interaction-list').html(interactionHtml);
        
        // Setup statistics
        $('#wpsi-total-clicks').text(currentJourney.stats.clicks);
        $('#wpsi-total-scrolls').text(currentJourney.stats.scrolls);
        $('#wpsi-total-forms').text(currentJourney.stats.forms);
        $('#wpsi-time-on-page').text(currentJourney.stats.duration + 's');
        
        // Setup progress
        $('#wpsi-total-time').text(formatTime(currentJourney.duration));
        currentTime = 0;
        updateProgress();
    }
    
    $('#wpsi-play-pause').on('click', function() {
        if (isPlaying) {
            pausePlayback();
        } else {
            startPlayback();
        }
    });
    
    $('#wpsi-stop').on('click', function() {
        stopPlayback();
    });
    
    $('#wpsi-reset').on('click', function() {
        resetPlayback();
    });
    
    function startPlayback() {
        if (!currentJourney) return;
        
        isPlaying = true;
        $('#wpsi-play-pause').text('<?php esc_html_e('Pause', 'wp-smart-insights'); ?>');
        
        var speed = parseFloat($('#wpsi-playback-speed').val());
        var interval = 1000 / speed;
        
        playbackInterval = setInterval(function() {
            currentTime += 1;
            updateProgress();
            
            if (currentTime >= currentJourney.duration) {
                stopPlayback();
            }
        }, interval);
    }
    
    function pausePlayback() {
        isPlaying = false;
        $('#wpsi-play-pause').text('<?php esc_html_e('Play', 'wp-smart-insights'); ?>');
        clearInterval(playbackInterval);
    }
    
    function stopPlayback() {
        pausePlayback();
        currentTime = currentJourney.duration;
        updateProgress();
    }
    
    function resetPlayback() {
        pausePlayback();
        currentTime = 0;
        updateProgress();
    }
    
    function updateProgress() {
        var progress = (currentTime / currentJourney.duration) * 100;
        $('#wpsi-progress-fill').css('width', progress + '%');
        $('#wpsi-current-time').text(formatTime(currentTime));
    }
    
    function formatTime(seconds) {
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        return mins.toString().padStart(2, '0') + ':' + secs.toString().padStart(2, '0');
    }
});
</script> 