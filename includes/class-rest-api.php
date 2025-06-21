<?php
/**
 * REST API Class
 * Provides REST API endpoints for external integrations
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPSI_REST_API {
    
    private $namespace = 'wpsi/v1';
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Dashboard endpoints
        register_rest_route($this->namespace, '/dashboard/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_dashboard_stats'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'date_range' => array(
                    'default' => 'all',
                    'sanitize_callback' => 'sanitize_text_field',
                )
            )
        ));
        
        // Content analysis endpoints
        register_rest_route($this->namespace, '/content/analyze', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_content'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'post_id' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
                'content' => array(
                    'required' => false,
                    'sanitize_callback' => 'wp_kses_post'
                )
            )
        ));
        
        register_rest_route($this->namespace, '/content/analysis/(?P<post_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_content_analysis'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'post_id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        ));
        
        // Heatmap endpoints
        register_rest_route($this->namespace, '/heatmaps', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_heatmaps'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'post_id' => array(
                    'required' => false,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
                'date_range' => array(
                    'default' => 'all',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'limit' => array(
                    'default' => 50,
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param <= 100;
                    }
                )
            )
        ));
        
        register_rest_route($this->namespace, '/heatmaps/(?P<post_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_heatmap_data'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'post_id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        ));
        
        // User journey endpoints
        register_rest_route($this->namespace, '/journeys', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_journeys'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'post_id' => array(
                    'required' => false,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
                'date_range' => array(
                    'default' => 'all',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'limit' => array(
                    'default' => 50,
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param <= 100;
                    }
                )
            )
        ));
        
        register_rest_route($this->namespace, '/journeys/(?P<journey_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_journey_details'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'journey_id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        ));
        
        // SEO endpoints
        register_rest_route($this->namespace, '/seo/analyze', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_seo'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'post_id' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        ));
        
        register_rest_route($this->namespace, '/seo/score/(?P<post_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_seo_score'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'post_id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        ));
        
        // Settings endpoints
        register_rest_route($this->namespace, '/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_settings'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($this->namespace, '/settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_settings'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'tracking_enabled' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'ai_provider' => array(
                    'type' => 'string',
                    'enum' => array('openai', 'google', 'none'),
                    'default' => 'none'
                ),
                'privacy_compliant' => array(
                    'type' => 'boolean',
                    'default' => true
                )
            )
        ));
        
        // Export endpoints
        register_rest_route($this->namespace, '/export/(?P<type>[a-zA-Z]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_data'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'type' => array(
                    'enum' => array('heatmaps', 'journeys', 'analysis', 'all'),
                    'required' => true
                ),
                'format' => array(
                    'enum' => array('json', 'csv'),
                    'default' => 'json'
                ),
                'date_range' => array(
                    'default' => 'all',
                    'sanitize_callback' => 'sanitize_text_field',
                )
            )
        ));
        
        // Webhook endpoints
        register_rest_route($this->namespace, '/webhooks', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_webhooks'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($this->namespace, '/webhooks', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_webhook'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'event' => array(
                    'required' => true,
                    'enum' => array('content_analysis', 'seo_update', 'heatmap_threshold', 'user_journey_anomaly'),
                ),
                'url' => array(
                    'required' => true,
                    'format' => 'uri',
                ),
                'secret' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field',
                )
            )
        ));
        
        register_rest_route($this->namespace, '/webhooks/(?P<webhook_id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_webhook'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'webhook_id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        ));
    }
    
    /**
     * Check API permissions
     */
    public function check_permissions($request) {
        // Check for API key authentication
        $api_key = $request->get_header('X-API-Key');
        if ($api_key) {
            $valid_key = get_option('wpsi_api_key', '');
            if ($api_key === $valid_key) {
                return true;
            }
        }
        
        // Fallback to WordPress user authentication
        return current_user_can('manage_options');
    }
    
    /**
     * Get dashboard statistics
     */
    public function get_dashboard_stats($request) {
        $date_range = $request->get_param('date_range');
        
        $stats = array(
            'total_posts' => wp_count_posts('post')->publish,
            'analyzed_posts' => $this->get_analyzed_posts_count($date_range),
            'heatmap_data' => $this->get_heatmap_sessions_count($date_range),
            'user_journeys' => $this->get_user_journeys_count($date_range),
            'avg_content_score' => $this->get_average_content_score($date_range),
            'avg_seo_score' => $this->get_average_seo_score($date_range),
            'last_updated' => current_time('mysql')
        );
        
        return new WP_REST_Response($stats, 200);
    }
    
    /**
     * Analyze content
     */
    public function analyze_content($request) {
        $post_id = intval($request->get_param('post_id'));
        $content = $request->get_param('content');
        
        if (!$content) {
            $post = get_post($post_id);
            if (!$post) {
                return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
            }
            $content = $post->post_content;
        }
        
        $content_analyzer = new WPSI_Content_Analyzer();
        $analysis = $content_analyzer->analyze($content);
        
        // Save analysis results
        update_post_meta($post_id, '_wpsi_content_analysis', $analysis);
        update_post_meta($post_id, '_wpsi_analysis_date', current_time('mysql'));
        
        // Trigger webhook
        $this->trigger_webhook('content_analysis', array(
            'post_id' => $post_id,
            'analysis' => $analysis
        ));
        
        return new WP_REST_Response($analysis, 200);
    }
    
    /**
     * Get content analysis
     */
    public function get_content_analysis($request) {
        $post_id = intval($request->get_param('post_id'));
        
        $analysis = get_post_meta($post_id, '_wpsi_content_analysis', true);
        if (!$analysis) {
            return new WP_Error('analysis_not_found', 'Analysis not found', array('status' => 404));
        }
        
        $response = array(
            'post_id' => $post_id,
            'post_title' => get_the_title($post_id),
            'analysis_date' => get_post_meta($post_id, '_wpsi_analysis_date', true),
            'analysis' => $analysis
        );
        
        return new WP_REST_Response($response, 200);
    }
    
    /**
     * Get heatmaps
     */
    public function get_heatmaps($request) {
        global $wpdb;
        
        $post_id = $request->get_param('post_id');
        $date_range = $request->get_param('date_range');
        $limit = intval($request->get_param('limit'));
        
        $where_conditions = array();
        $where_values = array();
        
        if ($post_id) {
            $where_conditions[] = 'post_id = %d';
            $where_values[] = intval($post_id);
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
        
        $query = $wpdb->prepare(
            "SELECT h.*, p.post_title 
             FROM {$wpdb->prefix}wpsi_heatmaps h 
             LEFT JOIN {$wpdb->posts} p ON h.post_id = p.ID 
             {$where_clause} 
             ORDER BY h.created_at DESC 
             LIMIT %d",
            array_merge($where_values, array($limit))
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return new WP_REST_Response($results, 200);
    }
    
    /**
     * Get heatmap data for specific post
     */
    public function get_heatmap_data($request) {
        $post_id = intval($request->get_param('post_id'));
        
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT h.*, p.post_title 
             FROM {$wpdb->prefix}wpsi_heatmaps h 
             LEFT JOIN {$wpdb->posts} p ON h.post_id = p.ID 
             WHERE h.post_id = %d 
             ORDER BY h.created_at DESC",
            $post_id
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        if (empty($results)) {
            return new WP_Error('no_data', 'No heatmap data found', array('status' => 404));
        }
        
        // Process and aggregate data
        $aggregated_data = $this->aggregate_heatmap_data($results);
        
        return new WP_REST_Response($aggregated_data, 200);
    }
    
    /**
     * Get user journeys
     */
    public function get_journeys($request) {
        global $wpdb;
        
        $post_id = $request->get_param('post_id');
        $date_range = $request->get_param('date_range') ?: 'all';
        $limit = $request->get_param('limit') ?: 50;
        
        $where_conditions = array();
        $where_values = array();
        
        if ($post_id) {
            $where_conditions[] = 'j.post_id = %d';
            $where_values[] = intval($post_id);
        }
        
        if ($date_range !== 'all') {
            $where_conditions[] = "j.created_at >= DATE_SUB(NOW(), INTERVAL 1 $date_range)";
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $sql = "SELECT j.*, p.post_title 
                FROM {$wpdb->prefix}wpsi_user_journeys j
                LEFT JOIN {$wpdb->posts} p ON j.post_id = p.ID
                $where_clause
                ORDER BY j.created_at DESC
                LIMIT %d";
        
        $where_values[] = intval($limit);
        $sql = $wpdb->prepare($sql, $where_values);
        
        $journeys = $wpdb->get_results($sql);
        
        $data = array();
        foreach ($journeys as $journey) {
            $journey_data = json_decode($journey->journey_data, true);
            
            $data[] = array(
                'id' => $journey->id,
                'post_id' => $journey->post_id,
                'post_title' => $journey->post_title,
                'session_id' => $journey->session_id,
                'created_at' => $journey->created_at,
                'duration' => isset($journey_data['duration']) ? $journey_data['duration'] : 0,
                'interaction_count' => isset($journey_data['interactions']) ? count($journey_data['interactions']) : 0,
                'journey_data' => $journey_data
            );
        }
        
        return new WP_REST_Response($data, 200);
    }
    
    /**
     * Get journey details
     */
    public function get_journey_details($request) {
        $journey_id = intval($request->get_param('journey_id'));
        
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT j.*, p.post_title 
             FROM {$wpdb->prefix}wpsi_user_journeys j 
             LEFT JOIN {$wpdb->posts} p ON j.post_id = p.ID 
             WHERE j.id = %d",
            $journey_id
        );
        
        $result = $wpdb->get_row($query, ARRAY_A);
        
        if (!$result) {
            return new WP_Error('journey_not_found', 'Journey not found', array('status' => 404));
        }
        
        return new WP_REST_Response($result, 200);
    }
    
    /**
     * Analyze SEO
     */
    public function analyze_seo($request) {
        $post_id = intval($request->get_param('post_id'));
        
        $seo_checker = new WPSI_SEO_Checker();
        $seo_data = $seo_checker->analyze_post($post_id);
        
        // Trigger webhook
        $this->trigger_webhook('seo_update', array(
            'post_id' => $post_id,
            'seo_data' => $seo_data
        ));
        
        return new WP_REST_Response($seo_data, 200);
    }
    
    /**
     * Get SEO score
     */
    public function get_seo_score($request) {
        $post_id = intval($request->get_param('post_id'));
        
        $seo_score = get_post_meta($post_id, '_wpsi_seo_score', true);
        if (!$seo_score) {
            return new WP_Error('seo_score_not_found', 'SEO score not found', array('status' => 404));
        }
        
        $response = array(
            'post_id' => $post_id,
            'post_title' => get_the_title($post_id),
            'seo_score' => $seo_score
        );
        
        return new WP_REST_Response($response, 200);
    }
    
    /**
     * Get settings
     */
    public function get_settings($request) {
        $settings = array(
            'tracking_enabled' => get_option('wpsi_tracking_enabled', false),
            'ai_provider' => get_option('wpsi_ai_provider', 'none'),
            'privacy_compliant' => get_option('wpsi_privacy_compliant', true),
            'cookie_consent' => get_option('wpsi_cookie_consent', true),
            'data_retention' => get_option('wpsi_data_retention', 90),
            'heatmap_enabled' => get_option('wpsi_heatmap_enabled', true),
            'journey_enabled' => get_option('wpsi_journey_enabled', true)
        );
        
        return new WP_REST_Response($settings, 200);
    }
    
    /**
     * Update settings
     */
    public function update_settings($request) {
        $tracking_enabled = $request->get_param('tracking_enabled');
        $ai_provider = $request->get_param('ai_provider');
        $privacy_compliant = $request->get_param('privacy_compliant');
        
        update_option('wpsi_tracking_enabled', $tracking_enabled);
        update_option('wpsi_ai_provider', $ai_provider);
        update_option('wpsi_privacy_compliant', $privacy_compliant);
        
        $response = array(
            'message' => 'Settings updated successfully',
            'settings' => array(
                'tracking_enabled' => $tracking_enabled,
                'ai_provider' => $ai_provider,
                'privacy_compliant' => $privacy_compliant
            )
        );
        
        return new WP_REST_Response($response, 200);
    }
    
    /**
     * Export data
     */
    public function export_data($request) {
        $type = $request->get_param('type');
        $format = $request->get_param('format');
        $date_range = $request->get_param('date_range');
        
        $export_service = new WPSI_Export_Service();
        
        switch ($type) {
            case 'heatmaps':
                $data = $export_service->get_heatmap_data($date_range);
                break;
            case 'journeys':
                $data = $export_service->get_journey_data($date_range);
                break;
            case 'analysis':
                $data = $export_service->get_analysis_data($date_range);
                break;
            case 'all':
                $data = array(
                    'heatmaps' => $export_service->get_heatmap_data($date_range),
                    'journeys' => $export_service->get_journey_data($date_range),
                    'analysis' => $export_service->get_analysis_data($date_range)
                );
                break;
            default:
                return new WP_Error('invalid_type', 'Invalid export type', array('status' => 400));
        }
        
        if ($format === 'csv') {
            return $this->output_csv($data, $type . '-export');
        } else {
            return new WP_REST_Response($data, 200);
        }
    }
    
    /**
     * Get webhooks
     */
    public function get_webhooks($request) {
        $webhooks = get_option('wpsi_webhooks', array());
        return new WP_REST_Response($webhooks, 200);
    }
    
    /**
     * Create webhook
     */
    public function create_webhook($request) {
        $event = $request->get_param('event');
        $url = $request->get_param('url');
        $secret = $request->get_param('secret');
        
        $webhooks = get_option('wpsi_webhooks', array());
        
        $webhook_id = uniqid('wpsi_');
        $webhooks[$webhook_id] = array(
            'id' => $webhook_id,
            'event' => $event,
            'url' => $url,
            'secret' => $secret,
            'created_at' => current_time('mysql'),
            'active' => true
        );
        
        update_option('wpsi_webhooks', $webhooks);
        
        return new WP_REST_Response($webhooks[$webhook_id], 201);
    }
    
    /**
     * Delete webhook
     */
    public function delete_webhook($request) {
        $webhook_id = $request->get_param('webhook_id');
        
        $webhooks = get_option('wpsi_webhooks', array());
        
        if (!isset($webhooks[$webhook_id])) {
            return new WP_Error('webhook_not_found', 'Webhook not found', array('status' => 404));
        }
        
        unset($webhooks[$webhook_id]);
        update_option('wpsi_webhooks', $webhooks);
        
        return new WP_REST_Response(array('message' => 'Webhook deleted successfully'), 200);
    }
    
    /**
     * Trigger webhook
     */
    private function trigger_webhook($event, $data) {
        $webhooks = get_option('wpsi_webhooks', array());
        
        foreach ($webhooks as $webhook) {
            if ($webhook['event'] === $event && $webhook['active']) {
                $this->send_webhook($webhook, $data);
            }
        }
    }
    
    /**
     * Send webhook
     */
    private function send_webhook($webhook, $data) {
        $payload = array(
            'event' => $webhook['event'],
            'timestamp' => current_time('mysql'),
            'data' => $data
        );
        
        $headers = array(
            'Content-Type' => 'application/json',
            'User-Agent' => 'WP-Smart-Insights/1.0'
        );
        
        if ($webhook['secret']) {
            $signature = hash_hmac('sha256', json_encode($payload), $webhook['secret']);
            $headers['X-WPSI-Signature'] = $signature;
        }
        
        wp_remote_post($webhook['url'], array(
            'headers' => $headers,
            'body' => json_encode($payload),
            'timeout' => 30
        ));
    }
    
    /**
     * Helper methods
     */
    private function get_analyzed_posts_count($date_range) {
        // Implementation for getting analyzed posts count
        return 0;
    }
    
    private function get_heatmap_sessions_count($date_range) {
        // Implementation for getting heatmap sessions count
        return 0;
    }
    
    private function get_user_journeys_count($date_range) {
        // Implementation for getting user journeys count
        return 0;
    }
    
    private function get_average_content_score($date_range) {
        // Implementation for getting average content score
        return 0;
    }
    
    private function get_average_seo_score($date_range) {
        // Implementation for getting average SEO score
        return 0;
    }
    
    private function get_date_condition($date_range) {
        // Implementation for getting date condition
        return null;
    }
    
    private function aggregate_heatmap_data($results) {
        // Implementation for aggregating heatmap data
        return array();
    }
    
    private function output_csv($data, $filename) {
        // Implementation for CSV output
        return new WP_REST_Response($data, 200);
    }
} 