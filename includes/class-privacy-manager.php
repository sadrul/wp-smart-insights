<?php
/**
 * Privacy Manager Class
 * 
 * Handles GDPR compliance, cookie consent, and privacy-first design
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPSI_Privacy_Manager {
    
    public function __construct() {
        add_action('wp_footer', array($this, 'inject_consent_banner'));
        add_action('wp_ajax_wpsi_save_consent', array($this, 'save_consent'));
        add_action('wp_ajax_nopriv_wpsi_save_consent', array($this, 'save_consent'));
        add_action('wp_ajax_wpsi_export_user_data', array($this, 'export_user_data'));
        add_action('wp_ajax_wpsi_delete_user_data', array($this, 'delete_user_data'));
        add_action('wp_ajax_wpsi_get_privacy_settings', array($this, 'get_privacy_settings'));
        add_action('wp_ajax_wpsi_update_privacy_settings', array($this, 'update_privacy_settings'));
    }
    
    public function inject_consent_banner() {
        // Only show if privacy compliance is enabled and consent is required
        if (!get_option('wpsi_privacy_compliant', true) || !get_option('wpsi_cookie_consent', true)) {
            return;
        }
        
        // Don't show if user has already given consent
        if ($this->has_user_consent()) {
            return;
        }
        
        ?>
        <div id="wpsi-consent-banner" class="wpsi-consent-banner">
            <div class="wpsi-consent-content">
                <div class="wpsi-consent-text">
                    <h3><?php esc_html_e('We value your privacy', 'wp-smart-insights'); ?></h3>
                    <p><?php esc_html_e('This website uses Smart Insights to analyze content performance and user experience. We collect anonymous interaction data to improve our content and user experience. No personal information is collected or stored.', 'wp-smart-insights'); ?></p>
                    <p><?php esc_html_e('You can learn more about what data we collect and how we use it in our', 'wp-smart-insights'); ?> <a href="<?php echo esc_url($this->get_privacy_policy_url()); ?>" target="_blank"><?php esc_html_e('Privacy Policy', 'wp-smart-insights'); ?></a>.</p>
                </div>
                <div class="wpsi-consent-actions">
                    <button type="button" class="wpsi-consent-accept button button-primary">
                        <?php esc_html_e('Accept', 'wp-smart-insights'); ?>
                    </button>
                    <button type="button" class="wpsi-consent-decline button">
                        <?php esc_html_e('Decline', 'wp-smart-insights'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <style>
        .wpsi-consent-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #ddd;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 999999;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        .wpsi-consent-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        
        .wpsi-consent-text h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #333;
        }
        
        .wpsi-consent-text p {
            margin: 0 0 8px 0;
            font-size: 14px;
            line-height: 1.4;
            color: #666;
        }
        
        .wpsi-consent-text a {
            color: #0073aa;
            text-decoration: none;
        }
        
        .wpsi-consent-text a:hover {
            text-decoration: underline;
        }
        
        .wpsi-consent-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .wpsi-consent-actions .button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        
        .wpsi-consent-actions .button-primary {
            background: #0073aa;
            color: #fff;
        }
        
        .wpsi-consent-actions .button-primary:hover {
            background: #005a87;
        }
        
        .wpsi-consent-actions .button:not(.button-primary) {
            background: #f1f1f1;
            color: #333;
        }
        
        .wpsi-consent-actions .button:not(.button-primary):hover {
            background: #e1e1e1;
        }
        
        @media (max-width: 768px) {
            .wpsi-consent-content {
                flex-direction: column;
                text-align: center;
            }
            
            .wpsi-consent-actions {
                justify-content: center;
            }
        }
        </style>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Handle consent acceptance
            $('.wpsi-consent-accept').on('click', function() {
                saveConsent(true);
            });
            
            // Handle consent decline
            $('.wpsi-consent-decline').on('click', function() {
                saveConsent(false);
            });
            
            function saveConsent(accepted) {
                $.post('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                    action: 'wpsi_save_consent',
                    nonce: '<?php echo esc_js(wp_create_nonce('wpsi_consent_nonce')); ?>',
                    accepted: accepted ? 1 : 0
                }, function(response) {
                    if (response.success) {
                        $('#wpsi-consent-banner').fadeOut();
                        
                        // Set cookie
                        var expiryDate = new Date();
                        expiryDate.setFullYear(expiryDate.getFullYear() + 1);
                        document.cookie = 'wpsi_consent=' + (accepted ? '1' : '0') + '; expires=' + expiryDate.toUTCString() + '; path=/';
                        
                        // Reload page to enable/disable tracking
                        if (accepted) {
                            location.reload();
                        }
                    }
                });
            }
        });
        </script>
        <?php
    }
    
    public function save_consent() {
        check_ajax_referer('wpsi_consent_nonce', 'nonce');
        
        $accepted = intval($_POST['accepted']);
        
        // Store consent in database
        $consent_data = array(
            'accepted' => $accepted,
            'timestamp' => current_time('mysql'),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        );
        
        // Store in options (you might want to use a more sophisticated storage method)
        $consents = get_option('wpsi_user_consents', array());
        $consents[] = $consent_data;
        
        // Keep only last 1000 consents
        if (count($consents) > 1000) {
            $consents = array_slice($consents, -1000);
        }
        
        update_option('wpsi_user_consents', $consents);
        
        wp_send_json_success();
    }
    
    public function export_user_data() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = intval($_POST['user_id']);
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            wp_send_json_error('User not found');
        }
        
        $export_data = array(
            'user_info' => array(
                'id' => $user->ID,
                'email' => $user->user_email,
                'username' => $user->user_login,
                'display_name' => $user->display_name,
                'registered' => $user->user_registered
            ),
            'consent_data' => $this->get_user_consent_data($user_id),
            'interaction_data' => $this->get_user_interaction_data($user_id),
            'export_date' => current_time('mysql'),
            'exported_by' => get_current_user_id()
        );
        
        // Generate JSON file
        $filename = 'wpsi-user-data-' . $user_id . '-' . date('Y-m-d-H-i-s') . '.json';
        $filepath = wp_upload_dir()['basedir'] . '/wpsi-exports/' . $filename;
        
        // Create directory if it doesn't exist
        wp_mkdir_p(dirname($filepath));
        
        file_put_contents($filepath, json_encode($export_data, JSON_PRETTY_PRINT));
        
        wp_send_json_success(array(
            'file_url' => wp_upload_dir()['baseurl'] . '/wpsi-exports/' . $filename,
            'filename' => $filename
        ));
    }
    
    public function delete_user_data() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = intval($_POST['user_id']);
        
        // Delete user consent data
        $consents = get_option('wpsi_user_consents', array());
        $consents = array_filter($consents, function($consent) use ($user_id) {
            return !isset($consent['user_id']) || $consent['user_id'] != $user_id;
        });
        update_option('wpsi_user_consents', $consents);
        
        // Delete interaction data (this would need to be implemented based on your data storage)
        // For now, we'll just return success
        
        wp_send_json_success();
    }
    
    public function get_privacy_settings() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $settings = array(
            'privacy_compliant' => get_option('wpsi_privacy_compliant', true),
            'cookie_consent' => get_option('wpsi_cookie_consent', true),
            'data_retention_days' => get_option('wpsi_data_retention_days', 365),
            'anonymize_ip' => get_option('wpsi_anonymize_ip', true),
            'respect_dnt' => get_option('wpsi_respect_dnt', true),
            'privacy_policy_url' => get_option('wpsi_privacy_policy_url', ''),
            'data_processing_basis' => get_option('wpsi_data_processing_basis', 'legitimate_interest')
        );
        
        wp_send_json_success($settings);
    }
    
    public function update_privacy_settings() {
        check_ajax_referer('wpsi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $settings = array(
            'wpsi_privacy_compliant' => boolval($_POST['privacy_compliant']),
            'wpsi_cookie_consent' => boolval($_POST['cookie_consent']),
            'wpsi_data_retention_days' => intval($_POST['data_retention_days']),
            'wpsi_anonymize_ip' => boolval($_POST['anonymize_ip']),
            'wpsi_respect_dnt' => boolval($_POST['respect_dnt']),
            'wpsi_privacy_policy_url' => esc_url_raw($_POST['privacy_policy_url']),
            'wpsi_data_processing_basis' => sanitize_text_field($_POST['data_processing_basis'])
        );
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        wp_send_json_success();
    }
    
    public function has_user_consent() {
        if (!get_option('wpsi_cookie_consent', true)) {
            return true;
        }
        
        // Check for consent cookie
        return isset($_COOKIE['wpsi_consent']) && $_COOKIE['wpsi_consent'] === '1';
    }
    
    public function should_track_user() {
        // Check if tracking is enabled
        if (!get_option('wpsi_tracking_enabled', false)) {
            return false;
        }
        
        // Check privacy compliance
        if (get_option('wpsi_privacy_compliant', true)) {
            // Check for user consent
            if (!$this->has_user_consent()) {
                return false;
            }
            
            // Check Do Not Track header
            if (get_option('wpsi_respect_dnt', true) && $this->has_dnt_header()) {
                return false;
            }
        }
        
        return true;
    }
    
    public function anonymize_ip($ip) {
        if (!get_option('wpsi_anonymize_ip', true)) {
            return $ip;
        }
        
        // Anonymize IPv4 by removing last octet
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip);
        }
        
        // Anonymize IPv6 by removing last 80 bits
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            $anonymized = array_slice($parts, 0, 4);
            while (count($anonymized) < 8) {
                $anonymized[] = '0000';
            }
            return implode(':', $anonymized);
        }
        
        return $ip;
    }
    
    public function cleanup_expired_data() {
        $retention_days = get_option('wpsi_data_retention_days', 365);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        // Clean up old consent data
        $consents = get_option('wpsi_user_consents', array());
        $consents = array_filter($consents, function($consent) use ($cutoff_date) {
            return strtotime($consent['timestamp']) > strtotime($cutoff_date);
        });
        update_option('wpsi_user_consents', $consents);
        
        // Clean up old heatmap data
        $this->cleanup_old_post_meta('_wpsi_heatmap_data', $cutoff_date);
        
        // Clean up old user journey data
        $this->cleanup_old_post_meta('_wpsi_user_journeys', $cutoff_date);
        
        // Clean up old analysis data
        $this->cleanup_old_post_meta('_wpsi_content_analysis', $cutoff_date);
        $this->cleanup_old_post_meta('_wpsi_seo_analysis', $cutoff_date);
    }
    
    private function cleanup_old_post_meta($meta_key, $cutoff_date) {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare(
            "DELETE pm FROM {$wpdb->postmeta} pm 
             INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
             WHERE pm.meta_key = %s 
             AND p.post_modified < %s",
            $meta_key,
            $cutoff_date
        ));
    }
    
    private function get_user_consent_data($user_id) {
        $consents = get_option('wpsi_user_consents', array());
        return array_filter($consents, function($consent) use ($user_id) {
            return isset($consent['user_id']) && $consent['user_id'] == $user_id;
        });
    }
    
    private function get_user_interaction_data($user_id) {
        // This would need to be implemented based on how you store user interaction data
        // For now, return empty array
        return array();
    }
    
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
    
    private function has_dnt_header() {
        return isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] === '1';
    }
    
    private function get_privacy_policy_url() {
        $url = get_option('wpsi_privacy_policy_url', '');
        if (!empty($url)) {
            return $url;
        }
        
        // Try to find privacy policy page
        $privacy_page = get_option('wp_page_for_privacy_policy');
        if ($privacy_page) {
            return get_permalink($privacy_page);
        }
        
        return home_url('/privacy-policy/');
    }
    
    public function generate_privacy_policy_content() {
        $content = array(
            'title' => __('Smart Insights Data Collection', 'wp-smart-insights'),
            'sections' => array(
                array(
                    'title' => __('What data we collect', 'wp-smart-insights'),
                    'content' => __('Smart Insights collects anonymous interaction data including mouse movements, clicks, scroll depth, and page interactions. No personal information such as names, email addresses, or IP addresses are stored or processed.', 'wp-smart-insights')
                ),
                array(
                    'title' => __('How we use the data', 'wp-smart-insights'),
                    'content' => __('The collected data is used solely to improve website content, user experience, and performance. We analyze user behavior patterns to optimize page layouts, content structure, and call-to-action placement.', 'wp-smart-insights')
                ),
                array(
                    'title' => __('Data retention', 'wp-smart-insights'),
                    // translators: %d is the number of days data is retained
                    'content' => sprintf(__('Data is retained for %d days and then automatically deleted. You can request deletion of your data at any time by contacting the website administrator.', 'wp-smart-insights'), get_option('wpsi_data_retention_days', 365))
                ),
                array(
                    'title' => __('Your rights', 'wp-smart-insights'),
                    'content' => __('You have the right to withdraw consent at any time, request access to your data, request deletion of your data, and lodge a complaint with supervisory authorities.', 'wp-smart-insights')
                ),
                array(
                    'title' => __('Contact information', 'wp-smart-insights'),
                    'content' => __('For privacy-related questions or requests, please contact the website administrator.', 'wp-smart-insights')
                )
            )
        );
        
        return $content;
    }
} 