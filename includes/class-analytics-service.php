<?php
/**
 * Analytics Service Class
 * 
 * Handles advanced analytics, webhooks, and data processing
 * 
 * @package WP_Smart_Insights
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WPSI_Analytics_Service {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_ajax_wpsi_save_analytics_event', array($this, 'save_analytics_event'));
        add_action('wp_ajax_nopriv_wpsi_save_analytics_event', array($this, 'save_analytics_event'));
        add_action('wp_ajax_wpsi_trigger_webhook', array($this, 'trigger_webhook'));
        add_action('wp_ajax_wpsi_get_analytics_data', array($this, 'get_analytics_data'));
        add_action('wp_ajax_wpsi_export_analytics', array($this, 'export_analytics'));
        
        // Scheduled events
        add_action('wpsi_daily_analytics_report', array($this, 'generate_daily_report'));
        add_action('wpsi_weekly_analytics_report', array($this, 'generate_weekly_report'));
    }
    
    public function init() {
        // Schedule events if not already scheduled
        if (!wp_next_scheduled('wpsi_daily_analytics_report')) {
            wp_schedule_event(time(), 'daily', 'wpsi_daily_analytics_report');
        }
        
        if (!wp_next_scheduled('wpsi_weekly_analytics_report')) {
            wp_schedule_event(time(), 'weekly', 'wpsi_weekly_analytics_report');
        }
    }
    
    /**
     * Save analytics event
     */
    public function save_analytics_event() {
        check_ajax_referer('wpsi_frontend_nonce', 'nonce');
        
        // Fix for PHP Fatal error: Cannot use isset() on the result of an expression
        $event_type_raw = wp_unslash($_POST['event_type'] ?? '');
        $event_data_raw = wp_unslash($_POST['event_data'] ?? '');
        $post_id_raw = wp_unslash($_POST['post_id'] ?? '');
        $session_id_raw = wp_unslash($_POST['session_id'] ?? '');

        $event_type = sanitize_text_field($event_type_raw);
        $event_data = wp_kses_post($event_data_raw);
        $post_id = $post_id_raw !== '' ? intval($post_id_raw) : 0;
        $session_id = sanitize_text_field($session_id_raw);
        
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wpsi_analytics_events',
            array(
                'event_type' => $event_type,
                'event_data' => $event_data,
                'post_id' => $post_id,
                'session_id' => $session_id,
                'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT']),
                'ip_address' => $this->get_client_ip(),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s', '%s')
        );
        
        // Trigger webhook if configured
        $this->trigger_webhook_for_event($event_type, $event_data, $post_id);
        
        wp_send_json_success();
    }
    
    /**
     * Get analytics data
     */
    public function get_analytics_data() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        // Fix for PHP Fatal error: Cannot use isset() on the result of an expression
        $date_range_raw = wp_unslash($_POST['date_range'] ?? '');
        $post_id_raw = wp_unslash($_POST['post_id'] ?? '');
        $date_range = sanitize_text_field($date_range_raw);
        $post_id = $post_id_raw !== '' ? intval($post_id_raw) : 0;

        $data = array(
            'page_views' => $this->get_page_views($date_range, $post_id),
            'user_engagement' => $this->get_user_engagement($date_range, $post_id),
            'content_performance' => $this->get_content_performance($date_range, $post_id),
            'user_behavior' => $this->get_user_behavior($date_range, $post_id),
            'conversion_funnel' => $this->get_conversion_funnel($date_range, $post_id)
        );
        
        wp_send_json_success($data);
    }
    
    /**
     * Get page views data
     */
    private function get_page_views($date_range, $post_id = 0) {
        global $wpdb;
        
        $where_conditions = array('created_at >= DATE_SUB(NOW(), INTERVAL 1 %s)');
        $where_values = array($date_range);
        if ($post_id > 0) {
            $where_conditions[] = 'post_id = %d';
            $where_values[] = $post_id;
        }
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as views, COUNT(DISTINCT session_id) as unique_views FROM {$wpdb->prefix}wpsi_analytics_events $where_clause ORDER BY date DESC";
        $sql = $wpdb->prepare($sql, $where_values);
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get user engagement data
     */
    private function get_user_engagement($date_range, $post_id = 0) {
        global $wpdb;
        
        $where_conditions = array('created_at >= DATE_SUB(NOW(), INTERVAL 1 %s)');
        $where_values = array($date_range);
        if ($post_id > 0) {
            $where_conditions[] = 'post_id = %d';
            $where_values[] = $post_id;
        }
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        $sql = "SELECT event_type, COUNT(*) as count, AVG(JSON_EXTRACT(event_data, '$.duration')) as avg_duration, AVG(JSON_EXTRACT(event_data, '$.scroll_depth')) as avg_scroll_depth FROM {$wpdb->prefix}wpsi_analytics_events $where_clause GROUP BY event_type";
        $sql = $wpdb->prepare($sql, $where_values);
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get content performance data
     */
    private function get_content_performance($date_range, $post_id = 0) {
        global $wpdb;
        
        $where_conditions = array('e.created_at >= DATE_SUB(NOW(), INTERVAL 1 %s)');
        $where_values = array($date_range);
        if ($post_id > 0) {
            $where_conditions[] = 'e.post_id = %d';
            $where_values[] = $post_id;
        }
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        $sql = "SELECT p.ID, p.post_title, COUNT(e.id) as total_events, COUNT(DISTINCT e.session_id) as unique_sessions, AVG(JSON_EXTRACT(e.event_data, '$.engagement_score')) as avg_engagement FROM {$wpdb->prefix}wpsi_analytics_events e LEFT JOIN {$wpdb->posts} p ON e.post_id = p.ID $where_clause GROUP BY e.post_id LIMIT 20";
        $sql = $wpdb->prepare($sql, $where_values);
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get user behavior data
     */
    private function get_user_behavior($date_range, $post_id = 0) {
        global $wpdb;
        
        $where_conditions = array('created_at >= DATE_SUB(NOW(), INTERVAL 1 %s)');
        $where_values = array($date_range);
        if ($post_id > 0) {
            $where_conditions[] = 'post_id = %d';
            $where_values[] = $post_id;
        }
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        $sql = "SELECT session_id, COUNT(*) as events_count, GROUP_CONCAT(event_type ORDER BY created_at) as event_sequence, MIN(created_at) as session_start, MAX(created_at) as session_end FROM {$wpdb->prefix}wpsi_analytics_events $where_clause GROUP BY session_id LIMIT 50";
        $sql = $wpdb->prepare($sql, $where_values);
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get conversion funnel data
     */
    private function get_conversion_funnel($date_range, $post_id = 0) {
        global $wpdb;
        
        $where_conditions = array('created_at >= DATE_SUB(NOW(), INTERVAL 1 %s)');
        $where_values = array($date_range);
        if ($post_id > 0) {
            $where_conditions[] = 'post_id = %d';
            $where_values[] = $post_id;
        }
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        $sql = "SELECT 'page_view' as step, COUNT(DISTINCT session_id) as count FROM {$wpdb->prefix}wpsi_analytics_events $where_clause AND event_type = 'page_view' UNION ALL SELECT 'scroll' as step, COUNT(DISTINCT session_id) as count FROM {$wpdb->prefix}wpsi_analytics_events $where_clause AND event_type = 'scroll' UNION ALL SELECT 'click' as step, COUNT(DISTINCT session_id) as count FROM {$wpdb->prefix}wpsi_analytics_events $where_clause AND event_type = 'click' UNION ALL SELECT 'form_submit' as step, COUNT(DISTINCT session_id) as count FROM {$wpdb->prefix}wpsi_analytics_events $where_clause AND event_type = 'form_submit'";
        $sql = $wpdb->prepare($sql, array_merge($where_values, $where_values, $where_values, $where_values));
        return $wpdb->get_results($sql);
    }
    
    /**
     * Export analytics data
     */
    public function export_analytics() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        $export_type_raw = wp_unslash($_POST['export_type'] ?? '');
        $date_range_raw = wp_unslash($_POST['date_range'] ?? '');
        $export_type = sanitize_text_field($export_type_raw);
        $date_range = sanitize_text_field($date_range_raw);
        $post_id_raw = wp_unslash($_POST['post_id'] ?? '');
        $post_id = $post_id_raw !== '' ? intval($post_id_raw) : 0;
        
        $export_service = new WPSI_Export_Service();
        
        switch ($export_type) {
            case 'page_views':
                $data = $this->get_page_views($date_range, $post_id);
                break;
            case 'user_engagement':
                $data = $this->get_user_engagement($date_range, $post_id);
                break;
            case 'content_performance':
                $data = $this->get_content_performance($date_range, $post_id);
                break;
            case 'user_behavior':
                $data = $this->get_user_behavior($date_range, $post_id);
                break;
            case 'conversion_funnel':
                $data = $this->get_conversion_funnel($date_range, $post_id);
                break;
            default:
                wp_send_json_error('Invalid export type');
        }
        
        $filename = "wpsi_analytics_{$export_type}_{$date_range}_" . gmdate('Y-m-d') . ".csv";
        $export_service->export_csv($data, $filename);
    }
    
    /**
     * Trigger webhook for event
     */
    private function trigger_webhook_for_event($event_type, $event_data, $post_id) {
        $webhook_url = get_option('wpsi_webhook_url', '');
        if (empty($webhook_url)) {
            return;
        }
        
        $webhook_data = array(
            'event_type' => $event_type,
            'event_data' => $event_data,
            'post_id' => $post_id,
            'timestamp' => current_time('mysql'),
            'site_url' => get_site_url(),
            'plugin_version' => WPSI_VERSION
        );
        
        wp_remote_post($webhook_url, array(
            'body' => json_encode($webhook_data),
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-WPSI-Webhook' => 'true'
            ),
            'timeout' => 30
        ));
    }
    
    /**
     * Generate daily analytics report
     */
    public function generate_daily_report() {
        $data = array(
            'page_views' => $this->get_page_views('DAY'),
            'user_engagement' => $this->get_user_engagement('DAY'),
            'content_performance' => $this->get_content_performance('DAY')
        );
        
        $notification_service = new WPSI_Notification_Service();
        $notification_service->send_daily_report($data);
    }
    
    /**
     * Generate weekly analytics report
     */
    public function generate_weekly_report() {
        $data = array(
            'page_views' => $this->get_page_views('WEEK'),
            'user_engagement' => $this->get_user_engagement('WEEK'),
            'content_performance' => $this->get_content_performance('WEEK'),
            'user_behavior' => $this->get_user_behavior('WEEK'),
            'conversion_funnel' => $this->get_conversion_funnel('WEEK')
        );
        
        $notification_service = new WPSI_Notification_Service();
        $notification_service->send_weekly_report($data);
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    /**
     * Create analytics tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpsi_analytics_events (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_data longtext NOT NULL,
            post_id bigint(20) NOT NULL,
            session_id varchar(255) NOT NULL,
            user_agent text,
            ip_address varchar(45),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY post_id (post_id),
            KEY session_id (session_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Save analytics configuration
     */
    public function save_analytics_config($data) {
        update_option('wpsi_analytics_enabled', isset($data['analytics_enabled']));
        update_option('wpsi_session_timeout', intval($data['session_timeout']));
        update_option('wpsi_data_retention_days', intval($data['data_retention_days']));
        update_option('wpsi_track_user_agents', isset($data['track_user_agents']));
        update_option('wpsi_track_ip_addresses', isset($data['track_ip_addresses']));
        
        return array('success' => true, 'message' => 'Analytics configuration saved successfully');
    }
    
    /**
     * Save webhook configuration
     */
    public function save_webhook_config($data) {
        update_option('wpsi_webhook_enabled', isset($data['webhook_enabled']));
        update_option('wpsi_webhook_url', sanitize_url($data['webhook_url']));
        update_option('wpsi_webhook_secret', sanitize_text_field($data['webhook_secret']));
        update_option('wpsi_webhook_events', isset($data['webhook_events']) ? $data['webhook_events'] : array());
        update_option('wpsi_webhook_timeout', intval($data['webhook_timeout']));
        update_option('wpsi_webhook_retry_attempts', intval($data['webhook_retry_attempts']));
        
        return array('success' => true, 'message' => 'Webhook configuration saved successfully');
    }
    
    /**
     * Test webhook
     */
    public function test_webhook() {
        $webhook_url = get_option('wpsi_webhook_url', '');
        if (empty($webhook_url)) {
            return array('success' => false, 'message' => 'Webhook URL not configured');
        }
        
        $test_data = array(
            'event_type' => 'test',
            'event_data' => json_encode(array('message' => 'Test webhook from Smart Insights')),
            'post_id' => 0,
            'timestamp' => current_time('mysql'),
            'site_url' => get_site_url(),
            'plugin_version' => WPSI_VERSION
        );
        
        $response = wp_remote_post($webhook_url, array(
            'body' => json_encode($test_data),
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-WPSI-Webhook' => 'true'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'Webhook test failed: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code >= 200 && $status_code < 300) {
            return array('success' => true, 'message' => 'Webhook test successful');
        } else {
            return array('success' => false, 'message' => 'Webhook test failed with status code: ' . $status_code);
        }
    }
    
    /**
     * Generate analytics report
     */
    public function generate_analytics_report($data) {
        $date_range = sanitize_text_field($data['date_range']);
        $start_date = isset($data['start_date']) ? sanitize_text_field($data['start_date']) : '';
        $end_date = isset($data['end_date']) ? sanitize_text_field($data['end_date']) : '';
        
        $report_data = array(
            'page_views' => $this->get_page_views($date_range),
            'user_engagement' => $this->get_user_engagement($date_range),
            'content_performance' => $this->get_content_performance($date_range),
            'user_behavior' => $this->get_user_behavior($date_range),
            'conversion_funnel' => $this->get_conversion_funnel($date_range)
        );
        
        $html = $this->generate_report_html($report_data, $date_range);
        
        return array('success' => true, 'html' => $html);
    }
    
    /**
     * Get real-time analytics
     */
    public function get_realtime_analytics() {
        global $wpdb;
        
        // Active sessions (last 30 minutes)
        $active_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id) FROM {$wpdb->prefix}wpsi_analytics_events 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d MINUTE)",
            get_option('wpsi_session_timeout', 30)
        ));
        
        // Page views today
        $page_views_today = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wpsi_analytics_events 
            WHERE event_type = 'page_view' AND DATE(created_at) = CURDATE()"
        );
        
        // Unique visitors today
        $unique_visitors_today = $wpdb->get_var(
            "SELECT COUNT(DISTINCT session_id) FROM {$wpdb->prefix}wpsi_analytics_events 
            WHERE DATE(created_at) = CURDATE()"
        );
        
        // Average session duration
        $avg_session_duration = $wpdb->get_var(
            "SELECT AVG(JSON_EXTRACT(event_data, '$.duration')) FROM {$wpdb->prefix}wpsi_analytics_events 
            WHERE event_type = 'session_end' AND DATE(created_at) = CURDATE()"
        );
        
        return array(
            'success' => true,
            'active_sessions' => intval($active_sessions),
            'page_views_today' => intval($page_views_today),
            'unique_visitors_today' => intval($unique_visitors_today),
            'avg_session_duration' => round($avg_session_duration / 60, 1) . 'm'
        );
    }
    
    /**
     * Get recent events
     */
    public function get_recent_events() {
        global $wpdb;
        
        $events = $wpdb->get_results(
            "SELECT e.*, p.post_title 
            FROM {$wpdb->prefix}wpsi_analytics_events e
            LEFT JOIN {$wpdb->posts} p ON e.post_id = p.ID
            ORDER BY e.created_at DESC 
            LIMIT 20"
        );
        
        $html = '<div class="wpsi-events-list">';
        foreach ($events as $event) {
            $html .= '<div class="wpsi-event-item">';
            $html .= '<div class="wpsi-event-time">' . gmdate('H:i:s', strtotime($event->created_at)) . '</div>';
            $html .= '<div class="wpsi-event-type">' . esc_html($event->event_type) . '</div>';
            $html .= '<div class="wpsi-event-post">' . esc_html($event->post_title ?: 'N/A') . '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        
        return array('success' => true, 'html' => $html);
    }
    
    /**
     * Cleanup old data
     */
    public function cleanup_old_data() {
        global $wpdb;
        
        $retention_days = get_option('wpsi_data_retention_days', 365);
        
        $removed_events = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}wpsi_analytics_events 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
        
        $removed_heatmaps = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}wpsi_heatmaps 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
        
        $removed_journeys = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}wpsi_user_journeys 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
        
        return array(
            'success' => true,
            'removed_records' => $removed_events + $removed_heatmaps + $removed_journeys
        );
    }
    
    /**
     * Export analytics data
     */
    public function export_analytics_data() {
        global $wpdb;
        
        $export_service = new WPSI_Export_Service();
        
        $data = $wpdb->get_results(
            "SELECT e.*, p.post_title 
            FROM {$wpdb->prefix}wpsi_analytics_events e
            LEFT JOIN {$wpdb->posts} p ON e.post_id = p.ID
            ORDER BY e.created_at DESC"
        );
        
        $filename = "wpsi_analytics_data_" . gmdate('Y-m-d_H-i-s') . ".csv";
        $download_url = $export_service->export_csv($data, $filename);
        
        return array('success' => true, 'download_url' => $download_url);
    }
    
    /**
     * Get webhook logs
     */
    public function get_webhook_logs($data) {
        global $wpdb;
        
        $filter = isset($data['filter']) ? sanitize_text_field($data['filter']) : 'all';
        $where_clause = '';
        
        if ($filter !== 'all') {
            $where_clause = $wpdb->prepare("WHERE status = %s", $filter);
        }
        
        $logs = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}wpsi_webhook_logs 
            $where_clause 
            ORDER BY created_at DESC 
            LIMIT 50"
        );
        
        $html = '';
        foreach ($logs as $log) {
            $html .= '<tr>';
            $html .= '<td>' . gmdate('Y-m-d H:i:s', strtotime($log->created_at)) . '</td>';
            $html .= '<td>' . esc_html($log->event_type) . '</td>';
            $html .= '<td>' . esc_html($log->status) . '</td>';
            $html .= '<td>' . esc_html($log->response) . '</td>';
            $html .= '<td>' . intval($log->attempts) . '</td>';
            $html .= '<td><button class="button button-small" onclick="retryWebhook(' . $log->id . ')">Retry</button></td>';
            $html .= '</tr>';
        }
        
        return array('success' => true, 'html' => $html);
    }
    
    /**
     * Clear webhook logs
     */
    public function clear_webhook_logs() {
        global $wpdb;
        
        $wpdb->query("DELETE FROM {$wpdb->prefix}wpsi_webhook_logs");
        
        return array('success' => true, 'message' => 'Webhook logs cleared successfully');
    }
    
    /**
     * Generate report HTML
     */
    private function generate_report_html($data, $date_range) {
        $html = '<div class="wpsi-report">';
        $html .= '<h3>Analytics Report - ' . ucfirst(str_replace('_', ' ', $date_range)) . '</h3>';
        
        // Page Views
        $html .= '<div class="wpsi-report-section">';
        $html .= '<h4>Page Views</h4>';
        $html .= '<table class="wp-list-table widefat">';
        $html .= '<thead><tr><th>Date</th><th>Views</th><th>Unique Views</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($data['page_views'] as $view) {
            $html .= '<tr>';
            $html .= '<td>' . esc_html($view->date) . '</td>';
            $html .= '<td>' . intval($view->views) . '</td>';
            $html .= '<td>' . intval($view->unique_views) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        
        // User Engagement
        $html .= '<div class="wpsi-report-section">';
        $html .= '<h4>User Engagement</h4>';
        $html .= '<table class="wp-list-table widefat">';
        $html .= '<thead><tr><th>Event Type</th><th>Count</th><th>Avg Duration</th><th>Avg Scroll Depth</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($data['user_engagement'] as $engagement) {
            $html .= '<tr>';
            $html .= '<td>' . esc_html($engagement->event_type) . '</td>';
            $html .= '<td>' . intval($engagement->count) . '</td>';
            $html .= '<td>' . round($engagement->avg_duration, 2) . 's</td>';
            $html .= '<td>' . round($engagement->avg_scroll_depth, 1) . '%</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        
        $html .= '</div>';
        
        return $html;
    }
} 