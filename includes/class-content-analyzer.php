<?php
/**
 * Content Analyzer Class
 * 
 * Handles AI-powered content analysis including readability, sentiment, tone, and keyword density
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPSI_Content_Analyzer {
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_content_analysis_meta_box'));
        add_action('save_post', array($this, 'auto_analyze_content'));
    }
    
    public function add_content_analysis_meta_box() {
        add_meta_box(
            'wpsi-content-analysis',
            __('Smart Insights - Content Analysis', 'wp-smart-insights'),
            array($this, 'render_meta_box'),
            'post',
            'side',
            'high'
        );
        
        add_meta_box(
            'wpsi-content-analysis',
            __('Smart Insights - Content Analysis', 'wp-smart-insights'),
            array($this, 'render_meta_box'),
            'page',
            'side',
            'high'
        );
    }
    
    public function render_meta_box($post) {
        wp_nonce_field('wpsi_content_analysis', 'wpsi_content_analysis_nonce');
        
        $analysis = get_post_meta($post->ID, '_wpsi_content_analysis', true);
        
        echo '<div id="wpsi-content-analysis-container">';
        
        if ($analysis) {
            $this->display_analysis_results($analysis);
        } else {
            echo '<p>' . __('No analysis available. Click "Analyze Content" to get started.', 'wp-smart-insights') . '</p>';
        }
        
        echo '<button type="button" id="wpsi-analyze-content" class="button button-primary">';
        echo __('Analyze Content', 'wp-smart-insights');
        echo '</button>';
        
        echo '<div id="wpsi-analysis-loading" style="display: none;">';
        echo '<p>' . __('Analyzing content...', 'wp-smart-insights') . '</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    public function display_analysis_results($analysis) {
        echo '<div class="wpsi-analysis-results">';
        
        // Overall Score
        if (isset($analysis['overall_score'])) {
            echo '<div class="wpsi-score-section">';
            echo '<h4>' . __('Overall Content Score', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-score-circle" data-score="' . esc_attr($analysis['overall_score']) . '">';
            echo '<span class="wpsi-score-number">' . esc_html($analysis['overall_score']) . '</span>';
            echo '<span class="wpsi-score-label">/100</span>';
            echo '</div>';
            echo '</div>';
        }
        
        // Readability
        if (isset($analysis['readability'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . __('Readability', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-metric-bar">';
            echo '<div class="wpsi-metric-fill" style="width: ' . esc_attr($analysis['readability']['score']) . '%"></div>';
            echo '</div>';
            echo '<p>' . esc_html($analysis['readability']['grade']) . '</p>';
            if (!empty($analysis['readability']['suggestions'])) {
                echo '<ul class="wpsi-suggestions">';
                foreach ($analysis['readability']['suggestions'] as $suggestion) {
                    echo '<li>' . esc_html($suggestion) . '</li>';
                }
                echo '</ul>';
            }
            echo '</div>';
        }
        
        // Sentiment
        if (isset($analysis['sentiment'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . __('Sentiment', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-sentiment-indicator ' . esc_attr($analysis['sentiment']['type']) . '">';
            echo esc_html($analysis['sentiment']['label']);
            echo '</div>';
            echo '<p>' . esc_html($analysis['sentiment']['description']) . '</p>';
            echo '</div>';
        }
        
        // Tone
        if (isset($analysis['tone'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . __('Tone', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-tone-tags">';
            foreach ($analysis['tone']['tags'] as $tag) {
                echo '<span class="wpsi-tone-tag">' . esc_html($tag) . '</span>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        // Keyword Analysis
        if (isset($analysis['keywords'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . __('Keyword Analysis', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-keyword-density">';
            echo '<p>' . __('Density: ', 'wp-smart-insights') . esc_html($analysis['keywords']['density']) . '%</p>';
            if (!empty($analysis['keywords']['suggestions'])) {
                echo '<ul class="wpsi-suggestions">';
                foreach ($analysis['keywords']['suggestions'] as $suggestion) {
                    echo '<li>' . esc_html($suggestion) . '</li>';
                }
                echo '</ul>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        // Repetition & Fluff
        if (isset($analysis['repetition'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . __('Repetition & Fluff', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-repetition-score">';
            echo '<div class="wpsi-metric-bar">';
            echo '<div class="wpsi-metric-fill" style="width: ' . esc_attr($analysis['repetition']['score']) . '%"></div>';
            echo '</div>';
            echo '<p>' . esc_html($analysis['repetition']['description']) . '</p>';
            if (!empty($analysis['repetition']['issues'])) {
                echo '<ul class="wpsi-suggestions">';
                foreach ($analysis['repetition']['issues'] as $issue) {
                    echo '<li>' . esc_html($issue) . '</li>';
                }
                echo '</ul>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    public function analyze($content) {
        // Remove HTML tags for analysis
        $clean_content = wp_strip_all_tags($content);
        
        $analysis = array(
            'overall_score' => $this->calculate_overall_score($clean_content),
            'readability' => $this->analyze_readability($clean_content),
            'sentiment' => $this->analyze_sentiment($clean_content),
            'tone' => $this->analyze_tone($clean_content),
            'keywords' => $this->analyze_keywords($clean_content),
            'repetition' => $this->analyze_repetition($clean_content),
            'timestamp' => current_time('mysql'),
        );
        
        return $analysis;
    }
    
    private function calculate_overall_score($content) {
        $readability_score = $this->analyze_readability($content)['score'];
        $sentiment_score = $this->analyze_sentiment($content)['score'];
        $keyword_score = $this->analyze_keywords($content)['score'];
        $repetition_score = $this->analyze_repetition($content)['score'];
        
        // Weighted average
        $overall = ($readability_score * 0.3) + ($sentiment_score * 0.2) + ($keyword_score * 0.25) + ($repetition_score * 0.25);
        
        return round($overall);
    }
    
    private function analyze_readability($content) {
        $words = str_word_count($content);
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $syllables = $this->count_syllables($content);
        
        // Flesch Reading Ease
        $flesch_score = 206.835 - (1.015 * ($words / count($sentences))) - (84.6 * ($syllables / $words));
        $flesch_score = max(0, min(100, $flesch_score));
        
        // Grade level
        $grade = $this->get_grade_level($flesch_score);
        
        // Suggestions
        $suggestions = array();
        if ($flesch_score < 60) {
            $suggestions[] = __('Consider using shorter sentences and simpler words', 'wp-smart-insights');
        }
        if (count($sentences) < 5) {
            $suggestions[] = __('Add more sentences to improve flow', 'wp-smart-insights');
        }
        
        return array(
            'score' => $flesch_score,
            'grade' => $grade,
            'words' => $words,
            'sentences' => count($sentences),
            'syllables' => $syllables,
            'suggestions' => $suggestions
        );
    }
    
    private function count_syllables($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z]/', '', $text);
        $text = preg_replace('/[^aeiouy]+/', 'a', $text);
        $text = preg_replace('/a+/', 'a', $text);
        $text = trim($text, 'a');
        return strlen($text);
    }
    
    private function get_grade_level($flesch_score) {
        if ($flesch_score >= 90) return '5th grade';
        if ($flesch_score >= 80) return '6th grade';
        if ($flesch_score >= 70) return '7th grade';
        if ($flesch_score >= 60) return '8th-9th grade';
        if ($flesch_score >= 50) return '10th-12th grade';
        if ($flesch_score >= 30) return 'College';
        return 'College graduate';
    }
    
    private function analyze_sentiment($content) {
        // Simple sentiment analysis based on positive/negative word lists
        $positive_words = array('good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'awesome', 'perfect', 'love', 'like', 'enjoy', 'happy', 'success', 'win', 'best', 'top', 'quality', 'benefit', 'advantage', 'improve', 'enhance');
        $negative_words = array('bad', 'terrible', 'awful', 'horrible', 'worst', 'hate', 'dislike', 'fail', 'lose', 'problem', 'issue', 'difficult', 'hard', 'complex', 'confusing', 'frustrating', 'annoying', 'disappointing', 'poor', 'weak');
        
        $words = str_word_count(strtolower($content), 1);
        $positive_count = 0;
        $negative_count = 0;
        
        foreach ($words as $word) {
            if (in_array($word, $positive_words)) {
                $positive_count++;
            }
            if (in_array($word, $negative_words)) {
                $negative_count++;
            }
        }
        
        $total_words = count($words);
        $sentiment_ratio = ($positive_count - $negative_count) / max(1, $total_words);
        
        if ($sentiment_ratio > 0.02) {
            $type = 'positive';
            $label = __('Positive', 'wp-smart-insights');
            $description = __('Content has a positive tone', 'wp-smart-insights');
            $score = min(100, 50 + ($sentiment_ratio * 1000));
        } elseif ($sentiment_ratio < -0.02) {
            $type = 'negative';
            $label = __('Negative', 'wp-smart-insights');
            $description = __('Content has a negative tone', 'wp-smart-insights');
            $score = max(0, 50 + ($sentiment_ratio * 1000));
        } else {
            $type = 'neutral';
            $label = __('Neutral', 'wp-smart-insights');
            $description = __('Content has a neutral tone', 'wp-smart-insights');
            $score = 50;
        }
        
        return array(
            'type' => $type,
            'label' => $label,
            'description' => $description,
            'score' => round($score),
            'positive_words' => $positive_count,
            'negative_words' => $negative_count,
            'ratio' => $sentiment_ratio
        );
    }
    
    private function analyze_tone($content) {
        $tone_indicators = array(
            'professional' => array('industry', 'business', 'corporate', 'enterprise', 'professional', 'expert', 'specialist'),
            'casual' => array('hey', 'cool', 'awesome', 'great', 'fun', 'easy', 'simple', 'quick'),
            'formal' => array('therefore', 'furthermore', 'moreover', 'consequently', 'thus', 'hence', 'accordingly'),
            'friendly' => array('you', 'your', 'we', 'our', 'us', 'together', 'help', 'support'),
            'technical' => array('algorithm', 'protocol', 'framework', 'architecture', 'implementation', 'optimization'),
            'conversational' => array('imagine', 'think', 'suppose', 'let\'s', 'what if', 'consider'),
        );
        
        $words = str_word_count(strtolower($content), 1);
        $tone_scores = array();
        
        foreach ($tone_indicators as $tone => $indicators) {
            $score = 0;
            foreach ($indicators as $indicator) {
                $score += substr_count(strtolower($content), $indicator);
            }
            $tone_scores[$tone] = $score;
        }
        
        // Get top 3 tones
        arsort($tone_scores);
        $top_tones = array_slice(array_keys($tone_scores), 0, 3, true);
        
        return array(
            'tags' => $top_tones,
            'scores' => $tone_scores
        );
    }
    
    private function analyze_keywords($content) {
        $words = str_word_count(strtolower($content), 1);
        $word_count = count($words);
        
        // Remove common stop words
        $stop_words = array('the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them');
        $filtered_words = array_diff($words, $stop_words);
        
        // Count word frequency
        $word_freq = array_count_values($filtered_words);
        arsort($word_freq);
        
        // Calculate keyword density
        $top_keywords = array_slice($word_freq, 0, 5, true);
        $total_keyword_occurrences = array_sum($top_keywords);
        $density = ($total_keyword_occurrences / $word_count) * 100;
        
        // Suggestions
        $suggestions = array();
        if ($density < 1) {
            $suggestions[] = __('Consider adding more relevant keywords', 'wp-smart-insights');
        } elseif ($density > 5) {
            $suggestions[] = __('Keyword density might be too high - consider natural variation', 'wp-smart-insights');
        }
        
        $score = max(0, min(100, 100 - abs($density - 2.5) * 20));
        
        return array(
            'density' => round($density, 2),
            'top_keywords' => $top_keywords,
            'score' => round($score),
            'suggestions' => $suggestions
        );
    }
    
    private function analyze_repetition($content) {
        $words = str_word_count(strtolower($content), 1);
        $word_freq = array_count_values($words);
        
        // Find repeated words
        $repeated_words = array_filter($word_freq, function($count) {
            return $count > 3;
        });
        
        $total_words = count($words);
        $repeated_count = array_sum($repeated_words);
        $repetition_ratio = $repeated_count / $total_words;
        
        $score = max(0, 100 - ($repetition_ratio * 200));
        
        $issues = array();
        if (!empty($repeated_words)) {
            $issues[] = __('Some words are repeated frequently', 'wp-smart-insights');
        }
        
        if ($repetition_ratio > 0.1) {
            $issues[] = __('Consider using synonyms to reduce repetition', 'wp-smart-insights');
        }
        
        $description = $repetition_ratio < 0.05 ? __('Good variety in word choice', 'wp-smart-insights') : __('Some repetition detected', 'wp-smart-insights');
        
        return array(
            'score' => round($score),
            'description' => $description,
            'repetition_ratio' => round($repetition_ratio, 3),
            'repeated_words' => $repeated_words,
            'issues' => $issues
        );
    }
    
    public function auto_analyze_content($post_id) {
        // Don't analyze on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Only analyze posts and pages
        if (!in_array(get_post_type($post_id), array('post', 'page'))) {
            return;
        }
        
        $content = get_post_field('post_content', $post_id);
        if (!empty($content)) {
            $analysis = $this->analyze($content);
            update_post_meta($post_id, '_wpsi_content_analysis', $analysis);
        }
    }
} 