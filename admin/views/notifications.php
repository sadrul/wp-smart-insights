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
    <h1><?php esc_html_e('Notifications', 'wp-smart-insights'); ?></h1>
    
    <div class="wpsi-admin-container">
        <!-- Email Configuration Section -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Email Configuration', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_notification_email"><?php esc_html_e('Notification Email:', 'wp-smart-insights'); ?></label>
                <input type="email" id="wpsi_notification_email" name="wpsi_notification_email" value="<?php echo esc_attr(get_option('wpsi_notification_email', get_option('admin_email'))); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('Email address to receive notifications.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_host"><?php esc_html_e('SMTP Host (Optional):', 'wp-smart-insights'); ?></label>
                <input type="text" id="wpsi_smtp_host" name="wpsi_smtp_host" value="<?php echo esc_attr(get_option('wpsi_smtp_host', '')); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('Leave empty to use WordPress default email.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_port"><?php esc_html_e('SMTP Port:', 'wp-smart-insights'); ?></label>
                <input type="number" id="wpsi_smtp_port" name="wpsi_smtp_port" value="<?php echo esc_attr(get_option('wpsi_smtp_port', '587')); ?>" class="small-text">
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_username"><?php esc_html_e('SMTP Username:', 'wp-smart-insights'); ?></label>
                <input type="text" id="wpsi_smtp_username" name="wpsi_smtp_username" value="<?php echo esc_attr(get_option('wpsi_smtp_username', '')); ?>" class="regular-text">
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_password"><?php esc_html_e('SMTP Password:', 'wp-smart-insights'); ?></label>
                <input type="password" id="wpsi_smtp_password" name="wpsi_smtp_password" value="<?php echo esc_attr(get_option('wpsi_smtp_password', '')); ?>" class="regular-text">
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_smtp_encryption"><?php esc_html_e('Encryption:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_smtp_encryption" name="wpsi_smtp_encryption">
                    <option value="tls" <?php selected(get_option('wpsi_smtp_encryption', 'tls'), 'tls'); ?>><?php esc_html_e('TLS', 'wp-smart-insights'); ?></option>
                    <option value="ssl" <?php selected(get_option('wpsi_smtp_encryption', 'tls'), 'ssl'); ?>><?php esc_html_e('SSL', 'wp-smart-insights'); ?></option>
                    <option value="none" <?php selected(get_option('wpsi_smtp_encryption', 'tls'), 'none'); ?>><?php esc_html_e('None', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_save_email_config" class="button button-primary"><?php esc_html_e('Save Email Configuration', 'wp-smart-insights'); ?></button>
            <button type="button" id="wpsi_test_email" class="button button-secondary"><?php esc_html_e('Send Test Email', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Notification Settings Section -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Notification Settings', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label><?php esc_html_e('Enable Notifications:', 'wp-smart-insights'); ?></label>
                <div class="wpsi-checkbox-group">
                    <label><input type="checkbox" name="wpsi_notifications[]" value="daily_report" <?php checked(in_array('daily_report', get_option('wpsi_notifications', array()))); ?>> <?php esc_html_e('Daily Analytics Report', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="weekly_report" <?php checked(in_array('weekly_report', get_option('wpsi_notifications', array()))); ?>> <?php esc_html_e('Weekly Analytics Report', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="low_engagement" <?php checked(in_array('low_engagement', get_option('wpsi_notifications', array()))); ?>> <?php esc_html_e('Low Engagement Alerts', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="seo_issues" <?php checked(in_array('seo_issues', get_option('wpsi_notifications', array()))); ?>> <?php esc_html_e('SEO Issues Alerts', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="content_suggestions" <?php checked(in_array('content_suggestions', get_option('wpsi_notifications', array()))); ?>> <?php esc_html_e('Content Improvement Suggestions', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_notifications[]" value="system_alerts" <?php checked(in_array('system_alerts', get_option('wpsi_notifications', array()))); ?>> <?php esc_html_e('System Alerts', 'wp-smart-insights'); ?></label>
                </div>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_notification_frequency"><?php esc_html_e('Notification Frequency:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_notification_frequency" name="wpsi_notification_frequency">
                    <option value="immediate" <?php selected(get_option('wpsi_notification_frequency', 'daily'), 'immediate'); ?>><?php esc_html_e('Immediate', 'wp-smart-insights'); ?></option>
                    <option value="hourly" <?php selected(get_option('wpsi_notification_frequency', 'daily'), 'hourly'); ?>><?php esc_html_e('Hourly', 'wp-smart-insights'); ?></option>
                    <option value="daily" <?php selected(get_option('wpsi_notification_frequency', 'daily'), 'daily'); ?>><?php esc_html_e('Daily', 'wp-smart-insights'); ?></option>
                    <option value="weekly" <?php selected(get_option('wpsi_notification_frequency', 'daily'), 'weekly'); ?>><?php esc_html_e('Weekly', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_engagement_threshold"><?php esc_html_e('Low Engagement Threshold (%):', 'wp-smart-insights'); ?></label>
                <input type="number" id="wpsi_engagement_threshold" name="wpsi_engagement_threshold" value="<?php echo esc_attr(get_option('wpsi_engagement_threshold', 30)); ?>" min="1" max="100" class="small-text">
                <p class="description"><?php esc_html_e('Percentage below which to trigger low engagement alerts.', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_seo_threshold"><?php esc_html_e('SEO Score Threshold:', 'wp-smart-insights'); ?></label>
                <input type="number" id="wpsi_seo_threshold" name="wpsi_seo_threshold" value="<?php echo esc_attr(get_option('wpsi_seo_threshold', 70)); ?>" min="1" max="100" class="small-text">
                <p class="description"><?php esc_html_e('SEO score below which to trigger alerts.', 'wp-smart-insights'); ?></p>
            </div>
            
            <button type="button" id="wpsi_save_notification_settings" class="button button-primary"><?php esc_html_e('Save Notification Settings', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Custom Notification Templates -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Custom Notification Templates', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_email_subject_prefix"><?php esc_html_e('Email Subject Prefix:', 'wp-smart-insights'); ?></label>
                <input type="text" id="wpsi_email_subject_prefix" name="wpsi_email_subject_prefix" value="<?php echo esc_attr(get_option('wpsi_email_subject_prefix', '[Smart Insights]')); ?>" class="regular-text">
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_daily_report_template"><?php esc_html_e('Daily Report Template:', 'wp-smart-insights'); ?></label>
                <textarea id="wpsi_daily_report_template" name="wpsi_daily_report_template" rows="8" class="large-text"><?php echo esc_textarea(get_option('wpsi_daily_report_template', $this->get_default_daily_template())); ?></textarea>
                <p class="description"><?php esc_html_e('Available variables: {site_name}, {date}, {page_views}, {unique_visitors}, {avg_engagement}, {top_posts}', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_weekly_report_template"><?php esc_html_e('Weekly Report Template:', 'wp-smart-insights'); ?></label>
                <textarea id="wpsi_weekly_report_template" name="wpsi_weekly_report_template" rows="8" class="large-text"><?php echo esc_textarea(get_option('wpsi_weekly_report_template', $this->get_default_weekly_template())); ?></textarea>
                <p class="description"><?php esc_html_e('Available variables: {site_name}, {week_start}, {week_end}, {total_page_views}, {total_visitors}, {avg_engagement}, {top_posts}, {trends}', 'wp-smart-insights'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_alert_template"><?php esc_html_e('Alert Template:', 'wp-smart-insights'); ?></label>
                <textarea id="wpsi_alert_template" name="wpsi_alert_template" rows="6" class="large-text"><?php echo esc_textarea(get_option('wpsi_alert_template', $this->get_default_alert_template())); ?></textarea>
                <p class="description"><?php esc_html_e('Available variables: {site_name}, {alert_type}, {alert_message}, {affected_posts}, {recommendations}', 'wp-smart-insights'); ?></p>
            </div>
            
            <button type="button" id="wpsi_save_templates" class="button button-primary"><?php esc_html_e('Save Templates', 'wp-smart-insights'); ?></button>
            <button type="button" id="wpsi_reset_templates" class="button button-secondary"><?php esc_html_e('Reset to Defaults', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Notification History -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Notification History', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_history_filter"><?php esc_html_e('Filter by Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_history_filter" name="wpsi_history_filter">
                    <option value="all"><?php esc_html_e('All Notifications', 'wp-smart-insights'); ?></option>
                    <option value="daily_report"><?php esc_html_e('Daily Reports', 'wp-smart-insights'); ?></option>
                    <option value="weekly_report"><?php esc_html_e('Weekly Reports', 'wp-smart-insights'); ?></option>
                    <option value="alert"><?php esc_html_e('Alerts', 'wp-smart-insights'); ?></option>
                    <option value="system"><?php esc_html_e('System Notifications', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div id="wpsi_notification_history">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Type', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Subject', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Status', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Actions', 'wp-smart-insights'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wpsi_notification_history_body">
                        <!-- Notification history will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <button type="button" id="wpsi_clear_history" class="button button-secondary"><?php esc_html_e('Clear History', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Manual Notification Sending -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Send Manual Notification', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_manual_notification_type"><?php esc_html_e('Notification Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_manual_notification_type" name="wpsi_manual_notification_type">
                    <option value="daily_report"><?php esc_html_e('Daily Report', 'wp-smart-insights'); ?></option>
                    <option value="weekly_report"><?php esc_html_e('Weekly Report', 'wp-smart-insights'); ?></option>
                    <option value="custom_message"><?php esc_html_e('Custom Message', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group" id="wpsi_custom_message_group" style="display: none;">
                <label for="wpsi_custom_message_subject"><?php esc_html_e('Subject:', 'wp-smart-insights'); ?></label>
                <input type="text" id="wpsi_custom_message_subject" name="wpsi_custom_message_subject" class="regular-text">
            </div>
            
            <div class="wpsi-form-group" id="wpsi_custom_message_content_group" style="display: none;">
                <label for="wpsi_custom_message_content"><?php esc_html_e('Message:', 'wp-smart-insights'); ?></label>
                <textarea id="wpsi_custom_message_content" name="wpsi_custom_message_content" rows="6" class="large-text"></textarea>
            </div>
            
            <button type="button" id="wpsi_send_manual_notification" class="button button-primary"><?php esc_html_e('Send Notification', 'wp-smart-insights'); ?></button>
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