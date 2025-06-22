<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('SEO Checker', 'wp-smart-insights'); ?></h1>
    <p class="description"><?php esc_html_e('Analyze your content for SEO optimization opportunities and get actionable recommendations.', 'wp-smart-insights'); ?></p>
    
    <div class="wpsi-seo-container">
        <!-- Post Selection -->
        <div class="wpsi-seo-post-selector">
            <h2><?php esc_html_e('Select Post to Analyze', 'wp-smart-insights'); ?></h2>
            <select id="wpsi-post-selector">
                <option value=""><?php esc_html_e('Choose a post...', 'wp-smart-insights'); ?></option>
                <?php
                $posts = get_posts(array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'numberposts' => 50,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ));
                
                foreach ($posts as $post) {
                    echo '<option value="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</option>';
                }
                ?>
            </select>
            <button id="wpsi-analyze-seo" class="button button-primary"><?php esc_html_e('Analyze SEO', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- SEO Analysis Results -->
        <div id="wpsi-seo-results" class="wpsi-seo-results" style="display: none;">
            <div class="wpsi-seo-header">
                <h2><?php esc_html_e('SEO Analysis Results', 'wp-smart-insights'); ?></h2>
                <div class="wpsi-seo-score">
                    <div class="wpsi-score-circle">
                        <span id="wpsi-overall-score">0</span>
                        <span class="wpsi-score-label"><?php esc_html_e('Score', 'wp-smart-insights'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- SEO Metrics -->
            <div class="wpsi-seo-metrics">
                <div class="wpsi-metric-card">
                    <h3><?php esc_html_e('Headings Structure', 'wp-smart-insights'); ?></h3>
                    <div class="wpsi-metric-score" id="wpsi-headings-score">-</div>
                    <div class="wpsi-metric-details" id="wpsi-headings-details"></div>
                </div>
                
                <div class="wpsi-metric-card">
                    <h3><?php esc_html_e('Meta Tags', 'wp-smart-insights'); ?></h3>
                    <div class="wpsi-metric-score" id="wpsi-meta-score">-</div>
                    <div class="wpsi-metric-details" id="wpsi-meta-details"></div>
                </div>
                
                <div class="wpsi-metric-card">
                    <h3><?php esc_html_e('Internal Links', 'wp-smart-insights'); ?></h3>
                    <div class="wpsi-metric-score" id="wpsi-links-score">-</div>
                    <div class="wpsi-metric-details" id="wpsi-links-details"></div>
                </div>
                
                <div class="wpsi-metric-card">
                    <h3><?php esc_html_e('Images & Alt Tags', 'wp-smart-insights'); ?></h3>
                    <div class="wpsi-metric-score" id="wpsi-images-score">-</div>
                    <div class="wpsi-metric-details" id="wpsi-images-details"></div>
                </div>
            </div>
            
            <!-- Quick Fixes -->
            <div class="wpsi-quick-fixes">
                <h3><?php esc_html_e('Quick Fixes & Recommendations', 'wp-smart-insights'); ?></h3>
                <div id="wpsi-fixes-list"></div>
            </div>
            
            <!-- Content Preview -->
            <div class="wpsi-content-preview">
                <h3><?php esc_html_e('Content Preview', 'wp-smart-insights'); ?></h3>
                <div id="wpsi-content-preview-content"></div>
            </div>
        </div>
        
        <!-- Loading State -->
        <div id="wpsi-seo-loading" class="wpsi-loading" style="display: none;">
            <div class="wpsi-spinner"></div>
            <p><?php esc_html_e('Analyzing SEO...', 'wp-smart-insights'); ?></p>
        </div>
    </div>
</div>

<style>
.wpsi-seo-container {
    max-width: 1200px;
    margin: 20px 0;
}

.wpsi-seo-post-selector {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.wpsi-seo-post-selector select {
    width: 300px;
    margin-right: 10px;
}

.wpsi-seo-results {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wpsi-seo-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.wpsi-score-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.wpsi-score-circle span:first-child {
    font-size: 24px;
    line-height: 1;
}

.wpsi-score-label {
    font-size: 12px;
    opacity: 0.8;
}

.wpsi-seo-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.wpsi-metric-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.wpsi-metric-card h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.wpsi-metric-score {
    font-size: 32px;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 10px;
}

.wpsi-metric-details {
    font-size: 14px;
    color: #666;
    line-height: 1.4;
}

.wpsi-quick-fixes {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.wpsi-quick-fixes h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.wpsi-fix-item {
    background: #fff;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 6px;
    border-left: 4px solid #28a745;
}

.wpsi-fix-item.warning {
    border-left-color: #ffc107;
}

.wpsi-fix-item.error {
    border-left-color: #dc3545;
}

.wpsi-content-preview {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.wpsi-content-preview h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.wpsi-loading {
    text-align: center;
    padding: 40px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wpsi-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#wpsi-analyze-seo').on('click', function() {
        var postId = $('#wpsi-post-selector').val();
        
        if (!postId) {
            alert('<?php esc_html_e('Please select a post to analyze.', 'wp-smart-insights'); ?>');
            return;
        }
        
        // Show loading
        $('#wpsi-seo-loading').show();
        $('#wpsi-seo-results').hide();
        
        // Analyze SEO
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_get_seo_score',
                post_id: postId,
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                $('#wpsi-seo-loading').hide();
                
                if (response.success) {
                    displaySEOResults(response.data);
                    $('#wpsi-seo-results').show();
                } else {
                    alert('<?php esc_html_e('Error analyzing SEO.', 'wp-smart-insights'); ?>');
                }
            },
            error: function() {
                $('#wpsi-seo-loading').hide();
                alert('<?php esc_html_e('Error analyzing SEO.', 'wp-smart-insights'); ?>');
            }
        });
    });
    
    function displaySEOResults(data) {
        // Overall score
        $('#wpsi-overall-score').text(data.overall_score);
        
        // Update metric scores
        $('#wpsi-headings-score').text(data.headings.score + '/100');
        $('#wpsi-meta-score').text(data.meta.score + '/100');
        $('#wpsi-links-score').text(data.links.score + '/100');
        $('#wpsi-images-score').text(data.images.score + '/100');
        
        // Update metric details
        $('#wpsi-headings-details').html(data.headings.details);
        $('#wpsi-meta-details').html(data.meta.details);
        $('#wpsi-links-details').html(data.links.details);
        $('#wpsi-images-details').html(data.images.details);
        
        // Display quick fixes
        var fixesHtml = '';
        data.fixes.forEach(function(fix) {
            fixesHtml += '<div class="wpsi-fix-item ' + fix.type + '">';
            fixesHtml += '<strong>' + fix.title + '</strong><br>';
            fixesHtml += fix.description;
            fixesHtml += '</div>';
        });
        $('#wpsi-fixes-list').html(fixesHtml);
        
        // Display content preview
        $('#wpsi-content-preview-content').html(data.content_preview);
    }
});
</script> 