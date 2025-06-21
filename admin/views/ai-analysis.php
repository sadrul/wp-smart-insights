<?php
/**
 * AI Analysis Admin View
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
    <h1><?php _e('AI Analysis', 'smart-insights-content-intelligence-ux-heatmap'); ?></h1>
    
    <div class="wpsi-admin-container">
        <!-- AI Configuration Section -->
        <div class="wpsi-card">
            <h2><?php _e('AI Configuration', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            <div class="wpsi-form-group">
                <label for="wpsi_ai_provider"><?php _e('AI Provider:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <select id="wpsi_ai_provider" name="wpsi_ai_provider">
                    <option value="openai" <?php selected(get_option('wpsi_ai_provider', 'openai'), 'openai'); ?>><?php _e('OpenAI GPT', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="google" <?php selected(get_option('wpsi_ai_provider', 'openai'), 'google'); ?>><?php _e('Google AI', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_ai_api_key"><?php _e('API Key:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="password" id="wpsi_ai_api_key" name="wpsi_ai_api_key" value="<?php echo esc_attr(get_option('wpsi_ai_api_key', '')); ?>" class="regular-text">
                <p class="description"><?php _e('Enter your AI provider API key. Keep it secure.', 'smart-insights-content-intelligence-ux-heatmap'); ?></p>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_ai_model"><?php _e('Model:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <select id="wpsi_ai_model" name="wpsi_ai_model">
                    <option value="gpt-4" <?php selected(get_option('wpsi_ai_model', 'gpt-4'), 'gpt-4'); ?>>GPT-4</option>
                    <option value="gpt-3.5-turbo" <?php selected(get_option('wpsi_ai_model', 'gpt-4'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                    <option value="gemini-pro" <?php selected(get_option('wpsi_ai_model', 'gpt-4'), 'gemini-pro'); ?>>Gemini Pro</option>
                </select>
            </div>
            
            <button type="button" id="wpsi_save_ai_config" class="button button-primary"><?php _e('Save Configuration', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
        </div>
        
        <!-- Content Analysis Section -->
        <div class="wpsi-card">
            <h2><?php _e('Content Analysis', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_content_to_analyze"><?php _e('Content to Analyze:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <textarea id="wpsi_content_to_analyze" rows="10" class="large-text" placeholder="<?php _e('Paste your content here for AI analysis...', 'smart-insights-content-intelligence-ux-heatmap'); ?>"></textarea>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_analysis_type"><?php _e('Analysis Type:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <select id="wpsi_analysis_type" name="wpsi_analysis_type">
                    <option value="readability"><?php _e('Readability Analysis', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="seo"><?php _e('SEO Optimization', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="engagement"><?php _e('Engagement Analysis', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="tone"><?php _e('Tone & Sentiment', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="comprehensive"><?php _e('Comprehensive Analysis', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_analyze_content_ai" class="button button-primary"><?php _e('Analyze with AI', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
            
            <div id="wpsi_ai_analysis_results" class="wpsi-analysis-results" style="display: none;">
                <h3><?php _e('Analysis Results', 'smart-insights-content-intelligence-ux-heatmap'); ?></h3>
                <div id="wpsi_ai_results_content"></div>
            </div>
        </div>
        
        <!-- Batch Analysis Section -->
        <div class="wpsi-card">
            <h2><?php _e('Batch Content Analysis', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_batch_post_type"><?php _e('Post Type:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <select id="wpsi_batch_post_type" name="wpsi_batch_post_type">
                    <option value="post"><?php _e('Posts', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="page"><?php _e('Pages', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="all"><?php _e('All Content', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_batch_limit"><?php _e('Number of Posts:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <input type="number" id="wpsi_batch_limit" name="wpsi_batch_limit" value="10" min="1" max="50">
            </div>
            
            <button type="button" id="wpsi_batch_analyze" class="button button-secondary"><?php _e('Start Batch Analysis', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
            
            <div id="wpsi_batch_progress" class="wpsi-progress" style="display: none;">
                <div class="wpsi-progress-bar">
                    <div class="wpsi-progress-fill"></div>
                </div>
                <div class="wpsi-progress-text">0%</div>
            </div>
            
            <div id="wpsi_batch_results" class="wpsi-batch-results" style="display: none;">
                <h3><?php _e('Batch Analysis Results', 'smart-insights-content-intelligence-ux-heatmap'); ?></h3>
                <div id="wpsi_batch_results_content"></div>
            </div>
        </div>
        
        <!-- AI Recommendations Section -->
        <div class="wpsi-card">
            <h2><?php _e('AI Recommendations', 'smart-insights-content-intelligence-ux-heatmap'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_recommendation_focus"><?php _e('Focus Area:', 'smart-insights-content-intelligence-ux-heatmap'); ?></label>
                <select id="wpsi_recommendation_focus" name="wpsi_recommendation_focus">
                    <option value="content_quality"><?php _e('Content Quality', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="user_engagement"><?php _e('User Engagement', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="seo_performance"><?php _e('SEO Performance', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                    <option value="conversion_optimization"><?php _e('Conversion Optimization', 'smart-insights-content-intelligence-ux-heatmap'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_get_recommendations" class="button button-secondary"><?php _e('Get AI Recommendations', 'smart-insights-content-intelligence-ux-heatmap'); ?></button>
            
            <div id="wpsi_recommendations_results" class="wpsi-recommendations-results" style="display: none;">
                <h3><?php _e('AI Recommendations', 'smart-insights-content-intelligence-ux-heatmap'); ?></h3>
                <div id="wpsi_recommendations_content"></div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Save AI Configuration
    $('#wpsi_save_ai_config').on('click', function() {
        var data = {
            action: 'wpsi_save_ai_config',
            nonce: wpsi_ajax.nonce,
            provider: $('#wpsi_ai_provider').val(),
            api_key: $('#wpsi_ai_api_key').val(),
            model: $('#wpsi_ai_model').val()
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            if (response.success) {
                alert('Configuration saved successfully!');
            } else {
                alert('Error saving configuration: ' + response.data);
            }
        });
    });
    
    // Analyze Content with AI
    $('#wpsi_analyze_content_ai').on('click', function() {
        var content = $('#wpsi_content_to_analyze').val();
        var analysis_type = $('#wpsi_analysis_type').val();
        
        if (!content.trim()) {
            alert('Please enter content to analyze.');
            return;
        }
        
        $(this).prop('disabled', true).text('Analyzing...');
        
        var data = {
            action: 'wpsi_analyze_content_ai',
            nonce: wpsi_ajax.nonce,
            content: content,
            analysis_type: analysis_type
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_analyze_content_ai').prop('disabled', false).text('Analyze with AI');
            
            if (response.success) {
                $('#wpsi_ai_results_content').html(response.data.html);
                $('#wpsi_ai_analysis_results').show();
            } else {
                alert('Error analyzing content: ' + response.data);
            }
        });
    });
    
    // Batch Analysis
    $('#wpsi_batch_analyze').on('click', function() {
        var post_type = $('#wpsi_batch_post_type').val();
        var limit = $('#wpsi_batch_limit').val();
        
        $(this).prop('disabled', true).text('Starting...');
        $('#wpsi_batch_progress').show();
        
        var data = {
            action: 'wpsi_batch_analyze',
            nonce: wpsi_ajax.nonce,
            post_type: post_type,
            limit: limit
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_batch_analyze').prop('disabled', false).text('Start Batch Analysis');
            
            if (response.success) {
                $('#wpsi_batch_results_content').html(response.data.html);
                $('#wpsi_batch_results').show();
            } else {
                alert('Error starting batch analysis: ' + response.data);
            }
        });
    });
    
    // Get AI Recommendations
    $('#wpsi_get_recommendations').on('click', function() {
        var focus = $('#wpsi_recommendation_focus').val();
        
        $(this).prop('disabled', true).text('Getting recommendations...');
        
        var data = {
            action: 'wpsi_get_recommendations',
            nonce: wpsi_ajax.nonce,
            focus: focus
        };
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_get_recommendations').prop('disabled', false).text('Get AI Recommendations');
            
            if (response.success) {
                $('#wpsi_recommendations_content').html(response.data.html);
                $('#wpsi_recommendations_results').show();
            } else {
                alert('Error getting recommendations: ' + response.data);
            }
        });
    });
});
</script> 