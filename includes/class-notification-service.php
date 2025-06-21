<?php
/**
 * Notification Service Class
 * Handles email notifications for important insights and events
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPSI_Notification_Service {
    
    private $notification_settings;
    
    public function __construct() {
        $this->notification_settings = get_option('wpsi_notification_settings', array());
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wpsi_content_analysis_completed', array($this, 'notify_content_analysis'));
        add_action('wpsi_seo_score_updated', array($this, 'notify_seo_update'));
        add_action('wpsi_heatmap_threshold_reached', array($this, 'notify_heatmap_insights'));
        add_action('wpsi_user_journey_anomaly', array($this, 'notify_user_journey_anomaly'));
        add_action('wpsi_privacy_compliance_alert', array($this, 'notify_privacy_alert'));
        add_action('wpsi_weekly_summary', array($this, 'send_weekly_summary'));
        add_action('wpsi_monthly_report', array($this, 'send_monthly_report'));
    }
    
    /**
     * Notify when content analysis is completed
     */
    public function notify_content_analysis($post_id, $analysis_data) {
        if (!$this->is_notification_enabled('content_analysis')) {
            return;
        }
        
        $post = get_post($post_id);
        $score = isset($analysis_data['overall_score']) ? $analysis_data['overall_score'] : 0;
        
        // Only notify for low scores or significant improvements
        if ($score < 60 || $score > 90) {
            $subject = sprintf(
                '[%s] Content Analysis Alert - %s',
                get_bloginfo('name'),
                $post->post_title
            );
            
            $message = $this->build_content_analysis_message($post, $analysis_data);
            
            $this->send_notification($subject, $message, 'content_analysis');
        }
    }
    
    /**
     * Notify when SEO score is updated
     */
    public function notify_seo_update($post_id, $seo_data) {
        if (!$this->is_notification_enabled('seo_updates')) {
            return;
        }
        
        $post = get_post($post_id);
        $score = isset($seo_data['overall_score']) ? $seo_data['overall_score'] : 0;
        
        // Notify for significant SEO changes
        $previous_score = get_post_meta($post_id, '_wpsi_previous_seo_score', true);
        if ($previous_score && abs($score - $previous_score) >= 10) {
            $subject = sprintf(
                '[%s] SEO Score Change - %s',
                get_bloginfo('name'),
                $post->post_title
            );
            
            $message = $this->build_seo_update_message($post, $seo_data, $previous_score);
            
            $this->send_notification($subject, $message, 'seo_updates');
        }
        
        update_post_meta($post_id, '_wpsi_previous_seo_score', $score);
    }
    
    /**
     * Notify when heatmap threshold is reached
     */
    public function notify_heatmap_insights($post_id, $heatmap_data) {
        if (!$this->is_notification_enabled('heatmap_insights')) {
            return;
        }
        
        $post = get_post($post_id);
        $threshold = $this->get_notification_setting('heatmap_threshold', 100);
        
        if ($heatmap_data['total_visitors'] >= $threshold) {
            $subject = sprintf(
                '[%s] Heatmap Insights Available - %s',
                get_bloginfo('name'),
                $post->post_title
            );
            
            $message = $this->build_heatmap_insights_message($post, $heatmap_data);
            
            $this->send_notification($subject, $message, 'heatmap_insights');
        }
    }
    
    /**
     * Notify when user journey anomaly is detected
     */
    public function notify_user_journey_anomaly($post_id, $anomaly_data) {
        if (!$this->is_notification_enabled('user_journey_alerts')) {
            return;
        }
        
        $post = get_post($post_id);
        
        $subject = sprintf(
            '[%s] User Journey Anomaly Detected - %s',
            get_bloginfo('name'),
            $post->post_title
        );
        
        $message = $this->build_user_journey_anomaly_message($post, $anomaly_data);
        
        $this->send_notification($subject, $message, 'user_journey_alerts');
    }
    
    /**
     * Notify privacy compliance alerts
     */
    public function notify_privacy_alert($alert_type, $alert_data) {
        if (!$this->is_notification_enabled('privacy_alerts')) {
            return;
        }
        
        $subject = sprintf(
            '[%s] Privacy Compliance Alert - %s',
            get_bloginfo('name'),
            ucfirst($alert_type)
        );
        
        $message = $this->build_privacy_alert_message($alert_type, $alert_data);
        
        $this->send_notification($subject, $message, 'privacy_alerts');
    }
    
    /**
     * Send weekly summary
     */
    public function send_weekly_summary() {
        if (!$this->is_notification_enabled('weekly_summary')) {
            return;
        }
        
        $summary_data = $this->get_weekly_summary_data();
        
        $subject = sprintf(
            '[%s] Weekly Smart Insights Summary',
            get_bloginfo('name')
        );
        
        $message = $this->build_weekly_summary_message($summary_data);
        
        $this->send_notification($subject, $message, 'weekly_summary');
    }
    
    /**
     * Send monthly report
     */
    public function send_monthly_report() {
        if (!$this->is_notification_enabled('monthly_report')) {
            return;
        }
        
        $report_data = $this->get_monthly_report_data();
        
        $subject = sprintf(
            '[%s] Monthly Smart Insights Report',
            get_bloginfo('name')
        );
        
        $message = $this->build_monthly_report_message($report_data);
        
        $this->send_notification($subject, $message, 'monthly_report');
    }
    
    /**
     * Build content analysis message
     */
    private function build_content_analysis_message($post, $analysis_data) {
        $score = isset($analysis_data['overall_score']) ? $analysis_data['overall_score'] : 0;
        $readability = isset($analysis_data['readability_score']) ? $analysis_data['readability_score'] : 0;
        $sentiment = isset($analysis_data['sentiment_score']) ? $analysis_data['sentiment_score'] : 0;
        
        $message = "Content Analysis Completed\n\n";
        $message .= "Post: " . $post->post_title . "\n";
        $message .= "URL: " . get_permalink($post->ID) . "\n";
        $message .= "Overall Score: " . $score . "/100\n";
        $message .= "Readability Score: " . $readability . "/100\n";
        $message .= "Sentiment Score: " . $sentiment . "/100\n\n";
        
        if (isset($analysis_data['recommendations'])) {
            $message .= "Recommendations:\n";
            foreach ($analysis_data['recommendations'] as $rec) {
                $message .= "- " . $rec['description'] . "\n";
            }
        }
        
        $message .= "\nView full analysis: " . admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap-content&post_id=' . $post->ID);
        
        return $message;
    }
    
    /**
     * Build SEO update message
     */
    private function build_seo_update_message($post, $seo_data, $previous_score) {
        $current_score = isset($seo_data['overall_score']) ? $seo_data['overall_score'] : 0;
        $change = $current_score - $previous_score;
        $direction = $change > 0 ? 'improved' : 'decreased';
        
        $message = "SEO Score Update\n\n";
        $message .= "Post: " . $post->post_title . "\n";
        $message .= "URL: " . get_permalink($post->ID) . "\n";
        $message .= "Previous Score: " . $previous_score . "/100\n";
        $message .= "Current Score: " . $current_score . "/100\n";
        $message .= "Change: " . ($change > 0 ? '+' : '') . $change . " points (" . $direction . ")\n\n";
        
        if (isset($seo_data['fixes'])) {
            $message .= "Quick Fixes Available:\n";
            foreach (array_slice($seo_data['fixes'], 0, 3) as $fix) {
                $message .= "- " . $fix['title'] . "\n";
            }
        }
        
        $message .= "\nView SEO analysis: " . admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap-seo&post_id=' . $post->ID);
        
        return $message;
    }
    
    /**
     * Build heatmap insights message
     */
    private function build_heatmap_insights_message($post, $heatmap_data) {
        $message = "Heatmap Insights Available\n\n";
        $message .= "Post: " . $post->post_title . "\n";
        $message .= "URL: " . get_permalink($post->ID) . "\n";
        $message .= "Total Visitors: " . $heatmap_data['total_visitors'] . "\n";
        $message .= "Average Time: " . $heatmap_data['avg_time'] . " seconds\n";
        $message .= "Bounce Rate: " . $heatmap_data['bounce_rate'] . "%\n\n";
        
        if (isset($heatmap_data['ux_warnings'])) {
            $message .= "UX Warnings:\n";
            foreach (array_slice($heatmap_data['ux_warnings'], 0, 3) as $warning) {
                $message .= "- " . $warning['title'] . "\n";
            }
        }
        
        $message .= "\nView heatmap: " . admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap-heatmaps&post_id=' . $post->ID);
        
        return $message;
    }
    
    /**
     * Build user journey anomaly message
     */
    private function build_user_journey_anomaly_message($post, $anomaly_data) {
        $message = "User Journey Anomaly Detected\n\n";
        $message .= "Post: " . $post->post_title . "\n";
        $message .= "URL: " . get_permalink($post->ID) . "\n";
        $message .= "Anomaly Type: " . $anomaly_data['type'] . "\n";
        $message .= "Description: " . $anomaly_data['description'] . "\n";
        $message .= "Severity: " . $anomaly_data['severity'] . "\n\n";
        
        if (isset($anomaly_data['suggestions'])) {
            $message .= "Suggestions:\n";
            foreach ($anomaly_data['suggestions'] as $suggestion) {
                $message .= "- " . $suggestion . "\n";
            }
        }
        
        $message .= "\nView user journeys: " . admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap-journeys&post_id=' . $post->ID);
        
        return $message;
    }
    
    /**
     * Build privacy alert message
     */
    private function build_privacy_alert_message($alert_type, $alert_data) {
        $message = "Privacy Compliance Alert\n\n";
        $message .= "Alert Type: " . ucfirst($alert_type) . "\n";
        $message .= "Description: " . $alert_data['description'] . "\n";
        $message .= "Severity: " . $alert_data['severity'] . "\n\n";
        
        if (isset($alert_data['actions'])) {
            $message .= "Required Actions:\n";
            foreach ($alert_data['actions'] as $action) {
                $message .= "- " . $action . "\n";
            }
        }
        
        $message .= "\nManage privacy settings: " . admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap-settings');
        
        return $message;
    }
    
    /**
     * Build weekly summary message
     */
    private function build_weekly_summary_message($summary_data) {
        $message = "Weekly Smart Insights Summary\n\n";
        $message .= "Period: " . $summary_data['period'] . "\n\n";
        
        $message .= "Content Analysis:\n";
        $message .= "- Posts Analyzed: " . $summary_data['posts_analyzed'] . "\n";
        $message .= "- Average Content Score: " . $summary_data['avg_content_score'] . "/100\n";
        $message .= "- Average SEO Score: " . $summary_data['avg_seo_score'] . "/100\n\n";
        
        $message .= "User Engagement:\n";
        $message .= "- Total Heatmap Sessions: " . $summary_data['heatmap_sessions'] . "\n";
        $message .= "- Total User Journeys: " . $summary_data['user_journeys'] . "\n";
        $message .= "- Average Session Duration: " . $summary_data['avg_session_duration'] . " seconds\n\n";
        
        $message .= "Top Performing Content:\n";
        foreach ($summary_data['top_content'] as $content) {
            $message .= "- " . $content['title'] . " (Score: " . $content['score'] . ")\n";
        }
        
        $message .= "\nView full dashboard: " . admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap');
        
        return $message;
    }
    
    /**
     * Build monthly report message
     */
    private function build_monthly_report_message($report_data) {
        $message = "Monthly Smart Insights Report\n\n";
        $message .= "Period: " . $report_data['period'] . "\n\n";
        
        $message .= "Performance Overview:\n";
        $message .= "- Total Posts: " . $report_data['total_posts'] . "\n";
        $message .= "- Posts Analyzed: " . $report_data['posts_analyzed'] . "\n";
        $message .= "- Content Quality Trend: " . $report_data['content_trend'] . "\n";
        $message .= "- SEO Performance Trend: " . $report_data['seo_trend'] . "\n\n";
        
        $message .= "User Behavior Insights:\n";
        $message .= "- Total Sessions: " . $report_data['total_sessions'] . "\n";
        $message .= "- Average Engagement: " . $report_data['avg_engagement'] . "%\n";
        $message .= "- Bounce Rate: " . $report_data['bounce_rate'] . "%\n\n";
        
        $message .= "Recommendations:\n";
        foreach ($report_data['recommendations'] as $rec) {
            $message .= "- " . $rec . "\n";
        }
        
        $message .= "\nView detailed report: " . admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap');
        
        return $message;
    }
    
    /**
     * Get weekly summary data
     */
    private function get_weekly_summary_data() {
        $end_date = current_time('mysql');
        $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));
        
        return array(
            'period' => date('M j', strtotime($start_date)) . ' - ' . date('M j', strtotime($end_date)),
            'posts_analyzed' => $this->get_posts_analyzed_count($start_date, $end_date),
            'avg_content_score' => $this->get_average_content_score($start_date, $end_date),
            'avg_seo_score' => $this->get_average_seo_score($start_date, $end_date),
            'heatmap_sessions' => $this->get_heatmap_sessions_count($start_date, $end_date),
            'user_journeys' => $this->get_user_journeys_count($start_date, $end_date),
            'avg_session_duration' => $this->get_average_session_duration($start_date, $end_date),
            'top_content' => $this->get_top_performing_content($start_date, $end_date)
        );
    }
    
    /**
     * Get monthly report data
     */
    private function get_monthly_report_data() {
        $end_date = current_time('mysql');
        $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        return array(
            'period' => date('M j', strtotime($start_date)) . ' - ' . date('M j', strtotime($end_date)),
            'total_posts' => wp_count_posts('post')->publish,
            'posts_analyzed' => $this->get_posts_analyzed_count($start_date, $end_date),
            'content_trend' => $this->get_content_trend($start_date, $end_date),
            'seo_trend' => $this->get_seo_trend($start_date, $end_date),
            'total_sessions' => $this->get_total_sessions_count($start_date, $end_date),
            'avg_engagement' => $this->get_average_engagement($start_date, $end_date),
            'bounce_rate' => $this->get_bounce_rate($start_date, $end_date),
            'recommendations' => $this->get_monthly_recommendations($start_date, $end_date)
        );
    }
    
    /**
     * Send notification
     */
    private function send_notification($subject, $message, $type) {
        $recipients = $this->get_notification_recipients($type);
        
        if (empty($recipients)) {
            return false;
        }
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        foreach ($recipients as $recipient) {
            wp_mail($recipient, $subject, $message, $headers);
        }
        
        // Log notification
        $this->log_notification($type, $subject, $recipients);
        
        return true;
    }
    
    /**
     * Check if notification is enabled
     */
    private function is_notification_enabled($type) {
        return isset($this->notification_settings[$type]['enabled']) && 
               $this->notification_settings[$type]['enabled'];
    }
    
    /**
     * Get notification setting
     */
    private function get_notification_setting($key, $default = null) {
        return isset($this->notification_settings[$key]) ? 
               $this->notification_settings[$key] : $default;
    }
    
    /**
     * Get notification recipients
     */
    private function get_notification_recipients($type) {
        $recipients = array();
        
        if (isset($this->notification_settings[$type]['recipients'])) {
            $recipients = $this->notification_settings[$type]['recipients'];
        }
        
        // Always include admin email if no specific recipients
        if (empty($recipients)) {
            $recipients = array(get_option('admin_email'));
        }
        
        return array_unique($recipients);
    }
    
    /**
     * Log notification
     */
    private function log_notification($type, $subject, $recipients) {
        $logs = get_option('wpsi_notification_logs', array());
        
        $logs[] = array(
            'type' => $type,
            'subject' => $subject,
            'recipients' => $recipients,
            'sent_at' => current_time('mysql')
        );
        
        // Keep only last 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        update_option('wpsi_notification_logs', $logs);
    }
    
    /**
     * Helper methods for data retrieval
     */
    private function get_posts_analyzed_count($start_date, $end_date) {
        // Implementation for getting posts analyzed count
        return 0;
    }
    
    private function get_average_content_score($start_date, $end_date) {
        // Implementation for getting average content score
        return 0;
    }
    
    private function get_average_seo_score($start_date, $end_date) {
        // Implementation for getting average SEO score
        return 0;
    }
    
    private function get_heatmap_sessions_count($start_date, $end_date) {
        // Implementation for getting heatmap sessions count
        return 0;
    }
    
    private function get_user_journeys_count($start_date, $end_date) {
        // Implementation for getting user journeys count
        return 0;
    }
    
    private function get_average_session_duration($start_date, $end_date) {
        // Implementation for getting average session duration
        return 0;
    }
    
    private function get_top_performing_content($start_date, $end_date) {
        // Implementation for getting top performing content
        return array();
    }
    
    private function get_total_sessions_count($start_date, $end_date) {
        // Implementation for getting total sessions count
        return 0;
    }
    
    private function get_average_engagement($start_date, $end_date) {
        // Implementation for getting average engagement
        return 0;
    }
    
    private function get_bounce_rate($start_date, $end_date) {
        // Implementation for getting bounce rate
        return 0;
    }
    
    private function get_content_trend($start_date, $end_date) {
        // Implementation for getting content trend
        return 'Stable';
    }
    
    private function get_seo_trend($start_date, $end_date) {
        // Implementation for getting SEO trend
        return 'Stable';
    }
    
    private function get_monthly_recommendations($start_date, $end_date) {
        // Implementation for getting monthly recommendations
        return array('Continue monitoring content performance', 'Focus on SEO optimization');
    }
    
    /**
     * Send weekly report
     */
    public function send_weekly_report($data) {
        $template = get_option('wpsi_weekly_report_template', $this->get_default_weekly_template());
        $subject = get_option('wpsi_email_subject_prefix', '[Smart Insights]') . ' Weekly Report';
        
        $message = $this->replace_template_variables($template, array(
            'site_name' => get_bloginfo('name'),
            'week_start' => date('Y-m-d', strtotime('-7 days')),
            'week_end' => date('Y-m-d'),
            'total_page_views' => $this->calculate_total_page_views($data['page_views']),
            'total_visitors' => $this->calculate_total_visitors($data['page_views']),
            'avg_engagement' => $this->calculate_avg_engagement($data['user_engagement']),
            'top_posts' => $this->format_top_posts($data['content_performance']),
            'trends' => $this->generate_trends_summary($data)
        ));
        
        return $this->send_email($subject, $message);
    }
    
    /**
     * Save email configuration
     */
    public function save_email_config($data) {
        update_option('wpsi_notification_email', sanitize_email($data['notification_email']));
        update_option('wpsi_smtp_host', sanitize_text_field($data['smtp_host']));
        update_option('wpsi_smtp_port', intval($data['smtp_port']));
        update_option('wpsi_smtp_username', sanitize_text_field($data['smtp_username']));
        update_option('wpsi_smtp_password', sanitize_text_field($data['smtp_password']));
        update_option('wpsi_smtp_encryption', sanitize_text_field($data['smtp_encryption']));
        
        return array('success' => true, 'message' => 'Email configuration saved successfully');
    }
    
    /**
     * Send test email
     */
    public function send_test_email() {
        $subject = get_option('wpsi_email_subject_prefix', '[Smart Insights]') . ' Test Email';
        $message = "This is a test email from Smart Insights plugin.\n\n";
        $message .= "If you received this email, your email configuration is working correctly.\n\n";
        $message .= "Sent at: " . current_time('mysql') . "\n";
        $message .= "Site: " . get_site_url();
        
        $result = $this->send_email($subject, $message);
        
        if ($result) {
            return array('success' => true, 'message' => 'Test email sent successfully');
        } else {
            return array('success' => false, 'message' => 'Failed to send test email');
        }
    }
    
    /**
     * Save notification settings
     */
    public function save_notification_settings($data) {
        update_option('wpsi_notifications', isset($data['notifications']) ? $data['notifications'] : array());
        update_option('wpsi_notification_frequency', sanitize_text_field($data['frequency']));
        update_option('wpsi_engagement_threshold', intval($data['engagement_threshold']));
        update_option('wpsi_seo_threshold', intval($data['seo_threshold']));
        
        return array('success' => true, 'message' => 'Notification settings saved successfully');
    }
    
    /**
     * Save templates
     */
    public function save_templates($data) {
        update_option('wpsi_email_subject_prefix', sanitize_text_field($data['subject_prefix']));
        update_option('wpsi_daily_report_template', wp_kses_post($data['daily_template']));
        update_option('wpsi_weekly_report_template', wp_kses_post($data['weekly_template']));
        update_option('wpsi_alert_template', wp_kses_post($data['alert_template']));
        
        return array('success' => true, 'message' => 'Templates saved successfully');
    }
    
    /**
     * Reset templates
     */
    public function reset_templates() {
        update_option('wpsi_email_subject_prefix', '[Smart Insights]');
        update_option('wpsi_daily_report_template', $this->get_default_daily_template());
        update_option('wpsi_weekly_report_template', $this->get_default_weekly_template());
        update_option('wpsi_alert_template', $this->get_default_alert_template());
        
        return array('success' => true, 'message' => 'Templates reset to defaults');
    }
    
    /**
     * Send manual notification
     */
    public function send_manual_notification($data) {
        $notification_type = sanitize_text_field($data['notification_type']);
        
        switch ($notification_type) {
            case 'daily_report':
                $analytics_service = new WPSI_Analytics_Service();
                $report_data = array(
                    'page_views' => $analytics_service->get_page_views('DAY'),
                    'user_engagement' => $analytics_service->get_user_engagement('DAY'),
                    'content_performance' => $analytics_service->get_content_performance('DAY')
                );
                return $this->send_daily_report($report_data);
                
            case 'weekly_report':
                $analytics_service = new WPSI_Analytics_Service();
                $report_data = array(
                    'page_views' => $analytics_service->get_page_views('WEEK'),
                    'user_engagement' => $analytics_service->get_user_engagement('WEEK'),
                    'content_performance' => $analytics_service->get_content_performance('WEEK'),
                    'user_behavior' => $analytics_service->get_user_behavior('WEEK'),
                    'conversion_funnel' => $analytics_service->get_conversion_funnel('WEEK')
                );
                return $this->send_weekly_report($report_data);
                
            case 'custom_message':
                $subject = sanitize_text_field($data['subject']);
                $content = wp_kses_post($data['content']);
                $result = $this->send_email($subject, $content);
                
                if ($result) {
                    return array('success' => true, 'message' => 'Custom notification sent successfully');
                } else {
                    return array('success' => false, 'message' => 'Failed to send custom notification');
                }
                
            default:
                return array('success' => false, 'message' => 'Invalid notification type');
        }
    }
    
    /**
     * Get notification history
     */
    public function get_notification_history($data) {
        global $wpdb;
        
        $filter = isset($data['filter']) ? sanitize_text_field($data['filter']) : 'all';
        $where_clause = '';
        
        if ($filter !== 'all') {
            $where_clause = $wpdb->prepare("WHERE notification_type = %s", $filter);
        }
        
        $notifications = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}wpsi_notifications 
            $where_clause 
            ORDER BY created_at DESC 
            LIMIT 50"
        );
        
        $html = '';
        foreach ($notifications as $notification) {
            $html .= '<tr>';
            $html .= '<td>' . date('Y-m-d H:i:s', strtotime($notification->created_at)) . '</td>';
            $html .= '<td>' . esc_html($notification->notification_type) . '</td>';
            $html .= '<td>' . esc_html($notification->subject) . '</td>';
            $html .= '<td>' . esc_html($notification->status) . '</td>';
            $html .= '<td><button class="button button-small" onclick="resendNotification(' . $notification->id . ')">Resend</button></td>';
            $html .= '</tr>';
        }
        
        return array('success' => true, 'html' => $html);
    }
    
    /**
     * Clear notification history
     */
    public function clear_notification_history() {
        global $wpdb;
        
        $wpdb->query("DELETE FROM {$wpdb->prefix}wpsi_notifications");
        
        return array('success' => true, 'message' => 'Notification history cleared successfully');
    }
    
    /**
     * Get default daily template
     */
    private function get_default_daily_template() {
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
    
    /**
     * Get default weekly template
     */
    private function get_default_weekly_template() {
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
    
    /**
     * Get default alert template
     */
    private function get_default_alert_template() {
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
    
    /**
     * Calculate total page views
     */
    private function calculate_total_page_views($page_views_data) {
        $total = 0;
        foreach ($page_views_data as $view) {
            $total += intval($view->views);
        }
        return $total;
    }
    
    /**
     * Calculate total visitors
     */
    private function calculate_total_visitors($page_views_data) {
        $total = 0;
        foreach ($page_views_data as $view) {
            $total += intval($view->unique_views);
        }
        return $total;
    }
    
    /**
     * Calculate average engagement
     */
    private function calculate_avg_engagement($engagement_data) {
        if (empty($engagement_data)) {
            return 0;
        }
        
        $total_engagement = 0;
        $count = 0;
        
        foreach ($engagement_data as $engagement) {
            if ($engagement->avg_duration) {
                $total_engagement += floatval($engagement->avg_duration);
                $count++;
            }
        }
        
        return $count > 0 ? round($total_engagement / $count, 1) : 0;
    }
    
    /**
     * Format top posts
     */
    private function format_top_posts($content_performance) {
        if (empty($content_performance)) {
            return 'No data available';
        }
        
        $html = '';
        $count = 0;
        foreach ($content_performance as $post) {
            if ($count >= 5) break;
            $html .= "• " . esc_html($post->post_title) . " (Engagement: " . round($post->avg_engagement, 1) . "%)\n";
            $count++;
        }
        
        return $html;
    }
    
    /**
     * Generate trends summary
     */
    private function generate_trends_summary($data) {
        $trends = array();
        
        if (isset($data['user_behavior']) && !empty($data['user_behavior'])) {
            $trends[] = "User behavior patterns analyzed";
        }
        
        if (isset($data['conversion_funnel']) && !empty($data['conversion_funnel'])) {
            $trends[] = "Conversion funnel optimization opportunities identified";
        }
        
        return empty($trends) ? "No significant trends detected" : implode("\n", $trends);
    }
} 