<?php
/**
 * Export Service Class
 * Handles data export in CSV and JSON formats
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPSI_Export_Service {
    
    public function __construct() {
        add_action('wp_ajax_wpsi_export_data', array($this, 'handle_export_request'));
        add_action('wp_ajax_wpsi_export_heatmap', array($this, 'export_heatmap_data'));
        add_action('wp_ajax_wpsi_export_journeys', array($this, 'export_journey_data'));
        add_action('wp_ajax_wpsi_export_analysis', array($this, 'export_analysis_data'));
    }
    
    /**
     * Handle export request
     */
    public function handle_export_request() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Fix for PHP Fatal error: Cannot use isset() on the result of an expression
        $export_type_raw = wp_unslash($_POST['export_type'] ?? '');
        $format_raw = wp_unslash($_POST['format'] ?? '');
        $date_range_raw = wp_unslash($_POST['date_range'] ?? '');
        $post_id_raw = wp_unslash($_POST['post_id'] ?? '');

        $export_type = sanitize_text_field($export_type_raw);
        $format = sanitize_text_field($format_raw);
        $date_range = $date_range_raw !== '' ? sanitize_text_field($date_range_raw) : 'all';
        $post_id = $post_id_raw !== '' ? intval($post_id_raw) : 0;
        
        switch ($export_type) {
            case 'heatmaps':
                $this->export_heatmap_data($format, $date_range, $post_id);
                break;
            case 'journeys':
                $this->export_journey_data($format, $date_range, $post_id);
                break;
            case 'analysis':
                $this->export_analysis_data($format, $date_range, $post_id);
                break;
            case 'all':
                $this->export_all_data($format, $date_range);
                break;
            default:
                wp_die('Invalid export type');
        }
    }
    
    /**
     * Export heatmap data
     */
    public function export_heatmap_data($format = 'csv', $date_range = 'all', $post_id = 0) {
        global $wpdb;
        
        $data = $this->get_heatmap_data($date_range, $post_id);
        
        if ($format === 'json') {
            $this->output_json($data, 'heatmap-data');
        } else {
            $this->output_csv($data, 'heatmap-data');
        }
    }
    
    /**
     * Export journey data
     */
    public function export_journey_data($format = 'csv', $date_range = 'all', $post_id = 0) {
        global $wpdb;
        
        if (is_array($format)) {
            $data = $format;
            $format = sanitize_text_field($data['format']);
            $date_range = sanitize_text_field($data['date_range']);
            $post_id = isset($data['post_filter']) ? intval($data['post_filter']) : 0;
            $session_filter = isset($data['session_filter']) ? sanitize_text_field($data['session_filter']) : 'all';
            
            $where_conditions = array();
            $where_values = array();
            
            if ($date_range !== 'all') {
                $where_conditions[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 1 %s)';
                $where_values[] = $date_range;
            }
            
            if ($post_id > 0) {
                $where_conditions[] = 'post_id = %d';
                $where_values[] = $post_id;
            }
            
            if ($session_filter === 'completed') {
                $where_conditions[] = "journey_data LIKE '%session_end%'";
            } elseif ($session_filter === 'abandoned') {
                $where_conditions[] = "journey_data NOT LIKE '%session_end%'";
            }
            
            $where_clause = '';
            if (!empty($where_conditions)) {
                $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
            }
            
            $query = "SELECT j.*, p.post_title FROM {$wpdb->prefix}wpsi_user_journeys j LEFT JOIN {$wpdb->posts} p ON j.post_id = p.ID";
            
            if (!empty($where_conditions)) {
                $query .= " WHERE " . implode(' AND ', $where_conditions);
            }
            
            $query .= " ORDER BY j.created_at DESC";
            
            if (!empty($where_values)) {
                $query = $wpdb->prepare($query, $where_values);
            }
            
            $journeys = $wpdb->get_results($query);
            
            $filename = "wpsi_journeys_{$date_range}_" . gmdate('Y-m-d') . ".$format";
            
            switch ($format) {
                case 'csv':
                    return $this->export_csv($journeys, $filename);
                case 'json':
                    return $this->export_json($journeys, $filename);
                case 'xml':
                    return $this->export_xml($journeys, $filename);
                case 'excel':
                    return $this->export_excel($journeys, $filename);
                default:
                    return array('success' => false, 'message' => 'Invalid export format');
            }
        }
        
        // Handle old parameter format
        $data = $this->get_journey_data($date_range, $post_id);
        
        if ($format === 'json') {
            $this->output_json($data, 'journey-data');
        } else {
            $this->output_csv($data, 'journey-data');
        }
    }
    
    /**
     * Export analysis data
     */
    public function export_analysis_data($format = 'csv', $date_range = 'all', $post_id = 0) {
        $data = $this->get_analysis_data($date_range, $post_id);
        
        if ($format === 'json') {
            $this->output_json($data, 'analysis-data');
        } else {
            $this->output_csv($data, 'analysis-data');
        }
    }
    
    /**
     * Export all data
     */
    public function export_all_data($format = 'json', $date_range = 'all') {
        $data = array(
            'export_info' => array(
                'export_date' => current_time('mysql'),
                'date_range' => $date_range,
                'format' => $format,
                'plugin_version' => WPSI_VERSION
            ),
            'heatmaps' => $this->get_heatmap_data($date_range),
            'journeys' => $this->get_journey_data($date_range),
            'analysis' => $this->get_analysis_data($date_range),
            'settings' => $this->get_settings_data(),
            'statistics' => $this->get_statistics_data()
        );
        
        if ($format === 'json') {
            $this->output_json($data, 'wpsi-complete-export');
        } else {
            $this->output_csv($data, 'wpsi-complete-export');
        }
    }
    
    /**
     * Get heatmap data
     */
    private function get_heatmap_data($date_range = 'all', $post_id = 0) {
        global $wpdb;
        
        $where_conditions = array();
        $where_values = array();
        
        if ($post_id > 0) {
            $where_conditions[] = 'post_id = %d';
            $where_values[] = $post_id;
        }
        
        if ($date_range !== 'all') {
            $date_condition = $this->get_date_condition($date_range);
            if ($date_condition) {
                $where_conditions[] = $date_condition['condition'];
                $where_values = array_merge($where_values, $date_condition['values']);
            }
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $sql = "SELECT h.*, p.post_title FROM {$wpdb->prefix}wpsi_heatmaps h LEFT JOIN {$wpdb->posts} p ON h.post_id = p.ID";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY h.created_at DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        $data = array();
        foreach ($results as $row) {
            $click_data = json_decode($row['click_data'], true);
            $scroll_data = json_decode($row['scroll_data'], true);
            $hover_data = json_decode($row['hover_data'], true);
            
            $data[] = array(
                'id' => $row['id'],
                'post_id' => $row['post_id'],
                'post_title' => $row['post_title'],
                'created_at' => $row['created_at'],
                'total_clicks' => is_array($click_data) ? count($click_data) : 0,
                'total_scrolls' => is_array($scroll_data) ? count($scroll_data) : 0,
                'total_hovers' => is_array($hover_data) ? count($hover_data) : 0,
                'click_data' => $click_data,
                'scroll_data' => $scroll_data,
                'hover_data' => $hover_data
            );
        }
        
        return $data;
    }
    
    /**
     * Get journey data
     */
    private function get_journey_data($date_range = 'all', $post_id = 0) {
        global $wpdb;
        
        $where_conditions = array();
        $where_values = array();
        
        if ($post_id > 0) {
            $where_conditions[] = 'post_id = %d';
            $where_values[] = $post_id;
        }
        
        if ($date_range !== 'all') {
            $date_condition = $this->get_date_condition($date_range);
            if ($date_condition) {
                $where_conditions[] = $date_condition['condition'];
                $where_values = array_merge($where_values, $date_condition['values']);
            }
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $query = "SELECT j.*, p.post_title FROM {$wpdb->prefix}wpsi_user_journeys j LEFT JOIN {$wpdb->posts} p ON j.post_id = p.ID";
        
        if (!empty($where_conditions)) {
            $query .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $query .= " ORDER BY j.created_at DESC";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        $data = array();
        foreach ($results as $row) {
            $journey_data = json_decode($row['journey_data'], true);
            
            $data[] = array(
                'id' => $row['id'],
                'post_id' => $row['post_id'],
                'post_title' => $row['post_title'],
                'session_id' => $row['session_id'],
                'created_at' => $row['created_at'],
                'duration' => isset($journey_data['duration']) ? $journey_data['duration'] : 0,
                'interaction_count' => isset($journey_data['interactions']) ? count($journey_data['interactions']) : 0,
                'journey_data' => $journey_data
            );
        }
        
        return $data;
    }
    
    /**
     * Get analysis data
     */
    private function get_analysis_data($date_range = 'all', $post_id = 0) {
        global $wpdb;
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => '_wpsi_content_analysis',
                    'compare' => 'EXISTS'
                )
            )
        );
        
        if ($post_id > 0) {
            $args['include'] = array($post_id);
        }
        
        $posts = get_posts($args);
        
        $data = array();
        foreach ($posts as $post) {
            $analysis = get_post_meta($post->ID, '_wpsi_content_analysis', true);
            $seo_score = get_post_meta($post->ID, '_wpsi_seo_score', true);
            
            if ($analysis) {
                $data[] = array(
                    'post_id' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_date' => $post->post_date,
                    'analysis_date' => get_post_meta($post->ID, '_wpsi_analysis_date', true),
                    'readability_score' => isset($analysis['readability_score']) ? $analysis['readability_score'] : 0,
                    'sentiment_score' => isset($analysis['sentiment_score']) ? $analysis['sentiment_score'] : 0,
                    'tone_score' => isset($analysis['tone_score']) ? $analysis['tone_score'] : 0,
                    'keyword_score' => isset($analysis['keyword_score']) ? $analysis['keyword_score'] : 0,
                    'repetition_score' => isset($analysis['repetition_score']) ? $analysis['repetition_score'] : 0,
                    'seo_score' => $seo_score ? $seo_score : 0,
                    'full_analysis' => $analysis
                );
            }
        }
        
        return $data;
    }
    
    /**
     * Get settings data
     */
    private function get_settings_data() {
        return array(
            'tracking_enabled' => get_option('wpsi_tracking_enabled', false),
            'ai_provider' => get_option('wpsi_ai_provider', 'openai'),
            'privacy_compliant' => get_option('wpsi_privacy_compliant', true),
            'cookie_consent' => get_option('wpsi_cookie_consent', true),
            'data_retention' => get_option('wpsi_data_retention', 90),
            'heatmap_enabled' => get_option('wpsi_heatmap_enabled', true),
            'journey_enabled' => get_option('wpsi_journey_enabled', true)
        );
    }
    
    /**
     * Get statistics data
     */
    private function get_statistics_data() {
        global $wpdb;
        
        $stats = array();
        
        // Heatmap statistics
        $heatmap_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wpsi_heatmaps");
        $journey_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wpsi_user_journeys");
        
        // Analysis statistics
        $posts_with_analysis = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => '_wpsi_content_analysis',
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        $stats['total_heatmaps'] = $heatmap_count;
        $stats['total_journeys'] = $journey_count;
        $stats['posts_analyzed'] = count($posts_with_analysis);
        $stats['total_posts'] = wp_count_posts('post')->publish;
        
        return $stats;
    }
    
    /**
     * Get date condition for queries
     */
    private function get_date_condition($date_range) {
        $now = current_time('mysql');
        
        switch ($date_range) {
            case 'today':
                return array(
                    'condition' => 'DATE(created_at) = CURDATE()',
                    'values' => array()
                );
            case 'yesterday':
                return array(
                    'condition' => 'DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)',
                    'values' => array()
                );
            case 'week':
                return array(
                    'condition' => 'created_at >= DATE_SUB(%s, INTERVAL 7 DAY)',
                    'values' => array($now)
                );
            case 'month':
                return array(
                    'condition' => 'created_at >= DATE_SUB(%s, INTERVAL 30 DAY)',
                    'values' => array($now)
                );
            case 'quarter':
                return array(
                    'condition' => 'created_at >= DATE_SUB(%s, INTERVAL 90 DAY)',
                    'values' => array($now)
                );
            default:
                return null;
        }
    }
    
    /**
     * Output JSON data
     */
    private function output_json($data, $filename) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '-' . gmdate('Y-m-d') . '.json"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Output CSV data
     */
    private function output_csv($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '-' . gmdate('Y-m-d') . '.csv"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        $output = fopen('php://output', 'w');
        
        if (empty($data)) {
            // TODO: Replace with WP_Filesystem - fclose($output);
            exit;
        }
        
        // Write headers
        fputcsv($output, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            $csv_row = array();
            foreach ($row as $value) {
                if (is_array($value)) {
                    $csv_row[] = json_encode($value);
                } else {
                    $csv_row[] = $value;
                }
            }
            fputcsv($output, $csv_row);
        }
        
        // TODO: Replace with WP_Filesystem - fclose($output);
        exit;
    }
    
    /**
     * Schedule automated exports
     */
    public function schedule_export($type, $format, $frequency, $email = '') {
        $schedule_key = 'wpsi_export_' . $type . '_' . $frequency;
        
        if (!wp_next_scheduled($schedule_key)) {
            wp_schedule_event(time(), $frequency, $schedule_key, array($type, $format, $email));
        }
        
        update_option('wpsi_export_schedule_' . $schedule_key, array(
            'type' => $type,
            'format' => $format,
            'email' => $email,
            'frequency' => $frequency,
            'last_export' => null,
            'next_export' => wp_next_scheduled($schedule_key)
        ));
    }
    
    /**
     * Cancel scheduled export
     */
    public function cancel_scheduled_export($type, $frequency) {
        $schedule_key = 'wpsi_export_' . $type . '_' . $frequency;
        wp_clear_scheduled_hook($schedule_key);
        delete_option('wpsi_export_schedule_' . $schedule_key);
    }
    
    /**
     * Get export schedules
     */
    public function get_export_schedules() {
        $schedules = array();
        $options = wp_load_alloptions();
        
        foreach ($options as $key => $value) {
            if (strpos($key, 'wpsi_export_schedule_') === 0) {
                $schedules[] = array_merge(
                    array('schedule_key' => $key),
                    maybe_unserialize($value)
                );
            }
        }
        
        return $schedules;
    }
    
    /**
     * Export heatmaps
     */
    public function export_heatmaps($data) {
        global $wpdb;
        
        $format = sanitize_text_field($data['format']);
        $date_range = sanitize_text_field($data['date_range']);
        $post_filter = isset($data['post_filter']) ? intval($data['post_filter']) : 0;
        $data_type = isset($data['data_type']) ? sanitize_text_field($data['data_type']) : 'all';
        
        $where_conditions = array();
        $where_values = array();
        
        // Date range filter
        if ($date_range !== 'all') {
            $where_conditions[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 1 %s)';
            $where_values[] = $date_range;
        }
        
        // Post filter
        if ($post_filter > 0) {
            $where_conditions[] = "post_id = %d";
            $where_values[] = $post_filter;
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $sql = "SELECT h.*, p.post_title 
                FROM {$wpdb->prefix}wpsi_heatmaps h
                LEFT JOIN {$wpdb->posts} p ON h.post_id = p.ID
                $where_clause
                ORDER BY h.created_at DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $heatmaps = $wpdb->get_results($sql);
        
        $filename = "wpsi_heatmaps_{$date_range}_" . gmdate('Y-m-d') . ".$format";
        
        switch ($format) {
            case 'csv':
                return $this->export_csv($heatmaps, $filename);
            case 'json':
                return $this->export_json($heatmaps, $filename);
            case 'xml':
                return $this->export_xml($heatmaps, $filename);
            case 'excel':
                return $this->export_excel($heatmaps, $filename);
            default:
                return array('success' => false, 'message' => 'Invalid export format');
        }
    }
    
    /**
     * Export analytics
     */
    public function export_analytics($data) {
        global $wpdb;
        
        $format = sanitize_text_field($data['format']);
        $date_range = sanitize_text_field($data['date_range']);
        $event_type = isset($data['event_type']) ? sanitize_text_field($data['event_type']) : 'all';
        $group_by = isset($data['group_by']) ? sanitize_text_field($data['group_by']) : 'none';
        
        $where_conditions = array();
        $where_values = array();
        
        // Date range filter
        if ($date_range !== 'all') {
            $where_conditions[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 1 %s)';
            $where_values[] = $date_range;
        }
        
        // Event type filter
        if ($event_type !== 'all') {
            $where_conditions[] = "event_type = %s";
            $where_values[] = $event_type;
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $group_clause = '';
        if ($group_by !== 'none') {
            $group_clause = "GROUP BY $group_by";
        }
        
        $sql = "SELECT e.*, p.post_title FROM {$wpdb->prefix}wpsi_analytics_events e LEFT JOIN {$wpdb->posts} p ON e.post_id = p.ID";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        if ($group_by !== 'none') {
            $sql .= " GROUP BY $group_by";
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $analytics = $wpdb->get_results($sql);
        
        $filename = "wpsi_analytics_{$date_range}_" . gmdate('Y-m-d') . ".$format";
        
        switch ($format) {
            case 'csv':
                return $this->export_csv($analytics, $filename);
            case 'json':
                return $this->export_json($analytics, $filename);
            case 'xml':
                return $this->export_xml($analytics, $filename);
            case 'excel':
                return $this->export_excel($analytics, $filename);
            default:
                return array('success' => false, 'message' => 'Invalid export format');
        }
    }
    
    /**
     * Export content analysis
     */
    public function export_content_analysis($data) {
        global $wpdb;
        
        $format = sanitize_text_field($data['format']);
        $post_type = isset($data['post_type']) ? sanitize_text_field($data['post_type']) : 'all';
        $analysis_type = isset($data['analysis_type']) ? sanitize_text_field($data['analysis_type']) : 'all';
        
        $where_conditions = array();
        $where_values = array();
        
        // Post type filter
        if ($post_type !== 'all') {
            $where_conditions[] = "p.post_type = %s";
            $where_values[] = $post_type;
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $sql = "SELECT p.ID, p.post_title, p.post_type, pm.meta_value as content_analysis, pm2.meta_value as seo_score FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wpsi_content_analysis' LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_wpsi_seo_score'";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY p.post_date DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $content_analysis = $wpdb->get_results($sql);
        
        $filename = "wpsi_content_analysis_{$post_type}_" . gmdate('Y-m-d') . ".$format";
        
        switch ($format) {
            case 'csv':
                return $this->export_csv($content_analysis, $filename);
            case 'json':
                return $this->export_json($content_analysis, $filename);
            case 'xml':
                return $this->export_xml($content_analysis, $filename);
            case 'excel':
                return $this->export_excel($content_analysis, $filename);
            default:
                return array('success' => false, 'message' => 'Invalid export format');
        }
    }
    
    /**
     * Bulk export
     */
    public function bulk_export($data) {
        $export_types = isset($data['export_types']) ? $data['export_types'] : array();
        $bulk_format = isset($data['bulk_format']) ? sanitize_text_field($data['bulk_format']) : 'separate_files';
        
        if (empty($export_types)) {
            return array('success' => false, 'message' => 'No export types selected');
        }
        
        $results = array();
        
        foreach ($export_types as $type) {
            switch ($type) {
                case 'heatmaps':
                    $results['heatmaps'] = $this->export_heatmaps($data);
                    break;
                case 'journeys':
                    $results['journeys'] = $this->export_journey_data($data);
                    break;
                case 'analytics':
                    $results['analytics'] = $this->export_analytics($data);
                    break;
                case 'content_analysis':
                    $results['content_analysis'] = $this->export_content_analysis($data);
                    break;
                case 'seo_scores':
                    $results['seo_scores'] = $this->export_seo_scores($data);
                    break;
            }
        }
        
        if ($bulk_format === 'zip_archive') {
            return $this->create_zip_archive($results);
        } elseif ($bulk_format === 'combined_file') {
            return $this->create_combined_file($results);
        } else {
            return array('success' => true, 'results' => $results);
        }
    }
    
    /**
     * Get export history
     */
    public function get_export_history() {
        global $wpdb;
        
        $exports = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}wpsi_exports 
            ORDER BY created_at DESC 
            LIMIT 50"
        );
        
        $html = '';
        foreach ($exports as $export) {
            $html .= '<tr>';
            $html .= '<td>' . gmdate('Y-m-d H:i:s', strtotime($export->created_at)) . '</td>';
            $html .= '<td>' . esc_html($export->export_type) . '</td>';
            $html .= '<td>' . esc_html($export->format) . '</td>';
            $html .= '<td>' . intval($export->records_count) . '</td>';
            $html .= '<td><a href="' . esc_url($export->download_url) . '" class="button button-small">Download</a></td>';
            $html .= '</tr>';
        }
        
        return array('success' => true, 'html' => $html);
    }
    
    /**
     * Export SEO scores
     */
    private function export_seo_scores($data) {
        global $wpdb;
        
        $format = sanitize_text_field($data['format']);
        
        $sql = "SELECT p.ID, p.post_title, p.post_type, 
                       pm.meta_value as seo_score
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wpsi_seo_score'
                WHERE pm.meta_value IS NOT NULL
                ORDER BY p.post_date DESC";
        
        $seo_scores = $wpdb->get_results($sql);
        
        $filename = "wpsi_seo_scores_" . gmdate('Y-m-d') . ".$format";
        
        switch ($format) {
            case 'csv':
                return $this->export_csv($seo_scores, $filename);
            case 'json':
                return $this->export_json($seo_scores, $filename);
            case 'xml':
                return $this->export_xml($seo_scores, $filename);
            case 'excel':
                return $this->export_excel($seo_scores, $filename);
            default:
                return array('success' => false, 'message' => 'Invalid export format');
        }
    }
    
    /**
     * Create ZIP archive
     */
    private function create_zip_archive($results) {
        $zip_file = wp_upload_dir()['basedir'] . '/wpsi_bulk_export_' . gmdate('Y-m-d_H-i-s') . '.zip';
        
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) {
            return array('success' => false, 'message' => 'Failed to create ZIP archive');
        }
        
        foreach ($results as $type => $result) {
            if ($result['success'] && isset($result['download_url'])) {
                $file_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $result['download_url']);
                if (file_exists($file_path)) {
                    $zip->addFile($file_path, basename($file_path));
                }
            }
        }
        
        $zip->close();
        
        $download_url = str_replace(wp_upload_dir()['basedir'], wp_upload_dir()['baseurl'], $zip_file);
        
        return array('success' => true, 'download_url' => $download_url);
    }
    
    /**
     * Create combined file
     */
    private function create_combined_file($results) {
        $combined_data = array();
        
        foreach ($results as $type => $result) {
            if ($result['success'] && isset($result['download_url'])) {
                $file_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $result['download_url']);
                if (file_exists($file_path)) {
                    $file_content = file_get_contents($file_path);
                    $combined_data[$type] = json_decode($file_content, true);
                }
            }
        }
        
        $filename = "wpsi_combined_export_" . gmdate('Y-m-d_H-i-s') . ".json";
        $file_path = wp_upload_dir()['basedir'] . '/' . $filename;
        
        file_put_contents($file_path, json_encode($combined_data, JSON_PRETTY_PRINT));
        
        $download_url = wp_upload_dir()['baseurl'] . '/' . $filename;
        
        return array('success' => true, 'download_url' => $download_url);
    }
} 