<?php
/**
 * Analytics & Webhooks Admin View
 * 
 * @package WP_Smart_Insights
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Analytics & Webhooks', 'wp-smart-insights'); ?></h1>
    
    <div class="wpsi-admin-container">
        <!-- Analytics Configuration Section -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Analytics Configuration', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_analytics_enabled"><?php esc_html_e('Enable Advanced Analytics:', 'wp-smart-insights'); ?></label>
                <input type="checkbox" id="wpsi_analytics_enabled" name="wpsi_analytics_enabled" <?php checked(get_option('wpsi_analytics_enabled', true)); ?>>
                <p class="description"><?php esc_html_e('Enable advanced analytics tracking and processing.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_session_timeout"><?php esc_html_e('Session Timeout (minutes):', 'wp-smart-insights'); ?></label>
                <input type="number" id="wpsi_session_timeout" name="wpsi_session_timeout" value="<?php echo esc_attr(get_option('wpsi_session_timeout', 30)); ?>" min="5" max="1440" class="small-text">
                <p class="description"><?php esc_html_e('Time in minutes before a user session expires.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_data_retention_days"><?php esc_html_e('Data Retention (days):', 'wp-smart-insights'); ?></label>
                <input type="number" id="wpsi_data_retention_days" name="wpsi_data_retention_days" value="<?php echo esc_attr(get_option('wpsi_data_retention_days', 365)); ?>" min="30" max="2555" class="small-text">
                <p class="description"><?php esc_html_e('Number of days to keep analytics data before automatic cleanup.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_track_user_agents"><?php esc_html_e('Track User Agents:', 'wp-smart-insights'); ?></label>
                <input type="checkbox" id="wpsi_track_user_agents" name="wpsi_track_user_agents" <?php checked(get_option('wpsi_track_user_agents', true)); ?>>
                <p class="description"><?php esc_html_e('Track and store user agent information for device analytics.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_track_ip_addresses"><?php esc_html_e('Track IP Addresses:', 'wp-smart-insights'); ?></label>
                <input type="checkbox" id="wpsi_track_ip_addresses" name="wpsi_track_ip_addresses" <?php checked(get_option('wpsi_track_ip_addresses', false)); ?>>
                <p class="description"><?php esc_html_e('Track IP addresses for geographic analytics (privacy compliant).', 'wp-smart-insights'); ?></p>
            </div>
            
            <button type="button" id="wpsi_save_analytics_config" class="button button-primary"><?php esc_html_e('Save Analytics Configuration', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Webhook Configuration Section -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Webhook Configuration', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_webhook_enabled"><?php esc_html_e('Enable Webhooks:', 'wp-smart-insights'); ?></label>
                <input type="checkbox" id="wpsi_webhook_enabled" name="wpsi_webhook_enabled" <?php checked(get_option('wpsi_webhook_enabled', false)); ?>>
                <p class="description"><?php esc_html_e('Enable webhook notifications for real-time data integration.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_webhook_url"><?php esc_html_e('Webhook URL:', 'wp-smart-insights'); ?></label>
                <input type="url" id="wpsi_webhook_url" name="wpsi_webhook_url" value="<?php echo esc_attr(get_option('wpsi_webhook_url', '')); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('URL to send webhook notifications to.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_webhook_secret"><?php esc_html_e('Webhook Secret:', 'wp-smart-insights'); ?></label>
                <input type="password" id="wpsi_webhook_secret" name="wpsi_webhook_secret" value="<?php echo esc_attr(get_option('wpsi_webhook_secret', '')); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('Secret key for webhook authentication (optional).', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label><?php esc_html_e('Webhook Events:', 'wp-smart-insights'); ?></label>
                <div class="wpsi-checkbox-group">
                    <label><input type="checkbox" name="wpsi_webhook_events[]" value="page_view" <?php checked(in_array('page_view', get_option('wpsi_webhook_events', array()))); ?>> <?php esc_html_e('Page Views', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_webhook_events[]" value="click" <?php checked(in_array('click', get_option('wpsi_webhook_events', array()))); ?>> <?php esc_html_e('Clicks', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_webhook_events[]" value="scroll" <?php checked(in_array('scroll', get_option('wpsi_webhook_events', array()))); ?>> <?php esc_html_e('Scroll Events', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_webhook_events[]" value="form_submit" <?php checked(in_array('form_submit', get_option('wpsi_webhook_events', array()))); ?>> <?php esc_html_e('Form Submissions', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_webhook_events[]" value="conversion" <?php checked(in_array('conversion', get_option('wpsi_webhook_events', array()))); ?>> <?php esc_html_e('Conversions', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_webhook_events[]" value="session_start" <?php checked(in_array('session_start', get_option('wpsi_webhook_events', array()))); ?>> <?php esc_html_e('Session Start', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_webhook_events[]" value="session_end" <?php checked(in_array('session_end', get_option('wpsi_webhook_events', array()))); ?>> <?php esc_html_e('Session End', 'wp-smart-insights'); ?></label>
                </div>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_webhook_timeout"><?php esc_html_e('Webhook Timeout (seconds):', 'wp-smart-insights'); ?></label>
                <input type="number" id="wpsi_webhook_timeout" name="wpsi_webhook_timeout" value="<?php echo esc_attr(get_option('wpsi_webhook_timeout', 30)); ?>" min="5" max="300" class="small-text">
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_webhook_retry_attempts"><?php esc_html_e('Retry Attempts:', 'wp-smart-insights'); ?></label>
                <input type="number" id="wpsi_webhook_retry_attempts" name="wpsi_webhook_retry_attempts" value="<?php echo esc_attr(get_option('wpsi_webhook_retry_attempts', 3)); ?>" min="0" max="10" class="small-text">
            </div>
            
            <button type="button" id="wpsi_save_webhook_config" class="button button-primary"><?php esc_html_e('Save Webhook Configuration', 'wp-smart-insights'); ?></button>
            <button type="button" id="wpsi_test_webhook" class="button button-secondary"><?php esc_html_e('Test Webhook', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Real-time Analytics Dashboard -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Real-time Analytics', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-realtime-stats">
                <div class="wpsi-stat-box">
                    <h3><?php esc_html_e('Active Sessions', 'wp-smart-insights'); ?></h3>
                    <div class="wpsi-stat-number" id="wpsi_active_sessions">0</div>
                </div>
                
                <div class="wpsi-stat-box">
                    <h3><?php esc_html_e('Page Views Today', 'wp-smart-insights'); ?></h3>
                    <div class="wpsi-stat-number" id="wpsi_page_views_today">0</div>
                </div>
                
                <div class="wpsi-stat-box">
                    <h3><?php esc_html_e('Unique Visitors Today', 'wp-smart-insights'); ?></h3>
                    <div class="wpsi-stat-number" id="wpsi_unique_visitors_today">0</div>
                </div>
                
                <div class="wpsi-stat-box">
                    <h3><?php esc_html_e('Avg. Session Duration', 'wp-smart-insights'); ?></h3>
                    <div class="wpsi-stat-number" id="wpsi_avg_session_duration">0m</div>
                </div>
            </div>
            
            <div class="wpsi-realtime-chart">
                <h3><?php esc_html_e('Real-time Activity (Last 24 Hours)', 'wp-smart-insights'); ?></h3>
                <canvas id="wpsi_realtime_chart" width="800" height="200"></canvas>
            </div>
            
            <div class="wpsi-realtime-events">
                <h3><?php esc_html_e('Recent Events', 'wp-smart-insights'); ?></h3>
                <div id="wpsi_recent_events" class="wpsi-events-list">
                    <!-- Recent events will be loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Analytics Reports Section -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Analytics Reports', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_report_date_range"><?php esc_html_e('Date Range:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_report_date_range" name="wpsi_report_date_range">
                    <option value="today"><?php esc_html_e('Today', 'wp-smart-insights'); ?></option>
                    <option value="yesterday"><?php esc_html_e('Yesterday', 'wp-smart-insights'); ?></option>
                    <option value="last_7_days" selected><?php esc_html_e('Last 7 Days', 'wp-smart-insights'); ?></option>
                    <option value="last_30_days"><?php esc_html_e('Last 30 Days', 'wp-smart-insights'); ?></option>
                    <option value="last_90_days"><?php esc_html_e('Last 90 Days', 'wp-smart-insights'); ?></option>
                    <option value="custom"><?php esc_html_e('Custom Range', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group" id="wpsi_custom_report_range" style="display: none;">
                <label><?php esc_html_e('Custom Date Range:', 'wp-smart-insights'); ?></label>
                <input type="date" id="wpsi_report_start_date" name="wpsi_report_start_date">
                <span><?php esc_html_e('to', 'wp-smart-insights'); ?></span>
                <input type="date" id="wpsi_report_end_date" name="wpsi_report_end_date">
            </div>
            
            <button type="button" id="wpsi_generate_report" class="button button-primary"><?php esc_html_e('Generate Report', 'wp-smart-insights'); ?></button>
            
            <div id="wpsi_report_results" class="wpsi-report-results" style="display: none;">
                <h3><?php esc_html_e('Analytics Report', 'wp-smart-insights'); ?></h3>
                <div id="wpsi_report_content"></div>
            </div>
        </div>
        
        <!-- Webhook Logs Section -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Webhook Logs', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_webhook_log_filter"><?php esc_html_e('Filter by Status:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_webhook_log_filter" name="wpsi_webhook_log_filter">
                    <option value="all"><?php esc_html_e('All Logs', 'wp-smart-insights'); ?></option>
                    <option value="success"><?php esc_html_e('Successful', 'wp-smart-insights'); ?></option>
                    <option value="failed"><?php esc_html_e('Failed', 'wp-smart-insights'); ?></option>
                    <option value="pending"><?php esc_html_e('Pending', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div id="wpsi_webhook_logs">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Event', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Status', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Response', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Attempts', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Actions', 'wp-smart-insights'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wpsi_webhook_logs_body">
                        <!-- Webhook logs will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <button type="button" id="wpsi_clear_webhook_logs" class="button button-secondary"><?php esc_html_e('Clear Logs', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Data Management Section -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Data Management', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_cleanup_old_data"><?php esc_html_e('Cleanup Old Data:', 'wp-smart-insights'); ?></label>
                <button type="button" id="wpsi_cleanup_old_data" class="button button-secondary"><?php esc_html_e('Run Data Cleanup', 'wp-smart-insights'); ?></button>
                <p class="description"><?php esc_html_e('Remove analytics data older than the retention period.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_export_analytics_data"><?php esc_html_e('Export Analytics Data:', 'wp-smart-insights'); ?></label>
                <button type="button" id="wpsi_export_analytics_data" class="button button-secondary"><?php esc_html_e('Export All Data', 'wp-smart-insights'); ?></button>
                <p class="description"><?php esc_html_e('Export all analytics data for backup or migration.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_reset_analytics"><?php esc_html_e('Reset Analytics:', 'wp-smart-insights'); ?></label>
                <button type="button" id="wpsi_reset_analytics" class="button button-secondary"><?php esc_html_e('Reset All Data', 'wp-smart-insights'); ?></button>
                <p class="description"><?php esc_html_e('⚠️ Warning: This will permanently delete all analytics data.', 'wp-smart-insights'); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Show/hide custom date range for reports
    $('#wpsi_report_date_range').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#wpsi_custom_report_range').show();
        } else {
            $('#wpsi_custom_report_range').hide();
        }
    });
    
    // Save Analytics Configuration
    $('#wpsi_save_analytics_config').on('click', function() {
        var data = {
            action: 'wpsi_save_analytics_config',
            nonce: wpsi_ajax.nonce,
            analytics_enabled: $('#wpsi_analytics_enabled').is(':checked'),
            session_timeout: $('#wpsi_session_timeout').val(),
            data_retention_days: $('#wpsi_data_retention_days').val(),
            track_user_agents: $('#wpsi_track_user_agents').is(':checked'),
            track_ip_addresses: $('#wpsi_track_ip_addresses').is(':checked')
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            if (response.success) {
                alert('Analytics configuration saved successfully!');
            } else {
                alert('Error saving configuration: ' + response.data);
            }
        });
    });
    
    // Save Webhook Configuration
    $('#wpsi_save_webhook_config').on('click', function() {
        var webhook_events = $('input[name="wpsi_webhook_events[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        var data = {
            action: 'wpsi_save_webhook_config',
            nonce: wpsi_ajax.nonce,
            webhook_enabled: $('#wpsi_webhook_enabled').is(':checked'),
            webhook_url: $('#wpsi_webhook_url').val(),
            webhook_secret: $('#wpsi_webhook_secret').val(),
            webhook_events: webhook_events,
            webhook_timeout: $('#wpsi_webhook_timeout').val(),
            webhook_retry_attempts: $('#wpsi_webhook_retry_attempts').val()
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            if (response.success) {
                alert('Webhook configuration saved successfully!');
            } else {
                alert('Error saving configuration: ' + response.data);
            }
        });
    });
    
    // Test Webhook
    $('#wpsi_test_webhook').on('click', function() {
        $(this).prop('disabled', true).text('Testing...');
        
        $.post(wpsi_ajax.ajax_url, {
            action: 'wpsi_test_webhook',
            nonce: wpsi_ajax.nonce
        }, function(response) {
            $('#wpsi_test_webhook').prop('disabled', false).text('Test Webhook');
            
            if (response.success) {
                alert('Webhook test successful!');
            } else {
                alert('Webhook test failed: ' + response.data);
            }
        });
    });
    
    // Generate Analytics Report
    $('#wpsi_generate_report').on('click', function() {
        var date_range = $('#wpsi_report_date_range').val();
        var start_date = $('#wpsi_report_start_date').val();
        var end_date = $('#wpsi_report_end_date').val();
        
        $(this).prop('disabled', true).text('Generating...');
        
        var data = {
            action: 'wpsi_generate_analytics_report',
            nonce: wpsi_ajax.nonce,
            date_range: date_range,
            start_date: start_date,
            end_date: end_date
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_generate_report').prop('disabled', false).text('Generate Report');
            
            if (response.success) {
                $('#wpsi_report_content').html(response.data.html);
                $('#wpsi_report_results').show();
            } else {
                alert('Error generating report: ' + response.data);
            }
        });
    });
    
    // Load real-time analytics
    function loadRealtimeAnalytics() {
        $.post(wpsi_ajax.ajax_url, {
            action: 'wpsi_get_realtime_analytics',
            nonce: wpsi_ajax.nonce
        }, function(response) {
            if (response.success) {
                $('#wpsi_active_sessions').text(response.data.active_sessions);
                $('#wpsi_page_views_today').text(response.data.page_views_today);
                $('#wpsi_unique_visitors_today').text(response.data.unique_visitors_today);
                $('#wpsi_avg_session_duration').text(response.data.avg_session_duration);
                
                // Update chart if data available
                if (response.data.chart_data) {
                    updateRealtimeChart(response.data.chart_data);
                }
            }
        });
    }
    
    // Load webhook logs
    function loadWebhookLogs() {
        var filter = $('#wpsi_webhook_log_filter').val();
        
        $.post(wpsi_ajax.ajax_url, {
            action: 'wpsi_get_webhook_logs',
            nonce: wpsi_ajax.nonce,
            filter: filter
        }, function(response) {
            if (response.success) {
                $('#wpsi_webhook_logs_body').html(response.data.html);
            }
        });
    }
    
    // Load recent events
    function loadRecentEvents() {
        $.post(wpsi_ajax.ajax_url, {
            action: 'wpsi_get_recent_events',
            nonce: wpsi_ajax.nonce
        }, function(response) {
            if (response.success) {
                $('#wpsi_recent_events').html(response.data.html);
            }
        });
    }
    
    // Update real-time chart
    function updateRealtimeChart(data) {
        var ctx = document.getElementById('wpsi_realtime_chart').getContext('2d');
        
        if (window.wpsiChart) {
            window.wpsiChart.destroy();
        }
        
        window.wpsiChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Page Views',
                    data: data.page_views,
                    borderColor: '#0073aa',
                    backgroundColor: 'rgba(0, 115, 170, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Unique Visitors',
                    data: data.unique_visitors,
                    borderColor: '#46b450',
                    backgroundColor: 'rgba(70, 180, 80, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Data cleanup
    $('#wpsi_cleanup_old_data').on('click', function() {
        if (confirm('Are you sure you want to cleanup old analytics data?')) {
            $(this).prop('disabled', true).text('Cleaning up...');
            
            $.post(wpsi_ajax.ajax_url, {
                action: 'wpsi_cleanup_old_data',
                nonce: wpsi_ajax.nonce
            }, function(response) {
                $('#wpsi_cleanup_old_data').prop('disabled', false).text('Run Data Cleanup');
                
                if (response.success) {
                    alert('Data cleanup completed! Removed ' + response.data.removed_records + ' records.');
                } else {
                    alert('Error during cleanup: ' + response.data);
                }
            });
        }
    });
    
    // Export analytics data
    $('#wpsi_export_analytics_data').on('click', function() {
        $(this).prop('disabled', true).text('Exporting...');
        
        $.post(wpsi_ajax.ajax_url, {
            action: 'wpsi_export_analytics_data',
            nonce: wpsi_ajax.nonce
        }, function(response) {
            $('#wpsi_export_analytics_data').prop('disabled', false).text('Export All Data');
            
            if (response.success) {
                window.location.href = response.data.download_url;
            } else {
                alert('Error exporting data: ' + response.data);
            }
        });
    });
    
    // Reset analytics
    $('#wpsi_reset_analytics').on('click', function() {
        if (confirm('⚠️ WARNING: This will permanently delete ALL analytics data. This action cannot be undone. Are you absolutely sure?')) {
            if (confirm('Final confirmation: Delete all analytics data?')) {
                $(this).prop('disabled', true).text('Resetting...');
                
                $.post(wpsi_ajax.ajax_url, {
                    action: 'wpsi_reset_analytics',
                    nonce: wpsi_ajax.nonce
                }, function(response) {
                    $('#wpsi_reset_analytics').prop('disabled', false).text('Reset All Data');
                    
                    if (response.success) {
                        alert('All analytics data has been reset.');
                        location.reload();
                    } else {
                        alert('Error resetting data: ' + response.data);
                    }
                });
            }
        }
    });
    
    // Clear webhook logs
    $('#wpsi_clear_webhook_logs').on('click', function() {
        if (confirm('Are you sure you want to clear all webhook logs?')) {
            $.post(wpsi_ajax.ajax_url, {
                action: 'wpsi_clear_webhook_logs',
                nonce: wpsi_ajax.nonce
            }, function(response) {
                if (response.success) {
                    loadWebhookLogs();
                } else {
                    alert('Error clearing logs: ' + response.data);
                }
            });
        }
    });
    
    // Load data on page load
    loadRealtimeAnalytics();
    loadWebhookLogs();
    loadRecentEvents();
    
    // Refresh real-time data every 30 seconds
    setInterval(function() {
        loadRealtimeAnalytics();
        loadRecentEvents();
    }, 30000);
    
    // Refresh webhook logs when filter changes
    $('#wpsi_webhook_log_filter').on('change', loadWebhookLogs);
});
</script> 