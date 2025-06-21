<?php
/**
 * Plugin Name: WP Smart Insights – Content Intelligence & UX Heatmap
 * Plugin URI: https://github.com/sadrul/wp-smart-insights
 * Description: A comprehensive WordPress plugin that combines AI-driven content analysis, user engagement heatmaps, and SEO scoring in one dashboard to help site owners optimize content quality and user experience.
 * Version: 1.0.0
 * Author: K M Sadrul Ula
 * Author URI: https://github.com/sadrul
 * Author Email: kmsadrulula@gmail.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-smart-insights
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package WP_Smart_Insights
 * @author K M Sadrul Ula
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPSI_VERSION', '1.0.0');
define('WPSI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPSI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WPSI_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Main plugin class
class WP_Smart_Insights {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_action('wp_ajax_wpsi_save_heatmap_data', array($this, 'save_heatmap_data'));
        add_action('wp_ajax_nopriv_wpsi_save_heatmap_data', array($this, 'save_heatmap_data'));
        add_action('wp_ajax_wpsi_analyze_content', array($this, 'analyze_content'));
        add_action('wp_ajax_wpsi_get_seo_score', array($this, 'get_seo_score'));
        add_action('wp_ajax_wpsi_save_user_journey', array($this, 'save_user_journey'));
        add_action('wp_ajax_nopriv_wpsi_save_user_journey', array($this, 'save_user_journey'));
        
        // Enhancement AJAX handlers
        add_action('wp_ajax_wpsi_save_ai_config', array($this, 'save_ai_config'));
        add_action('wp_ajax_wpsi_analyze_content_ai', array($this, 'analyze_content_ai'));
        add_action('wp_ajax_wpsi_batch_analyze', array($this, 'batch_analyze'));
        add_action('wp_ajax_wpsi_get_recommendations', array($this, 'get_recommendations'));
        
        add_action('wp_ajax_wpsi_export_heatmaps', array($this, 'export_heatmaps'));
        add_action('wp_ajax_wpsi_export_journeys', array($this, 'export_journeys'));
        add_action('wp_ajax_wpsi_export_analytics', array($this, 'export_analytics'));
        add_action('wp_ajax_wpsi_export_content_analysis', array($this, 'export_content_analysis'));
        add_action('wp_ajax_wpsi_bulk_export', array($this, 'bulk_export'));
        add_action('wp_ajax_wpsi_get_export_history', array($this, 'get_export_history'));
        
        add_action('wp_ajax_wpsi_save_email_config', array($this, 'save_email_config'));
        add_action('wp_ajax_wpsi_send_test_email', array($this, 'send_test_email'));
        add_action('wp_ajax_wpsi_save_notification_settings', array($this, 'save_notification_settings'));
        add_action('wp_ajax_wpsi_save_templates', array($this, 'save_templates'));
        add_action('wp_ajax_wpsi_reset_templates', array($this, 'reset_templates'));
        add_action('wp_ajax_wpsi_send_manual_notification', array($this, 'send_manual_notification'));
        add_action('wp_ajax_wpsi_get_notification_history', array($this, 'get_notification_history'));
        add_action('wp_ajax_wpsi_clear_notification_history', array($this, 'clear_notification_history'));
        
        add_action('wp_ajax_wpsi_save_analytics_config', array($this, 'save_analytics_config'));
        add_action('wp_ajax_wpsi_save_webhook_config', array($this, 'save_webhook_config'));
        add_action('wp_ajax_wpsi_test_webhook', array($this, 'test_webhook'));
        add_action('wp_ajax_wpsi_generate_analytics_report', array($this, 'generate_analytics_report'));
        add_action('wp_ajax_wpsi_get_realtime_analytics', array($this, 'get_realtime_analytics'));
        add_action('wp_ajax_wpsi_get_recent_events', array($this, 'get_recent_events'));
        add_action('wp_ajax_wpsi_cleanup_old_data', array($this, 'cleanup_old_data'));
        add_action('wp_ajax_wpsi_export_analytics_data', array($this, 'export_analytics_data'));
        add_action('wp_ajax_wpsi_get_webhook_logs', array($this, 'get_webhook_logs'));
        add_action('wp_ajax_wpsi_clear_webhook_logs', array($this, 'clear_webhook_logs'));
        
        // AI Service AJAX handlers
        add_action('wp_ajax_wpsi_test_ai_connection', array($this, 'test_ai_connection'));
        add_action('wp_ajax_wpsi_get_ai_models', array($this, 'get_ai_models'));
        add_action('wp_ajax_wpsi_get_ai_usage', array($this, 'get_ai_usage'));
        add_action('wp_ajax_wpsi_reset_ai_config', array($this, 'reset_ai_config'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain for internationalization
        load_plugin_textdomain('wp-smart-insights', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components
        $this->init_components();
    }
    
    private function init_components() {
        // Include required files
        require_once WPSI_PLUGIN_PATH . 'includes/class-content-analyzer.php';
        require_once WPSI_PLUGIN_PATH . 'includes/class-heatmap-tracker.php';
        require_once WPSI_PLUGIN_PATH . 'includes/class-seo-checker.php';
        require_once WPSI_PLUGIN_PATH . 'includes/class-user-journey.php';
        require_once WPSI_PLUGIN_PATH . 'includes/class-privacy-manager.php';
        
        // Include enhancement services
        require_once WPSI_PLUGIN_PATH . 'includes/class-ai-service.php';
        require_once WPSI_PLUGIN_PATH . 'includes/class-export-service.php';
        require_once WPSI_PLUGIN_PATH . 'includes/class-notification-service.php';
        require_once WPSI_PLUGIN_PATH . 'includes/class-rest-api.php';
        require_once WPSI_PLUGIN_PATH . 'includes/class-analytics-service.php';
        
        // Initialize components
        new WPSI_Content_Analyzer();
        new WPSI_Heatmap_Tracker();
        new WPSI_SEO_Checker();
        new WPSI_User_Journey();
        new WPSI_Privacy_Manager();
        
        // Initialize enhancement services
        new WPSI_AI_Service();
        new WPSI_Export_Service();
        new WPSI_Notification_Service();
        new WPSI_REST_API();
        new WPSI_Analytics_Service();
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Smart Insights', 'wp-smart-insights'),
            __('Smart Insights', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights',
            array($this, 'admin_page'),
            'dashicons-chart-area',
            30
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('Dashboard', 'wp-smart-insights'),
            __('Dashboard', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('Heatmaps', 'wp-smart-insights'),
            __('Heatmaps', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights-heatmaps',
            array($this, 'heatmaps_page')
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('Content Analysis', 'wp-smart-insights'),
            __('Content Analysis', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights-content',
            array($this, 'content_analysis_page')
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('SEO Checker', 'wp-smart-insights'),
            __('SEO Checker', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights-seo',
            array($this, 'seo_page')
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('User Journeys', 'wp-smart-insights'),
            __('User Journeys', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights-journeys',
            array($this, 'journeys_page')
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('Settings', 'wp-smart-insights'),
            __('Settings', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('AI Analysis', 'wp-smart-insights'),
            __('AI Analysis', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights-ai',
            array($this, 'ai_analysis_page')
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('Export Data', 'wp-smart-insights'),
            __('Export Data', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights-export',
            array($this, 'export_page')
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('Notifications', 'wp-smart-insights'),
            __('Notifications', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights-notifications',
            array($this, 'notifications_page')
        );
        
        add_submenu_page(
            'wp-smart-insights',
            __('Analytics & Webhooks', 'wp-smart-insights'),
            __('Analytics & Webhooks', 'wp-smart-insights'),
            'manage_options',
            'wp-smart-insights-analytics',
            array($this, 'analytics_page')
        );
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'wp-smart-insights') === false) {
            return;
        }
        
        wp_enqueue_script('wpsi-admin', WPSI_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), WPSI_VERSION, true);
        wp_enqueue_style('wpsi-admin', WPSI_PLUGIN_URL . 'assets/css/admin.css', array(), WPSI_VERSION);
        
        wp_localize_script('wpsi-admin', 'wpsi_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpsi_nonce'),
            'strings' => array(
                'analyzing' => __('Analyzing content...', 'wp-smart-insights'),
                'saving' => __('Saving...', 'wp-smart-insights'),
                'error' => __('An error occurred', 'wp-smart-insights'),
            )
        ));
    }
    
    public function frontend_enqueue_scripts() {
        if (is_admin()) {
            return;
        }
        
        // Only load if tracking is enabled
        if (get_option('wpsi_tracking_enabled', false)) {
            wp_enqueue_script('wpsi-frontend', WPSI_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), WPSI_VERSION, true);
            wp_localize_script('wpsi-frontend', 'wpsi_frontend', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wpsi_frontend_nonce'),
                'post_id' => get_the_ID(),
                'page_url' => get_permalink(),
            ));
        }
    }
    
    public function admin_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/dashboard.php';
    }
    
    public function heatmaps_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/heatmaps.php';
    }
    
    public function content_analysis_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/content-analysis.php';
    }
    
    public function seo_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/seo.php';
    }
    
    public function journeys_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/journeys.php';
    }
    
    public function settings_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/settings.php';
    }
    
    public function ai_analysis_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/ai-analysis.php';
    }

    public function export_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/export.php';
    }

    public function notifications_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/notifications.php';
    }

    public function analytics_page() {
        include WPSI_PLUGIN_PATH . 'admin/views/analytics.php';
    }
    
    public function save_heatmap_data() {
        check_ajax_referer('wpsi_frontend_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        $click_data = sanitize_text_field($_POST['click_data']);
        $scroll_data = sanitize_text_field($_POST['scroll_data']);
        $hover_data = sanitize_text_field($_POST['hover_data']);
        
        // Save to database
        $heatmap_data = get_post_meta($post_id, '_wpsi_heatmap_data', true);
        if (!is_array($heatmap_data)) {
            $heatmap_data = array();
        }
        
        $heatmap_data[] = array(
            'timestamp' => current_time('mysql'),
            'clicks' => $click_data,
            'scrolls' => $scroll_data,
            'hovers' => $hover_data,
        );
        
        update_post_meta($post_id, '_wpsi_heatmap_data', $heatmap_data);
        
        wp_send_json_success();
    }
    
    public function analyze_content() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $content = wp_kses_post($_POST['content']);
        $post_id = intval($_POST['post_id']);
        
        // Initialize content analyzer
        $analyzer = new WPSI_Content_Analyzer();
        $analysis = $analyzer->analyze($content);
        
        // Save analysis results
        update_post_meta($post_id, '_wpsi_content_analysis', $analysis);
        
        wp_send_json_success($analysis);
    }
    
    public function get_seo_score() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        // Initialize SEO checker
        $seo_checker = new WPSI_SEO_Checker();
        $seo_score = $seo_checker->analyze_post($post_id);
        
        wp_send_json_success($seo_score);
    }
    
    public function save_user_journey() {
        check_ajax_referer('wpsi_frontend_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        $journey_data = sanitize_text_field($_POST['journey_data']);
        
        // Save to database
        $journeys = get_post_meta($post_id, '_wpsi_user_journeys', true);
        if (!is_array($journeys)) {
            $journeys = array();
        }
        
        $journeys[] = array(
            'timestamp' => current_time('mysql'),
            'journey' => $journey_data,
        );
        
        update_post_meta($post_id, '_wpsi_user_journeys', $journeys);
        
        wp_send_json_success();
    }
    
    public function activate() {
        // Create database tables
        $this->create_tables();
        
        // Set default options
        add_option('wpsi_tracking_enabled', false);
        add_option('wpsi_ai_api_key', '');
        add_option('wpsi_privacy_compliant', true);
        add_option('wpsi_cookie_consent', true);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpsi_heatmaps (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            click_data longtext NOT NULL,
            scroll_data longtext NOT NULL,
            hover_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id)
        ) $charset_collate;";
        
        $sql .= "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpsi_user_journeys (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            journey_data longtext NOT NULL,
            session_id varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY session_id (session_id)
        ) $charset_collate;";
        
        $sql .= "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpsi_analytics_events (
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
        
        $sql .= "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpsi_webhook_logs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            webhook_url varchar(500) NOT NULL,
            payload longtext NOT NULL,
            response longtext,
            status varchar(20) NOT NULL,
            attempts int(11) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $sql .= "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpsi_notifications (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            notification_type varchar(50) NOT NULL,
            subject varchar(255) NOT NULL,
            message longtext NOT NULL,
            recipient_email varchar(255) NOT NULL,
            status varchar(20) NOT NULL,
            sent_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY notification_type (notification_type),
            KEY status (status),
            KEY sent_at (sent_at)
        ) $charset_collate;";
        
        $sql .= "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpsi_exports (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            export_type varchar(50) NOT NULL,
            format varchar(20) NOT NULL,
            filename varchar(255) NOT NULL,
            download_url varchar(500) NOT NULL,
            records_count int(11) DEFAULT 0,
            file_size int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY export_type (export_type),
            KEY format (format),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    // AI Service AJAX Handlers
    public function save_ai_config() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        update_option('wpsi_ai_provider', sanitize_text_field($_POST['provider']));
        update_option('wpsi_ai_api_key', sanitize_text_field($_POST['api_key']));
        update_option('wpsi_ai_model', sanitize_text_field($_POST['model']));
        
        wp_send_json_success();
    }
    
    public function analyze_content_ai() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $content = wp_kses_post($_POST['content']);
        $analysis_type = sanitize_text_field($_POST['analysis_type']);
        
        $ai_service = new WPSI_AI_Service();
        $result = $ai_service->analyze_content($content, $analysis_type);
        
        wp_send_json_success($result);
    }
    
    public function batch_analyze() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $post_type = sanitize_text_field($_POST['post_type']);
        $limit = intval($_POST['limit']);
        
        $ai_service = new WPSI_AI_Service();
        $result = $ai_service->batch_analyze($post_type, $limit);
        
        wp_send_json_success($result);
    }
    
    public function get_recommendations() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $focus = sanitize_text_field($_POST['focus']);
        
        $ai_service = new WPSI_AI_Service();
        $result = $ai_service->get_recommendations($focus);
        
        wp_send_json_success($result);
    }
    
    // Export Service AJAX Handlers
    public function export_heatmaps() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $export_service = new WPSI_Export_Service();
        $result = $export_service->export_heatmaps($_POST);
        
        wp_send_json_success($result);
    }
    
    public function export_journeys() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $export_service = new WPSI_Export_Service();
        $result = $export_service->export_journeys($_POST);
        
        wp_send_json_success($result);
    }
    
    public function export_analytics() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $export_service = new WPSI_Export_Service();
        $result = $export_service->export_analytics($_POST);
        
        wp_send_json_success($result);
    }
    
    public function export_content_analysis() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $export_service = new WPSI_Export_Service();
        $result = $export_service->export_content_analysis($_POST);
        
        wp_send_json_success($result);
    }
    
    public function bulk_export() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $export_service = new WPSI_Export_Service();
        $result = $export_service->bulk_export($_POST);
        
        wp_send_json_success($result);
    }
    
    public function get_export_history() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $export_service = new WPSI_Export_Service();
        $result = $export_service->get_export_history();
        
        wp_send_json_success($result);
    }
    
    // Notification Service AJAX Handlers
    public function save_email_config() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $notification_service = new WPSI_Notification_Service();
        $result = $notification_service->save_email_config($_POST);
        
        wp_send_json_success($result);
    }
    
    public function send_test_email() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $notification_service = new WPSI_Notification_Service();
        $result = $notification_service->send_test_email();
        
        wp_send_json_success($result);
    }
    
    public function save_notification_settings() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $notification_service = new WPSI_Notification_Service();
        $result = $notification_service->save_notification_settings($_POST);
        
        wp_send_json_success($result);
    }
    
    public function save_templates() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $notification_service = new WPSI_Notification_Service();
        $result = $notification_service->save_templates($_POST);
        
        wp_send_json_success($result);
    }
    
    public function reset_templates() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $notification_service = new WPSI_Notification_Service();
        $result = $notification_service->reset_templates();
        
        wp_send_json_success($result);
    }
    
    public function send_manual_notification() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $notification_service = new WPSI_Notification_Service();
        $result = $notification_service->send_manual_notification($_POST);
        
        wp_send_json_success($result);
    }
    
    public function get_notification_history() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $notification_service = new WPSI_Notification_Service();
        $result = $notification_service->get_notification_history($_POST);
        
        wp_send_json_success($result);
    }
    
    public function clear_notification_history() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $notification_service = new WPSI_Notification_Service();
        $result = $notification_service->clear_notification_history();
        
        wp_send_json_success($result);
    }
    
    // Analytics Service AJAX Handlers
    public function save_analytics_config() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->save_analytics_config($_POST);
        
        wp_send_json_success($result);
    }
    
    public function save_webhook_config() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->save_webhook_config($_POST);
        
        wp_send_json_success($result);
    }
    
    public function test_webhook() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->test_webhook();
        
        wp_send_json_success($result);
    }
    
    public function generate_analytics_report() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->generate_analytics_report($_POST);
        
        wp_send_json_success($result);
    }
    
    public function get_realtime_analytics() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->get_realtime_analytics();
        
        wp_send_json_success($result);
    }
    
    public function get_recent_events() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->get_recent_events();
        
        wp_send_json_success($result);
    }
    
    public function cleanup_old_data() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->cleanup_old_data();
        
        wp_send_json_success($result);
    }
    
    public function export_analytics_data() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->export_analytics_data();
        
        wp_send_json_success($result);
    }
    
    public function get_webhook_logs() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->get_webhook_logs($_POST);
        
        wp_send_json_success($result);
    }
    
    public function clear_webhook_logs() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $analytics_service = new WPSI_Analytics_Service();
        $result = $analytics_service->clear_webhook_logs();
        
        wp_send_json_success($result);
    }
    
    // AI Service AJAX handlers
    public function test_ai_connection() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $ai_service = new WPSI_AI_Service();
        $result = $ai_service->test_ai_connection();
        
        wp_send_json_success($result);
    }
    
    public function get_ai_models() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $ai_service = new WPSI_AI_Service();
        $result = $ai_service->get_ai_models();
        
        wp_send_json_success($result);
    }
    
    public function get_ai_usage() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $ai_service = new WPSI_AI_Service();
        $result = $ai_service->get_ai_usage();
        
        wp_send_json_success($result);
    }
    
    public function reset_ai_config() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        $ai_service = new WPSI_AI_Service();
        $result = $ai_service->reset_ai_config();
        
        wp_send_json_success($result);
    }
}

// Initialize the plugin
function wpsi_init() {
    return WP_Smart_Insights::get_instance();
}

// Start the plugin
wpsi_init(); 