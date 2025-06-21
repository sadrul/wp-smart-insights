<?php
/**
 * Dashboard View
 * 
 * Main dashboard showing overview of Smart Insights features
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get recent posts with analysis
$recent_posts = get_posts(array(
    'post_type' => array('post', 'page'),
    'posts_per_page' => 5,
    'post_status' => 'publish',
    'meta_query' => array(
        'relation' => 'OR',
        array('key' => '_wpsi_content_analysis', 'compare' => 'EXISTS'),
        array('key' => '_wpsi_seo_analysis', 'compare' => 'EXISTS'),
        array('key' => '_wpsi_heatmap_data', 'compare' => 'EXISTS')
    )
));

// Get overall stats
$total_posts = wp_count_posts('post')->publish;
$total_pages = wp_count_posts('page')->publish;
$analyzed_posts = get_posts(array(
    'post_type' => array('post', 'page'),
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => array(
        array('key' => '_wpsi_content_analysis', 'compare' => 'EXISTS')
    )
));
$analyzed_count = count($analyzed_posts);

// Get tracking status
$tracking_enabled = get_option('wpsi_tracking_enabled', false);
$privacy_compliant = get_option('wpsi_privacy_compliant', true);
?>

<div class="wrap">
    <h1><?php _e('Smart Insights Dashboard', 'smart-insights-content-intelligence-ux-heatmap'); ?></h1>
    
    <div class="wpsi-dashboard-overview">
        <div class="wpsi-stats-grid">
            <div class="wpsi-stat-card">
                <div class="wpsi-stat-icon dashicons dashicons-admin-post"></div>
                <div class="wpsi-stat-content">
                    <h3><?php echo $total_posts + $total_pages; ?></h3>
                    <p><?php _e('Total Posts & Pages', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                </div>
            </div>
            
            <div class="wpsi-stat-card">
                <div class="wpsi-stat-icon dashicons dashicons-chart-area"></div>
                <div class="wpsi-stat-content">
                    <h3><?php echo $analyzed_count; ?></h3>
                    <p><?php _e('Analyzed Content', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                </div>
            </div>
            
            <div class="wpsi-stat-card">
                <div class="wpsi-stat-icon dashicons dashicons-visibility"></div>
                <div class="wpsi-stat-content">
                    <h3><?php echo $tracking_enabled ? __('Active', 'smart-insights-content-intelligence-ux-heatmap') : __('Inactive', 'smart-insights-content-intelligence-ux-heatmap'); ?></h3>
                    <p><?php _e('Tracking Status', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                </div>
            </div>
            
            <div class="wpsi-stat-card">
                <div class="wpsi-stat-icon dashicons dashicons-shield"></div>
                <div class="wpsi-stat-content">
                    <h3><?php echo $privacy_compliant ? __('Enabled', 'smart-insights-content-intelligence-ux-heatmap') : __('Disabled', 'smart-insights-content-intelligence-ux-heatmap'); ?></h3>
                    <p><?php _e('Privacy Compliance', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="wpsi-dashboard-sections">
        <!-- Quick Actions -->
        <div class="wpsi-section">
            <h2><?php _e('Quick Actions', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            <div class="wpsi-quick-actions">
                <a href="<?php echo admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap-content'); ?>" class="button button-primary">
                    <span class="dashicons dashicons-edit"></span>
                    <?php _e('Analyze Content', 'smart-insights-content-intelligence-ux-heatmap'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap-heatmaps'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-chart-area"></span>
                    <?php _e('View Heatmaps', 'smart-insights-content-intelligence-ux-heatmap'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap-seo'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-search"></span>
                    <?php _e('SEO Checker', 'smart-insights-content-intelligence-ux-heatmap'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=smart-insights-content-intelligence-ux-heatmap-settings'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Settings', 'smart-insights-content-intelligence-ux-heatmap'); ?>
                </a>
            </div>
        </div>
        
        <!-- Recent Analysis -->
        <div class="wpsi-section">
            <h2><?php _e('Recent Analysis', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            <?php if (!empty($recent_posts)): ?>
                <div class="wpsi-recent-analysis">
                    <?php foreach ($recent_posts as $post): ?>
                        <?php
                        $content_analysis = get_post_meta($post->ID, '_wpsi_content_analysis', true);
                        $seo_analysis = get_post_meta($post->ID, '_wpsi_seo_analysis', true);
                        $heatmap_stats = get_post_meta($post->ID, '_wpsi_heatmap_data', true);
                        ?>
                        <div class="wpsi-analysis-item">
                            <div class="wpsi-analysis-header">
                                <h4><a href="<?php echo get_edit_post_link($post->ID); ?>"><?php echo esc_html($post->post_title); ?></a></h4>
                                <span class="wpsi-post-type"><?php echo get_post_type_object($post->post_type)->labels->singular_name; ?></span>
                            </div>
                            
                            <div class="wpsi-analysis-metrics">
                                <?php if ($content_analysis): ?>
                                    <div class="wpsi-metric">
                                        <span class="wpsi-metric-label"><?php _e('Content Score', 'smart-insights-content-intelligence-ux-heatmap'); ?></span>
                                        <span class="wpsi-metric-value"><?php echo esc_html($content_analysis['overall_score'] ?? 0); ?>/100</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($seo_analysis): ?>
                                    <div class="wpsi-metric">
                                        <span class="wpsi-metric-label"><?php _e('SEO Score', 'smart-insights-content-intelligence-ux-heatmap'); ?></span>
                                        <span class="wpsi-metric-value"><?php echo esc_html($seo_analysis['overall_score'] ?? 0); ?>/100</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($heatmap_stats): ?>
                                    <div class="wpsi-metric">
                                        <span class="wpsi-metric-label"><?php _e('Sessions', 'smart-insights-content-intelligence-ux-heatmap'); ?></span>
                                        <span class="wpsi-metric-value"><?php echo count($heatmap_stats); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="wpsi-analysis-actions">
                                <a href="<?php echo get_edit_post_link($post->ID); ?>" class="button button-small">
                                    <?php _e('Edit', 'smart-insights-content-intelligence-ux-heatmap'); ?>
                                </a>
                                <a href="<?php echo get_permalink($post->ID); ?>" class="button button-small" target="_blank">
                                    <?php _e('View', 'smart-insights-content-intelligence-ux-heatmap'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p><?php _e('No analyzed content found. Start by analyzing your posts and pages.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- System Status -->
        <div class="wpsi-section">
            <h2><?php _e('System Status', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            <div class="wpsi-status-grid">
                <div class="wpsi-status-item">
                    <span class="wpsi-status-icon dashicons <?php echo $tracking_enabled ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                    <div class="wpsi-status-content">
                        <h4><?php _e('Tracking', 'smart-insights-content-intelligence-ux-heatmap'); ?></h4>
                        <p><?php echo $tracking_enabled ? __('Enabled', 'smart-insights-content-intelligence-ux-heatmap') : __('Disabled', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                    </div>
                </div>
                
                <div class="wpsi-status-item">
                    <span class="wpsi-status-icon dashicons <?php echo $privacy_compliant ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                    <div class="wpsi-status-content">
                        <h4><?php _e('Privacy Compliance', 'smart-insights-content-intelligence-ux-heatmap'); ?></h4>
                        <p><?php echo $privacy_compliant ? __('Enabled', 'smart-insights-content-intelligence-ux-heatmap') : __('Disabled', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                    </div>
                </div>
                
                <div class="wpsi-status-item">
                    <span class="wpsi-status-icon dashicons dashicons-yes-alt"></span>
                    <div class="wpsi-status-content">
                        <h4><?php _e('Database Tables', 'smart-insights-content-intelligence-ux-heatmap'); ?></h4>
                        <p><?php _e('Ready', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                    </div>
                </div>
                
                <div class="wpsi-status-item">
                    <span class="wpsi-status-icon dashicons dashicons-yes-alt"></span>
                    <div class="wpsi-status-content">
                        <h4><?php _e('File Permissions', 'smart-insights-content-intelligence-ux-heatmap'); ?></h4>
                        <p><?php _e('OK', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Getting Started -->
        <div class="wpsi-section">
            <h2><?php _e('Getting Started', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            <div class="wpsi-getting-started">
                <div class="wpsi-step">
                    <div class="wpsi-step-number">1</div>
                    <div class="wpsi-step-content">
                        <h4><?php _e('Enable Tracking', 'smart-insights-content-intelligence-ux-heatmap'); ?></h4>
                        <p><?php _e('Go to Settings and enable user tracking to start collecting heatmap and journey data.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                    </div>
                </div>
                
                <div class="wpsi-step">
                    <div class="wpsi-step-number">2</div>
                    <div class="wpsi-step-content">
                        <h4><?php _e('Analyze Content', 'smart-insights-content-intelligence-ux-heatmap'); ?></h4>
                        <p><?php _e('Use the Content Analysis tool to get AI-powered insights about your posts and pages.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                    </div>
                </div>
                
                <div class="wpsi-step">
                    <div class="wpsi-step-number">3</div>
                    <div class="wpsi-step-content">
                        <h4><?php _e('Check SEO', 'smart-insights-content-intelligence-ux-heatmap'); ?></h4>
                        <p><?php _e('Run SEO analysis to identify and fix issues with headings, meta tags, and internal links.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                    </div>
                </div>
                
                <div class="wpsi-step">
                    <div class="wpsi-step-number">4</div>
                    <div class="wpsi-step-content">
                        <h4><?php _e('Review Insights', 'smart-insights-content-intelligence-ux-heatmap'); ?></h4>
                        <p><?php _e('Monitor heatmaps and user journeys to optimize your content and user experience.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wpsi-dashboard-overview {
    margin: 20px 0;
}

.wpsi-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.wpsi-stat-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wpsi-stat-icon {
    font-size: 2.5em;
    color: #0073aa;
}

.wpsi-stat-content h3 {
    margin: 0;
    font-size: 1.8em;
    color: #333;
}

.wpsi-stat-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.9em;
}

.wpsi-dashboard-sections {
    display: grid;
    gap: 30px;
}

.wpsi-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wpsi-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
}

.wpsi-quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.wpsi-quick-actions .button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    font-size: 14px;
}

.wpsi-recent-analysis {
    display: grid;
    gap: 15px;
}

.wpsi-analysis-item {
    border: 1px solid #e1e1e1;
    border-radius: 6px;
    padding: 15px;
    background: #f9f9f9;
}

.wpsi-analysis-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.wpsi-analysis-header h4 {
    margin: 0;
}

.wpsi-post-type {
    background: #0073aa;
    color: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
}

.wpsi-analysis-metrics {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.wpsi-metric {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.wpsi-metric-label {
    font-size: 0.8em;
    color: #666;
    margin-bottom: 5px;
}

.wpsi-metric-value {
    font-weight: bold;
    color: #333;
}

.wpsi-analysis-actions {
    display: flex;
    gap: 10px;
}

.wpsi-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.wpsi-status-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    border: 1px solid #e1e1e1;
    border-radius: 6px;
    background: #f9f9f9;
}

.wpsi-status-icon {
    font-size: 1.5em;
    color: #46b450;
}

.wpsi-status-icon.dashicons-no-alt {
    color: #dc3232;
}

.wpsi-status-content h4 {
    margin: 0 0 5px 0;
    font-size: 1em;
}

.wpsi-status-content p {
    margin: 0;
    font-size: 0.9em;
    color: #666;
}

.wpsi-getting-started {
    display: grid;
    gap: 20px;
}

.wpsi-step {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.wpsi-step-number {
    background: #0073aa;
    color: #fff;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.wpsi-step-content h4 {
    margin: 0 0 8px 0;
    color: #333;
}

.wpsi-step-content p {
    margin: 0;
    color: #666;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .wpsi-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .wpsi-quick-actions {
        flex-direction: column;
    }
    
    .wpsi-analysis-metrics {
        flex-direction: column;
        gap: 10px;
    }
    
    .wpsi-status-grid {
        grid-template-columns: 1fr;
    }
}
</style> 