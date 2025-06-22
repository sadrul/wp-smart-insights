<?php
/**
 * AI Service Class
 * Handles integration with OpenAI and Google AI services
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPSI_AI_Service {
    
    private $openai_api_key;
    private $google_api_key;
    private $ai_provider;
    
    public function __construct() {
        $this->openai_api_key = get_option('wpsi_openai_api_key', '');
        $this->google_api_key = get_option('wpsi_google_api_key', '');
        $this->ai_provider = get_option('wpsi_ai_provider', 'openai');
    }
    
    /**
     * Analyze content using AI
     */
    public function analyze_content($content, $analysis_type = 'comprehensive') {
        if ($this->ai_provider === 'openai' && !empty($this->openai_api_key)) {
            return $this->analyze_with_openai($content, $analysis_type);
        } elseif ($this->ai_provider === 'google' && !empty($this->google_api_key)) {
            return $this->analyze_with_google($content, $analysis_type);
        } else {
            return $this->fallback_analysis($content);
        }
    }
    
    /**
     * OpenAI Analysis
     */
    private function analyze_with_openai($content, $analysis_type) {
        $prompt = $this->build_openai_prompt($content, $analysis_type);
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->openai_api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are an expert content analyst and SEO specialist. Provide detailed, actionable analysis in JSON format.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => 2000,
                'temperature' => 0.3
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $this->fallback_analysis($content);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            $ai_response = json_decode($data['choices'][0]['message']['content'], true);
            if ($ai_response) {
                return $this->merge_ai_analysis($ai_response, $this->fallback_analysis($content));
            }
        }
        
        return $this->fallback_analysis($content);
    }
    
    /**
     * Google AI Analysis
     */
    private function analyze_with_google($content, $analysis_type) {
        // Google Natural Language API for sentiment and entities
        $sentiment_response = wp_remote_post('https://language.googleapis.com/v1/documents:analyzeSentiment?key=' . $this->google_api_key, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'document' => array(
                    'type' => 'PLAIN_TEXT',
                    'content' => wp_strip_all_tags($content)
                ),
                'encodingType' => 'UTF8'
            )),
            'timeout' => 30
        ));
        
        $sentiment_data = array();
        if (!is_wp_error($sentiment_response)) {
            $body = wp_remote_retrieve_body($sentiment_response);
            $sentiment_data = json_decode($body, true);
        }
        
        // Google AI for content analysis
        $ai_response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->google_api_key, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array(
                                'text' => $this->build_google_prompt($content, $analysis_type)
                            )
                        )
                    )
                )
            )),
            'timeout' => 30
        ));
        
        $ai_data = array();
        if (!is_wp_error($ai_response)) {
            $body = wp_remote_retrieve_body($ai_response);
            $ai_data = json_decode($body, true);
        }
        
        return $this->merge_google_analysis($sentiment_data, $ai_data, $this->fallback_analysis($content));
    }
    
    /**
     * Build OpenAI prompt
     */
    private function build_openai_prompt($content, $analysis_type) {
        $prompt = "Analyze the following content and provide a detailed analysis in JSON format:\n\n";
        $prompt .= "Content: " . substr(wp_strip_all_tags($content), 0, 3000) . "\n\n";
        
        $prompt .= "Please provide analysis in this exact JSON format:\n";
        $prompt .= '{
            "readability_score": 85,
            "readability_level": "High School",
            "sentiment_score": 0.7,
            "sentiment_label": "Positive",
            "tone": "Professional",
            "keyword_density": {
                "primary_keyword": "wordpress",
                "density": 2.1,
                "recommendation": "Good density"
            },
            "content_structure": {
                "paragraphs": 15,
                "sentences": 45,
                "words": 1200,
                "recommendation": "Good structure"
            },
            "seo_analysis": {
                "title_optimization": "Good",
                "meta_description": "Missing",
                "heading_structure": "Needs improvement",
                "internal_links": 3,
                "recommendations": ["Add meta description", "Improve heading structure"]
            },
            "engagement_potential": {
                "score": 78,
                "factors": ["Good readability", "Positive sentiment", "Clear structure"]
            },
            "improvement_suggestions": [
                "Add a meta description for better SEO",
                "Include more internal links",
                "Consider adding more subheadings"
            ]
        }';
        
        return $prompt;
    }
    
    /**
     * Build Google AI prompt
     */
    private function build_google_prompt($content, $analysis_type) {
        return "Analyze this content for readability, SEO, and engagement potential: " . substr(wp_strip_all_tags($content), 0, 3000);
    }
    
    /**
     * Merge AI analysis with fallback
     */
    private function merge_ai_analysis($ai_data, $fallback_data) {
        return array_merge($fallback_data, $ai_data);
    }
    
    /**
     * Merge Google analysis
     */
    private function merge_google_analysis($sentiment_data, $ai_data, $fallback_data) {
        $merged = $fallback_data;
        
        if (isset($sentiment_data['documentSentiment'])) {
            $merged['sentiment_score'] = $sentiment_data['documentSentiment']['score'];
            $merged['sentiment_magnitude'] = $sentiment_data['documentSentiment']['magnitude'];
        }
        
        return $merged;
    }
    
    /**
     * Fallback analysis when AI is not available
     */
    private function fallback_analysis($content) {
        $content_analyzer = new WPSI_Content_Analyzer();
        return $content_analyzer->analyze($content);
    }
    
    /**
     * Test AI connection
     */
    public function test_connection($provider = null) {
        if ($provider) {
            $this->ai_provider = $provider;
        }
        
        $test_content = "This is a test content for AI analysis. It contains some basic text to verify the connection is working properly.";
        
        try {
            $result = $this->analyze_content($test_content, 'basic');
            return array(
                'success' => true,
                'message' => 'AI connection successful',
                'provider' => $this->ai_provider,
                'sample_result' => $result
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'AI connection failed: ' . $e->getMessage(),
                'provider' => $this->ai_provider
            );
        }
    }
    
    /**
     * Get AI usage statistics
     */
    public function get_usage_stats() {
        $stats = get_option('wpsi_ai_usage_stats', array());
        
        return array(
            'total_analyses' => isset($stats['total_analyses']) ? $stats['total_analyses'] : 0,
            'openai_analyses' => isset($stats['openai_analyses']) ? $stats['openai_analyses'] : 0,
            'google_analyses' => isset($stats['google_analyses']) ? $stats['google_analyses'] : 0,
            'fallback_analyses' => isset($stats['fallback_analyses']) ? $stats['fallback_analyses'] : 0,
            'last_analysis' => isset($stats['last_analysis']) ? $stats['last_analysis'] : null,
            'monthly_usage' => isset($stats['monthly_usage']) ? $stats['monthly_usage'] : array()
        );
    }
    
    /**
     * Update usage statistics
     */
    private function update_usage_stats($provider) {
        $stats = get_option('wpsi_ai_usage_stats', array());
        
        $stats['total_analyses'] = isset($stats['total_analyses']) ? $stats['total_analyses'] + 1 : 1;
        $stats[$provider . '_analyses'] = isset($stats[$provider . '_analyses']) ? $stats[$provider . '_analyses'] + 1 : 1;
        $stats['last_analysis'] = current_time('mysql');
        
        $current_month = gmdate('Y-m');
        if (!isset($stats['monthly_usage'][$current_month])) {
            $stats['monthly_usage'][$current_month] = 0;
        }
        $stats['monthly_usage'][$current_month]++;
        
        update_option('wpsi_ai_usage_stats', $stats);
    }
    
    /**
     * Get AI recommendations for content
     */
    public function get_content_recommendations($content, $post_id = null) {
        $analysis = $this->analyze_content($content, 'recommendations');
        
        $recommendations = array();
        
        // SEO recommendations
        if (isset($analysis['seo_analysis']['recommendations'])) {
            foreach ($analysis['seo_analysis']['recommendations'] as $rec) {
                $recommendations[] = array(
                    'type' => 'seo',
                    'priority' => 'high',
                    'title' => 'SEO Improvement',
                    'description' => $rec,
                    'actionable' => true
                );
            }
        }
        
        // Content recommendations
        if (isset($analysis['improvement_suggestions'])) {
            foreach ($analysis['improvement_suggestions'] as $suggestion) {
                $recommendations[] = array(
                    'type' => 'content',
                    'priority' => 'medium',
                    'title' => 'Content Enhancement',
                    'description' => $suggestion,
                    'actionable' => true
                );
            }
        }
        
        // Engagement recommendations
        if (isset($analysis['engagement_potential']['score']) && $analysis['engagement_potential']['score'] < 70) {
            $recommendations[] = array(
                'type' => 'engagement',
                'priority' => 'medium',
                'title' => 'Improve Engagement',
                'description' => 'Content engagement potential is below optimal. Consider adding more interactive elements or improving readability.',
                'actionable' => true
            );
        }
        
        return $recommendations;
    }
    
    /**
     * Analyze content with AI
     */
    public function analyze_content_ai($content, $post_id = 0) {
        $api_key = get_option('wpsi_openai_api_key', '');
        $model = get_option('wpsi_ai_model', 'gpt-3.5-turbo');
        
        if (empty($api_key)) {
            return array('success' => false, 'message' => 'OpenAI API key not configured');
        }
        
        $prompt = $this->build_analysis_prompt($content);
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are a content analysis expert. Provide detailed analysis in JSON format.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => 2000,
                'temperature' => 0.3
            )),
            'timeout' => 60
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'AI analysis failed: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            $analysis = json_decode($data['choices'][0]['message']['content'], true);
            
            if ($analysis && $post_id > 0) {
                update_post_meta($post_id, '_wpsi_ai_analysis', $analysis);
            }
            
            return array('success' => true, 'analysis' => $analysis);
        } else {
            return array('success' => false, 'message' => 'Invalid AI response format');
        }
    }
    
    /**
     * Save AI configuration
     */
    public function save_ai_config($data) {
        update_option('wpsi_openai_api_key', sanitize_text_field($data['openai_api_key']));
        update_option('wpsi_google_ai_key', sanitize_text_field($data['google_ai_key']));
        update_option('wpsi_ai_model', sanitize_text_field($data['ai_model']));
        update_option('wpsi_ai_enabled', isset($data['ai_enabled']));
        update_option('wpsi_ai_analysis_types', isset($data['analysis_types']) ? $data['analysis_types'] : array());
        update_option('wpsi_ai_confidence_threshold', floatval($data['confidence_threshold']));
        
        return array('success' => true, 'message' => 'AI configuration saved successfully');
    }
    
    /**
     * Test AI connection
     */
    public function test_ai_connection() {
        $api_key = get_option('wpsi_openai_api_key', '');
        $model = get_option('wpsi_ai_model', 'gpt-3.5-turbo');
        
        if (empty($api_key)) {
            return array('success' => false, 'message' => 'OpenAI API key not configured');
        }
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => 'Hello, this is a test message from Smart Insights plugin.'
                    )
                ),
                'max_tokens' => 50,
                'temperature' => 0.1
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'Connection test failed: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code === 200) {
            return array('success' => true, 'message' => 'AI connection test successful');
        } else {
            $body = wp_remote_retrieve_body($response);
            $error = json_decode($body, true);
            $error_message = isset($error['error']['message']) ? $error['error']['message'] : 'Unknown error';
            return array('success' => false, 'message' => 'Connection test failed: ' . $error_message);
        }
    }
    
    /**
     * Get AI models
     */
    public function get_ai_models() {
        $api_key = get_option('wpsi_openai_api_key', '');
        
        if (empty($api_key)) {
            return array('success' => false, 'message' => 'OpenAI API key not configured');
        }
        
        $response = wp_remote_get('https://api.openai.com/v1/models', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'Failed to fetch models: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['data'])) {
            $models = array();
            foreach ($data['data'] as $model) {
                if (strpos($model['id'], 'gpt') === 0) {
                    $models[] = array(
                        'id' => $model['id'],
                        'name' => $model['id'],
                        'created' => $model['created']
                    );
                }
            }
            return array('success' => true, 'models' => $models);
        } else {
            return array('success' => false, 'message' => 'Invalid response format');
        }
    }
    
    /**
     * Get AI usage
     */
    public function get_ai_usage() {
        $api_key = get_option('wpsi_openai_api_key', '');
        
        if (empty($api_key)) {
            return array('success' => false, 'message' => 'OpenAI API key not configured');
        }
        
        $current_date = gmdate('Y-m-d');
        $first_day = gmdate('Y-m-01');
        
        $response = wp_remote_get("https://api.openai.com/v1/usage?date=$current_date", array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'Failed to fetch usage: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['data'])) {
            $usage = array(
                'daily_usage' => $data['data'][0]['line_items'][0]['cost'] ?? 0,
                'daily_requests' => $data['data'][0]['line_items'][0]['n_requests'] ?? 0,
                'daily_tokens' => $data['data'][0]['line_items'][0]['n_generated_tokens'] ?? 0
            );
            
            return array('success' => true, 'usage' => $usage);
        } else {
            return array('success' => false, 'message' => 'Invalid usage response format');
        }
    }
    
    /**
     * Reset AI configuration
     */
    public function reset_ai_config() {
        delete_option('wpsi_openai_api_key');
        delete_option('wpsi_google_ai_key');
        delete_option('wpsi_ai_model');
        delete_option('wpsi_ai_enabled');
        delete_option('wpsi_ai_analysis_types');
        delete_option('wpsi_ai_confidence_threshold');
        
        return array('success' => true, 'message' => 'AI configuration reset to defaults');
    }
    
    /**
     * Build analysis prompt
     */
    private function build_analysis_prompt($content) {
        $analysis_types = get_option('wpsi_ai_analysis_types', array('readability', 'sentiment', 'tone'));
        
        $prompt = "Analyze the following content and provide insights in JSON format:\n\n";
        $prompt .= "Content: " . substr($content, 0, 2000) . "\n\n";
        $prompt .= "Please provide analysis for the following aspects:\n";
        
        if (in_array('readability', $analysis_types)) {
            $prompt .= "- Readability score (0-100)\n";
            $prompt .= "- Reading level (elementary, middle school, high school, college, graduate)\n";
            $prompt .= "- Sentence complexity\n";
        }
        
        if (in_array('sentiment', $analysis_types)) {
            $prompt .= "- Sentiment analysis (positive, negative, neutral)\n";
            $prompt .= "- Sentiment score (-1 to 1)\n";
            $prompt .= "- Emotional tone\n";
        }
        
        if (in_array('tone', $analysis_types)) {
            $prompt .= "- Writing tone (formal, casual, technical, persuasive, etc.)\n";
            $prompt .= "- Tone consistency\n";
        }
        
        $prompt .= "- Key themes and topics\n";
        $prompt .= "- Content quality score (0-100)\n";
        $prompt .= "- Improvement suggestions\n\n";
        
        $prompt .= "Return the analysis in this JSON format:\n";
        $prompt .= '{"readability": {"score": 75, "level": "high school", "complexity": "moderate"}, "sentiment": {"overall": "positive", "score": 0.6, "tone": "optimistic"}, "tone": {"style": "informative", "consistency": "good"}, "themes": ["topic1", "topic2"], "quality_score": 82, "suggestions": ["suggestion1", "suggestion2"]}';
        
        return $prompt;
    }
} 