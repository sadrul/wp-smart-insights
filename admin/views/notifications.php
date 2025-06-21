<?php
/**
 * Notifications Admin View
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
    <h1><?php _e('Notifications', 'smart-insights-content-intelligence-ux-heatmap'); ?></h1>
    
    <div class="wpsi-admin-container">
        <!-- Email Configuration Section -->
        <div class="wpsi-card">
            <h2><?php _e('Email Configuration', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_notification_email"><?php _e('Notification Email:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="email" id="wpsi_notification_email" name="wpsi_notification_email" value="<?php echo esc_attr(get_option('wpsi_notification_email', get_option('admin_email'))); ?>" class="regular-text">
                <p class="description"><?php _e('Email address to receive notifications.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_host"><?php _e('SMTP Host (Optional):', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="text" id="wpsi_smtp_host" name="wpsi_smtp_host" value="<?php echo esc_attr(get_option('wpsi_smtp_host', '')); ?>" class="regular-text">
                <p class="description"><?php _e('Leave empty to use WordPress default email.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_port"><?php _e('SMTP Port:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="number" id="wpsi_smtp_port" name="wpsi_smtp_port" value="<?php echo esc_attr(get_option('wpsi_smtp_port', '587')); ?>" class="small-text">
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_username"><?php _e('SMTP Username:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="text" id="wpsi_smtp_username" name="wpsi_smtp_username" value="<?php echo esc_attr(get_option('wpsi_smtp_username', '')); ?>" class="regular-text">
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_password"><?php _e('SMTP Password:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="password" id="wpsi_smtp_password" name="wpsi_smtp_password" value="<?php echo esc_attr(get_option('wpsi_smtp_password', '')); ?>" class="regular-text">
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_encryption"><?php _e('Encryption:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <select id="wpsi_smtp_encryption" name="wpsi_smtp_encryption">
                    <option value="tls" <?php selected(get_option('wpsi_smtp_encryption', 'tls'), 'tls'); ?>><?php _e('TLS', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="ssl" <?php selected(get_option('wpsi_smtp_encryption', 'tls'), 'ssl'); ?>><?php _e('SSL', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="none" <?php selected(get_option('wpsi_smtp_encryption', 'tls'), 'none'); ?>><?php _e('None', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_save_email_config" class="button button-primary"><?php _e('Save Email Configuration', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
            <button type="button" id="wpsi_test_email" class="button button-secondary"><?php _e('Send Test Email', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
        </div>
        
        <!-- Notification Settings Section -->
        <div class="wpsi-card">
            <h2><?php _e('Notification Settings', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            
            <div class="wpsi-form-group">
                <label><?php _e('Enable Notifications:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <div class="wpsi-checkbox-group">
                    <label><input type="checkbox" name="wpsi_notifications[]" value="daily_report" <?php checked(in_array('daily_report', get_option('wpsi_notifications', array()))); ?>> <?php _e('Daily Analytics Report', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="weekly_report" <?php checked(in_array('weekly_report', get_option('wpsi_notifications', array()))); ?>> <?php _e('Weekly Analytics Report', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="low_engagement" <?php checked(in_array('low_engagement', get_option('wpsi_notifications', array()))); ?>> <?php _e('Low Engagement Alerts', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="seo_issues" <?php checked(in_array('seo_issues', get_option('wpsi_notifications', array()))); ?>> <?php _e('SEO Issues Alerts', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="content_suggestions" <?php checked(in_array('content_suggestions', get_option('wpsi_notifications', array()))); ?>> <?php _e('Content Improvement Suggestions', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="system_alerts" <?php checked(in_array('system_alerts', get_option('wpsi_notifications', array()))); ?>> <?php _e('System Alerts', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                </div>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_notification_frequency"><?php _e('Notification Frequency:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <select id="wpsi_notification_frequency" name="wpsi_notification_frequency">
                    <option value="immediate" <?php selected(get_option('wpsi_notification_frequency', 'daily'), 'immediate'); ?>><?php _e('Immediate', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="hourly" <?php selected(get_option('wpsi_notification_frequency', 'daily'), 'hourly'); ?>><?php _e('Hourly', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="daily" <?php selected(get_option('wpsi_notification_frequency', 'daily'), 'daily'); ?>><?php _e('Daily', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="weekly" <?php selected(get_option('wpsi_notification_frequency', 'daily'), 'weekly'); ?>><?php _e('Weekly', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_engagement_threshold"><?php _e('Low Engagement Threshold (%):', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="number" id="wpsi_engagement_threshold" name="wpsi_engagement_threshold" value="<?php echo esc_attr(get_option('wpsi_engagement_threshold', 30)); ?>" min="1" max="100" class="small-text">
                <p class="description"><?php _e('Percentage below which to trigger low engagement alerts.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_seo_threshold"><?php _e('SEO Score Threshold:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="number" id="wpsi_seo_threshold" name="wpsi_seo_threshold" value="<?php echo esc_attr(get_option('wpsi_seo_threshold', 70)); ?>" min="1" max="100" class="small-text">
                <p class="description"><?php _e('SEO score below which to trigger alerts.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
            </div>
            
            <button type="button" id="wpsi_save_notification_settings" class="button button-primary"><?php _e('Save Notification Settings', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
        </div>
        
        <!-- Custom Notification Templates -->
        <div class="wpsi-card">
            <h2><?php _e('Custom Notification Templates', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_email_subject_prefix"><?php _e('Email Subject Prefix:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="text" id="wpsi_email_subject_prefix" name="wpsi_email_subject_prefix" value="<?php echo esc_attr(get_option('wpsi_email_subject_prefix', '[Smart Insights]')); ?>" class="regular-text">
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_daily_report_template"><?php _e('Daily Report Template:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <textarea id="wpsi_daily_report_template" name="wpsi_daily_report_template" rows="8" class="large-text"><?php echo esc_textarea(get_option('wpsi_daily_report_template', $this->get_default_daily_template())); ?></textarea>
                <p class="description"><?php _e('Available variables: {site_name}, {date}, {page_views}, {unique_visitors}, {avg_engagement}, {top_posts}', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_weekly_report_template"><?php _e('Weekly Report Template:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <textarea id="wpsi_weekly_report_template" name="wpsi_weekly_report_template" rows="8" class="large-text"><?php echo esc_textarea(get_option('wpsi_weekly_report_template', $this->get_default_weekly_template())); ?></textarea>
                <p class="description"><?php _e('Available variables: {site_name}, {week_start}, {week_end}, {total_page_views}, {total_visitors}, {avg_engagement}, {top_posts}, {trends}', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_alert_template"><?php _e('Alert Template:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <textarea id="wpsi_alert_template" name="wpsi_alert_template" rows="6" class="large-text"><?php echo esc_textarea(get_option('wpsi_alert_template', $this->get_default_alert_template())); ?></textarea>
                <p class="description"><?php _e('Available variables: {site_name}, {alert_type}, {alert_message}, {affected_posts}, {recommendations}', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
            </div>
            
            <button type="button" id="wpsi_save_templates" class="button button-primary"><?php _e('Save Templates', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
            <button type="button" id="wpsi_reset_templates" class="button button-secondary"><?php _e('Reset to Defaults', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
        </div>
        
        <!-- Notification History -->
        <div class="wpsi-card">
            <h2><?php _e('Notification History', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_history_filter"><?php _e('Filter by Type:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <select id="wpsi_history_filter" name="wpsi_history_filter">
                    <option value="all"><?php _e('All Notifications', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="daily_report"><?php _e('Daily Reports', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="weekly_report"><?php _e('Weekly Reports', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="alert"><?php _e('Alerts', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="system"><?php _e('System Notifications', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                </select>
            </div>
            
            <div id="wpsi_notification_history">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'smart-insights-content-intelligence-ux-heatmap'); ?></th>
                            <th><?php _e('Type', 'smart-insights-content-intelligence-ux-heatmap'); ?></th>
                            <th><?php _e('Subject', 'smart-insights-content-intelligence-ux-heatmap'); ?></th>
                            <th><?php _e('Status', 'smart-insights-content-intelligence-ux-heatmap'); ?></th>
                            <th><?php _e('Actions', 'smart-insights-content-intelligence-ux-heatmap'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wpsi_notification_history_body">
                        <!-- Notification history will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <button type="button" id="wpsi_clear_history" class="button button-secondary"><?php _e('Clear History', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
        </div>
        
        <!-- Manual Notification Sending -->
        <div class="wpsi-card">
            <h2><?php _e('Send Manual Notification', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_manual_notification_type"><?php _e('Notification Type:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <select id="wpsi_manual_notification_type" name="wpsi_manual_notification_type">
                    <option value="daily_report"><?php _e('Daily Report', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="weekly_report"><?php _e('Weekly Report', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="custom_message"><?php _e('Custom Message', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group" id="wpsi_custom_message_group" style="display: none;">
                <label for="wpsi_custom_message_subject"><?php _e('Subject:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="text" id="wpsi_custom_message_subject" name="wpsi_custom_message_subject" class="regular-text">
            </div>
            
            <div class="wpsi-form-group" id="wpsi_custom_message_content_group" style="display: none;">
                <label for="wpsi_custom_message_content"><?php _e('Message:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <textarea id="wpsi_custom_message_content" name="wpsi_custom_message_content" rows="6" class="large-text"></textarea>
            </div>
            
            <button type="button" id="wpsi_send_manual_notification" class="button button-primary"><?php _e('Send Notification', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Show/hide custom message fields
    $('#wpsi_manual_notification_type').on('change', function() {
        if ($(this).val() === 'custom_message') {
            $('#wpsi_custom_message_group, #wpsi_custom_message_content_group').show();
        } else {
            $('#wpsi_custom_message_group, #wpsi_custom_message_content_group').hide();
        }
    });
    
    // Save Email Configuration
    $('#wpsi_save_email_config').on('click', function() {
        var data = {
            action: 'wpsi_save_email_config',
            nonce: wpsi_ajax.nonce,
            notification_email: $('#wpsi_notification_email').val(),
            smtp_host: $('#wpsi_smtp_host').val(),
            smtp_port: $('#wpsi_smtp_port').val(),
            smtp_username: $('#wpsi_smtp_username').val(),
            smtp_password: $('#wpsi_smtp_password').val(),
            smtp_encryption: $('#wpsi_smtp_encryption').val()
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            if (response.success) {
                alert('Email configuration saved successfully!');
            } else {
                alert('Error saving configuration: ' + response.data);
            }
        });
    });
    
    // Send Test Email
    $('#wpsi_test_email').on('click', function() {
        $(this).prop('disabled', true).text('Sending...');
        
        $.post(wpsi_ajax.ajax_url, {
            action: 'wpsi_send_test_email',
            nonce: wpsi_ajax.nonce
        }, function(response) {
            $('#wpsi_test_email').prop('disabled', false).text('Send Test Email');
            
            if (response.success) {
                alert('Test email sent successfully!');
            } else {
                alert('Error sending test email: ' + response.data);
            }
        });
    });
    
    // Save Notification Settings
    $('#wpsi_save_notification_settings').on('click', function() {
        var notifications = $('input[name="wpsi_notifications[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        var data = {
            action: 'wpsi_save_notification_settings',
            nonce: wpsi_ajax.nonce,
            notifications: notifications,
            frequency: $('#wpsi_notification_frequency').val(),
            engagement_threshold: $('#wpsi_engagement_threshold').val(),
            seo_threshold: $('#wpsi_seo_threshold').val()
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            if (response.success) {
                alert('Notification settings saved successfully!');
            } else {
                alert('Error saving settings: ' + response.data);
            }
        });
    });
    
    // Save Templates
    $('#wpsi_save_templates').on('click', function() {
        var data = {
            action: 'wpsi_save_templates',
            nonce: wpsi_ajax.nonce,
            subject_prefix: $('#wpsi_email_subject_prefix').val(),
            daily_template: $('#wpsi_daily_report_template').val(),
            weekly_template: $('#wpsi_weekly_report_template').val(),
            alert_template: $('#wpsi_alert_template').val()
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            if (response.success) {
                alert('Templates saved successfully!');
            } else {
                alert('Error saving templates: ' + response.data);
            }
        });
    });
    
    // Reset Templates
    $('#wpsi_reset_templates').on('click', function() {
        if (confirm('Are you sure you want to reset all templates to defaults?')) {
            $.post(wpsi_ajax.ajax_url, {
                action: 'wpsi_reset_templates',
                nonce: wpsi_ajax.nonce
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error resetting templates: ' + response.data);
                }
            });
        }
    });
    
    // Send Manual Notification
    $('#wpsi_send_manual_notification').on('click', function() {
        var notification_type = $('#wpsi_manual_notification_type').val();
        var data = {
            action: 'wpsi_send_manual_notification',
            nonce: wpsi_ajax.nonce,
            notification_type: notification_type
        };
        
        if (notification_type === 'custom_message') {
            data.subject = $('#wpsi_custom_message_subject').val();
            data.content = $('#wpsi_custom_message_content').val();
            
            if (!data.subject || !data.content) {
                alert('Please fill in both subject and message for custom notifications.');
                return;
            }
        }
        
        $(this).prop('disabled', true).text('Sending...');
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_send_manual_notification').prop('disabled', false).text('Send Notification');
            
            if (response.success) {
                alert('Notification sent successfully!');
            } else {
                alert('Error sending notification: ' + response.data);
            }
        });
    });
    
    // Load notification history
    function loadNotificationHistory() {
        var filter = $('#wpsi_history_filter').val();
        
        $.post(wpsi_ajax.ajax_url, {
            action: 'wpsi_get_notification_history',
            nonce: wpsi_ajax.nonce,
            filter: filter
        }, function(response) {
            if (response.success) {
                $('#wpsi_notification_history_body').html(response.data.html);
            }
        });
    }
    
    // Load history on page load and filter change
    loadNotificationHistory();
    $('#wpsi_history_filter').on('change', loadNotificationHistory);
    
    // Clear History
    $('#wpsi_clear_history').on('click', function() {
        if (confirm('Are you sure you want to clear all notification history?')) {
            $.post(wpsi_ajax.ajax_url, {
                action: 'wpsi_clear_notification_history',
                nonce: wpsi_ajax.nonce
            }, function(response) {
                if (response.success) {
                    loadNotificationHistory();
                } else {
                    alert('Error clearing history: ' + response.data);
                }
            });
        }
    });
});
</script>

<?php
// Helper methods for default templates
function get_default_daily_template() {
    return "Hi there,

Here's your daily Smart Insights report for {site_name}:

📊 Daily Summary ({date})
• Page Views: {page_views}
• Unique Visitors: {unique_visitors}
• Average Engagement: {avg_engagement}%

🏆 Top Performing Posts:
{top_posts}

Keep up the great work!

Best regards,
Smart Insights Team";
}

function get_default_weekly_template() {
    return "Hi there,

Here's your weekly Smart Insights report for {site_name}:

📊 Weekly Summary ({week_start} - {week_end})
• Total Page Views: {total_page_views}
• Total Visitors: {total_visitors}
• Average Engagement: {avg_engagement}%

🏆 Top Performing Posts:
{top_posts}

📈 Trends:
{trends}

Best regards,
Smart Insights Team";
}

function get_default_alert_template() {
    return "Hi there,

Smart Insights Alert for {site_name}:

🚨 Alert Type: {alert_type}
📝 Message: {alert_message}

📄 Affected Posts:
{affected_posts}

💡 Recommendations:
{recommendations}

Best regards,
Smart Insights Team";
}
?> 