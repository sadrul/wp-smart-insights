<?php
/**
 * Settings View
 * 
 * Plugin settings and configuration page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['wpsi_save_settings']) && wp_verify_nonce($_POST['wpsi_settings_nonce'], 'wpsi_save_settings')) {
    // General Settings
    update_option('wpsi_tracking_enabled', isset($_POST['tracking_enabled']));
    update_option('wpsi_ai_api_key', sanitize_text_field($_POST['ai_api_key']));
    
    // Privacy Settings
    update_option('wpsi_privacy_compliant', isset($_POST['privacy_compliant']));
    update_option('wpsi_cookie_consent', isset($_POST['cookie_consent']));
    update_option('wpsi_data_retention_days', intval($_POST['data_retention_days']));
    update_option('wpsi_anonymize_ip', isset($_POST['anonymize_ip']));
    update_option('wpsi_respect_dnt', isset($_POST['respect_dnt']));
    update_option('wpsi_privacy_policy_url', esc_url_raw($_POST['privacy_policy_url']));
    update_option('wpsi_data_processing_basis', sanitize_text_field($_POST['data_processing_basis']));
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'smart-insights-content-intelligence-ux-heatmap') . '</p></div>';
}

// Get current settings
$tracking_enabled = get_option('wpsi_tracking_enabled', false);
$ai_api_key = get_option('wpsi_ai_api_key', '');
$privacy_compliant = get_option('wpsi_privacy_compliant', true);
$cookie_consent = get_option('wpsi_cookie_consent', true);
$data_retention_days = get_option('wpsi_data_retention_days', 365);
$anonymize_ip = get_option('wpsi_anonymize_ip', true);
$respect_dnt = get_option('wpsi_respect_dnt', true);
$privacy_policy_url = get_option('wpsi_privacy_policy_url', '');
$data_processing_basis = get_option('wpsi_data_processing_basis', 'legitimate_interest');
?>

<div class="wrap">
    <h1><?php _e('Smart Insights Settings', 'smart-insights-content-intelligence-ux-heatmap'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wpsi_save_settings', 'wpsi_settings_nonce'); ?>
        
        <div class="wpsi-settings-container">
            <!-- General Settings -->
            <div class="wpsi-settings-section">
                <h2><?php _e('General Settings', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="tracking_enabled"><?php _e('Enable Tracking', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="tracking_enabled" name="tracking_enabled" value="1" <?php checked($tracking_enabled); ?> />
                            <p class="description"><?php _e('Enable user interaction tracking for heatmaps and user journeys.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ai_api_key"><?php _e('AI API Key', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="ai_api_key" name="ai_api_key" value="<?php echo esc_attr($ai_api_key); ?>" class="regular-text" />
                            <p class="description"><?php _e('API key for enhanced AI-powered content analysis (optional).', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Privacy Settings -->
            <div class="wpsi-settings-section">
                <h2><?php _e('Privacy & Compliance', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="privacy_compliant"><?php _e('GDPR Compliant', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="privacy_compliant" name="privacy_compliant" value="1" <?php checked($privacy_compliant); ?> />
                            <p class="description"><?php _e('Enable GDPR compliance features including consent management.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cookie_consent"><?php _e('Require Cookie Consent', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="cookie_consent" name="cookie_consent" value="1" <?php checked($cookie_consent); ?> />
                            <p class="description"><?php _e('Show cookie consent banner before tracking user interactions.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="data_retention_days"><?php _e('Data Retention (Days)', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="data_retention_days" name="data_retention_days" value="<?php echo esc_attr($data_retention_days); ?>" min="1" max="3650" />
                            <p class="description"><?php _e('How long to keep user interaction data (1-3650 days).', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="anonymize_ip"><?php _e('Anonymize IP Addresses', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="anonymize_ip" name="anonymize_ip" value="1" <?php checked($anonymize_ip); ?> />
                            <p class="description"><?php _e('Remove last octet from IPv4 addresses for privacy.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="respect_dnt"><?php _e('Respect Do Not Track', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="respect_dnt" name="respect_dnt" value="1" <?php checked($respect_dnt); ?> />
                            <p class="description"><?php _e('Stop tracking when users have Do Not Track enabled.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="privacy_policy_url"><?php _e('Privacy Policy URL', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="privacy_policy_url" name="privacy_policy_url" value="<?php echo esc_attr($privacy_policy_url); ?>" class="regular-text" />
                            <p class="description"><?php _e('URL to your privacy policy page (used in consent banner).', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="data_processing_basis"><?php _e('Data Processing Basis', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                        </th>
                        <td>
                            <select id="data_processing_basis" name="data_processing_basis">
                                <option value="legitimate_interest" <?php selected($data_processing_basis, 'legitimate_interest'); ?>><?php _e('Legitimate Interest', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                                <option value="consent" <?php selected($data_processing_basis, 'consent'); ?>><?php _e('Consent', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                            </select>
                            <p class="description"><?php _e('Legal basis for processing personal data under GDPR.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="wpsi_save_settings" class="button-primary" value="<?php _e('Save Settings', 'smart-insights-content-intelligence-ux-heatmap'); ?>" />
        </p>
    </form>
</div>

<style>
.wpsi-settings-container {
    max-width: 800px;
}

.wpsi-settings-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wpsi-settings-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
}

.form-table th {
    width: 200px;
    padding: 20px 10px 20px 0;
}

.form-table td {
    padding: 15px 10px;
}

.description {
    color: #666;
    font-style: italic;
    margin-top: 5px;
}

.submit {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

@media (max-width: 768px) {
    .form-table th {
        width: auto;
        display: block;
        padding-bottom: 5px;
    }
    
    .form-table td {
        display: block;
        padding-top: 5px;
    }
}
</style> 