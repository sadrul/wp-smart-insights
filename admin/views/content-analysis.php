<?php
/**
 * Content Analysis View
 * 
 * Shows AI-powered content analysis and insights
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get posts with content analysis
$posts_with_analysis = get_posts(array(
    'post_type' => array('post', 'page'),
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => array(
        array('key' => '_wpsi_content_analysis', 'compare' => 'EXISTS')
    )
));

$selected_post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$selected_post = null;

if ($selected_post_id > 0) {
    $selected_post = get_post($selected_post_id);
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Smart Insights - Content Analysis', 'wp-smart-insights'); ?></h1>
    
    <div class="wpsi-content-analysis-container">
        <!-- Post Selection -->
        <div class="wpsi-section">
            <h2><?php esc_html_e('Select Content to Analyze', 'wp-smart-insights'); ?></h2>
            
            <form method="get" action="">
                <input type="hidden" name="page" value="smart-insights-content-intelligence-ux-heatmap-content" />
                <select name="post_id" id="wpsi-post-select">
                    <option value=""><?php esc_html_e('-- Select a page or post --', 'wp-smart-insights'); ?></option>
                    <?php foreach ($posts_with_analysis as $post): ?>
                        <?php
                        $analysis = get_post_meta($post->ID, '_wpsi_content_analysis', true);
                        $score = is_array($analysis) ? ($analysis['overall_score'] ?? 0) : 0;
                        ?>
                        <option value="<?php echo esc_attr($post->ID); ?>"><?php echo esc_html($post->post_title); ?> (<?php echo esc_html($score); ?>)</option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="<?php esc_html_e('Load Analysis', 'wp-smart-insights'); ?>" />
            </form>
        </div>
        
        <?php if ($selected_post): ?>
            <?php
            $analysis = get_post_meta($selected_post->ID, '_wpsi_content_analysis', true);
            $content_analyzer = new WPSI_Content_Analyzer();
            ?>
            
            <!-- Overall Score -->
            <div class="wpsi-section">
                <h2><?php esc_html_e('Content Quality Score', 'wp-smart-insights'); ?></h2>
                
                <div class="wpsi-score-display">
                    <div class="wpsi-score-circle" data-score="<?php echo esc_attr($analysis['overall_score'] ?? 0); ?>">
                        <div class="wpsi-score-number"><?php echo esc_html($analysis['overall_score'] ?? 0); ?></div>
                        <div class="wpsi-score-label">/100</div>
                    </div>
                    <div class="wpsi-score-details">
                        <h3><?php echo esc_html($selected_post->post_title); ?></h3>
                        <p><?php esc_html_e('Last analyzed:', 'wp-smart-insights'); ?> <?php echo esc_html($analysis['timestamp'] ?? ''); ?></p>
                        <button type="button" class="button button-primary" id="wpsi-reanalyze-content">
                            <?php esc_html_e('Re-analyze Content', 'wp-smart-insights'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Analysis -->
            <div class="wpsi-section">
                <h2><?php esc_html_e('Detailed Analysis', 'wp-smart-insights'); ?></h2>
                
                <div class="wpsi-analysis-grid">
                    <!-- Readability -->
                    <?php if (isset($analysis['readability'])): ?>
                        <div class="wpsi-analysis-card">
                            <div class="wpsi-analysis-header">
                                <h3><?php esc_html_e('Readability', 'wp-smart-insights'); ?></h3>
                                <div class="wpsi-score-badge"><?php echo esc_html($analysis['readability']['score']); ?>/100</div>
                            </div>
                            <div class="wpsi-analysis-content">
                                <div class="wpsi-metric-bar">
                                    <div class="wpsi-metric-fill" style="width: <?php echo esc_attr($analysis['readability']['score']); ?>%"></div>
                                </div>
                                <p><strong><?php esc_html_e('Grade Level:', 'wp-smart-insights'); ?></strong> <?php echo esc_html($analysis['readability']['grade']); ?></p>
                                <p><strong><?php esc_html_e('Words:', 'wp-smart-insights'); ?></strong> <?php echo esc_html($analysis['readability']['words']); ?></p>
                                <p><strong><?php esc_html_e('Sentences:', 'wp-smart-insights'); ?></strong> <?php echo esc_html($analysis['readability']['sentences']); ?></p>
                                
                                <?php if (!empty($analysis['readability']['suggestions'])): ?>
                                    <div class="wpsi-suggestions">
                                        <h4><?php esc_html_e('Suggestions:', 'wp-smart-insights'); ?></h4>
                                        <ul>
                                            <?php foreach ($analysis['readability']['suggestions'] as $suggestion): ?>
                                                <li><?php echo esc_html($suggestion); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Sentiment -->
                    <?php if (isset($analysis['sentiment'])): ?>
                        <div class="wpsi-analysis-card">
                            <div class="wpsi-analysis-header">
                                <h3><?php esc_html_e('Sentiment', 'wp-smart-insights'); ?></h3>
                                <div class="wpsi-score-badge"><?php echo esc_html($analysis['sentiment']['score']); ?>/100</div>
                            </div>
                            <div class="wpsi-analysis-content">
                                <div class="wpsi-sentiment-indicator <?php echo esc_attr($analysis['sentiment']['type']); ?>">
                                    <?php echo esc_html($analysis['sentiment']['label']); ?>
                                </div>
                                <p><?php echo esc_html($analysis['sentiment']['description']); ?></p>
                                <p><strong><?php esc_html_e('Positive Words:', 'wp-smart-insights'); ?></strong> <?php echo esc_html($analysis['sentiment']['positive_words']); ?></p>
                                <p><strong><?php esc_html_e('Negative Words:', 'wp-smart-insights'); ?></strong> <?php echo esc_html($analysis['sentiment']['negative_words']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Tone -->
                    <?php if (isset($analysis['tone'])): ?>
                        <div class="wpsi-analysis-card">
                            <div class="wpsi-analysis-header">
                                <h3><?php esc_html_e('Tone', 'wp-smart-insights'); ?></h3>
                            </div>
                            <div class="wpsi-analysis-content">
                                <div class="wpsi-tone-tags">
                                    <?php foreach ($analysis['tone']['tags'] as $tag): ?>
                                        <span class="wpsi-tone-tag"><?php echo esc_html($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Keywords -->
                    <?php if (isset($analysis['keywords'])): ?>
                        <div class="wpsi-analysis-card">
                            <div class="wpsi-analysis-header">
                                <h3><?php esc_html_e('Keyword Analysis', 'wp-smart-insights'); ?></h3>
                                <div class="wpsi-score-badge"><?php echo esc_html($analysis['keywords']['score']); ?>/100</div>
                            </div>
                            <div class="wpsi-analysis-content">
                                <p><strong><?php esc_html_e('Density:', 'wp-smart-insights'); ?></strong> <?php echo esc_html($analysis['keywords']['density']); ?>%</p>
                                
                                <div class="wpsi-keywords-list">
                                    <h4><?php esc_html_e('Top Keywords:', 'wp-smart-insights'); ?></h4>
                                    <?php foreach (array_slice($analysis['keywords']['top_keywords'], 0, 5) as $keyword => $count): ?>
                                        <div class="wpsi-keyword-item">
                                            <span class="wpsi-keyword-text"><?php echo esc_html($keyword); ?></span>
                                            <span class="wpsi-keyword-count"><?php echo esc_html($count); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (!empty($analysis['keywords']['suggestions'])): ?>
                                    <div class="wpsi-suggestions">
                                        <h4><?php esc_html_e('Suggestions:', 'wp-smart-insights'); ?></h4>
                                        <ul>
                                            <?php foreach ($analysis['keywords']['suggestions'] as $suggestion): ?>
                                                <li><?php echo esc_html($suggestion); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Repetition -->
                    <?php if (isset($analysis['repetition'])): ?>
                        <div class="wpsi-analysis-card">
                            <div class="wpsi-analysis-header">
                                <h3><?php esc_html_e('Repetition & Fluff', 'wp-smart-insights'); ?></h3>
                                <div class="wpsi-score-badge"><?php echo esc_html($analysis['repetition']['score']); ?>/100</div>
                            </div>
                            <div class="wpsi-analysis-content">
                                <div class="wpsi-metric-bar">
                                    <div class="wpsi-metric-fill" style="width: <?php echo esc_attr($analysis['repetition']['score']); ?>%"></div>
                                </div>
                                <p><?php echo esc_html($analysis['repetition']['description']); ?></p>
                                <p><strong><?php esc_html_e('Repetition Ratio:', 'wp-smart-insights'); ?></strong> <?php echo esc_html($analysis['repetition']['repetition_ratio']); ?></p>
                                
                                <?php if (!empty($analysis['repetition']['issues'])): ?>
                                    <div class="wpsi-suggestions">
                                        <h4><?php esc_html_e('Issues:', 'wp-smart-insights'); ?></h4>
                                        <ul>
                                            <?php foreach ($analysis['repetition']['issues'] as $issue): ?>
                                                <li><?php echo esc_html($issue); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <div class="wpsi-section">
                <div class="wpsi-no-data">
                    <div class="wpsi-no-data-icon dashicons dashicons-edit"></div>
                    <h3><?php esc_html_e('No Content Analysis Available', 'wp-smart-insights'); ?></h3>
                    <p><?php esc_html_e('Select a page or post from the dropdown above to view content analysis. You can also analyze content directly from the post editor.', 'wp-smart-insights'); ?></p>
                    <a href="<?php echo esc_url(admin_url('edit.php')); ?>" class="button button-primary">
                        <?php esc_html_e('Go to Posts', 'wp-smart-insights'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.wpsi-content-analysis-container {
    max-width: 1200px;
}

.wpsi-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wpsi-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
}

.wpsi-score-display {
    display: flex;
    align-items: center;
    gap: 30px;
}

.wpsi-score-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#0073aa 0deg, #0073aa calc(var(--score) * 3.6deg), #e1e1e1 calc(var(--score) * 3.6deg), #e1e1e1 360deg);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #333;
    font-weight: bold;
}

.wpsi-score-circle[data-score] {
    --score: attr(data-score);
}

.wpsi-score-number {
    font-size: 2em;
    line-height: 1;
}

.wpsi-score-label {
    font-size: 0.9em;
    opacity: 0.7;
}

.wpsi-score-details h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.wpsi-score-details p {
    margin: 0 0 15px 0;
    color: #666;
}

.wpsi-analysis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.wpsi-analysis-card {
    border: 1px solid #e1e1e1;
    border-radius: 6px;
    overflow: hidden;
}

.wpsi-analysis-header {
    background: #f9f9f9;
    padding: 15px;
    border-bottom: 1px solid #e1e1e1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wpsi-analysis-header h3 {
    margin: 0;
    color: #333;
}

.wpsi-score-badge {
    background: #0073aa;
    color: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9em;
    font-weight: bold;
}

.wpsi-analysis-content {
    padding: 15px;
}

.wpsi-metric-bar {
    width: 100%;
    height: 8px;
    background: #e1e1e1;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 15px;
}

.wpsi-metric-fill {
    height: 100%;
    background: #0073aa;
    transition: width 0.3s ease;
}

.wpsi-sentiment-indicator {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    margin-bottom: 15px;
}

.wpsi-sentiment-indicator.positive {
    background: #d4edda;
    color: #155724;
}

.wpsi-sentiment-indicator.negative {
    background: #f8d7da;
    color: #721c24;
}

.wpsi-sentiment-indicator.neutral {
    background: #e2e3e5;
    color: #383d41;
}

.wpsi-tone-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.wpsi-tone-tag {
    background: #0073aa;
    color: #fff;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.9em;
}

.wpsi-keywords-list {
    margin: 15px 0;
}

.wpsi-keywords-list h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.wpsi-keyword-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f1f1f1;
}

.wpsi-keyword-item:last-child {
    border-bottom: none;
}

.wpsi-keyword-count {
    background: #f1f1f1;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.8em;
    font-weight: bold;
}

.wpsi-suggestions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f1f1f1;
}

.wpsi-suggestions h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.wpsi-suggestions ul {
    margin: 0;
    padding-left: 20px;
}

.wpsi-suggestions li {
    margin-bottom: 5px;
    color: #666;
}

.wpsi-no-data {
    text-align: center;
    padding: 60px 20px;
}

.wpsi-no-data-icon {
    font-size: 4em;
    color: #ddd;
    margin-bottom: 20px;
}

.wpsi-no-data h3 {
    margin: 0 0 15px 0;
    color: #666;
}

.wpsi-no-data p {
    margin: 0 0 25px 0;
    color: #999;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

@media (max-width: 768px) {
    .wpsi-score-display {
        flex-direction: column;
        text-align: center;
    }
    
    .wpsi-analysis-grid {
        grid-template-columns: 1fr;
    }
    
    .wpsi-analysis-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Re-analyze content
    $('#wpsi-reanalyze-content').on('click', function() {
        var button = $(this);
        var originalText = button.text();
        
        button.prop('disabled', true).text('<?php esc_html_e('Analyzing...', 'wp-smart-insights'); ?>');
        
        $.post(ajaxurl, {
            action: 'wpsi_analyze_content',
            nonce: '<?php echo esc_js(wp_create_nonce('wpsi_analyze_content')); ?>',
            post_id: <?php echo esc_js($selected_post_id ?: 0); ?>,
            content: '<?php echo esc_js($selected_post ? $selected_post->post_content : ''); ?>'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('<?php esc_html_e('Analysis failed. Please try again.', 'wp-smart-insights'); ?>');
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Update score circles
    $('.wpsi-score-circle').each(function() {
        var score = $(this).data('score');
        $(this).css('--score', score);
    });
});
</script> 