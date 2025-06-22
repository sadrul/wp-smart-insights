<?php
/**
 * SEO Checker Class
 * 
 * Handles SEO analysis including H1-H6 structure, meta tags, internal linking, and image alt tags
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPSI_SEO_Checker {
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_seo_meta_box'));
        add_action('save_post', array($this, 'auto_check_seo'));
    }
    
    public function add_seo_meta_box() {
        add_meta_box(
            'wpsi-seo-checker',
            __('Smart Insights - SEO Checker', 'wp-smart-insights'),
            array($this, 'render_meta_box'),
            'post',
            'side',
            'default'
        );
        
        add_meta_box(
            'wpsi-seo-checker',
            __('Smart Insights - SEO Checker', 'wp-smart-insights'),
            array($this, 'render_meta_box'),
            'page',
            'side',
            'default'
        );
    }
    
    public function render_meta_box($post) {
        wp_nonce_field('wpsi_seo_checker', 'wpsi_seo_checker_nonce');
        
        $seo_analysis = get_post_meta($post->ID, '_wpsi_seo_analysis', true);
        
        echo '<div id="wpsi-seo-checker-container">';
        
        if ($seo_analysis) {
            $this->display_seo_results($seo_analysis);
        } else {
            echo '<p>' . esc_html__('No SEO analysis available. Click "Check SEO" to get started.', 'wp-smart-insights') . '</p>';
        }
        
        echo '<button type="button" id="wpsi-check-seo" class="button button-primary">';
        echo esc_html__('Check SEO', 'wp-smart-insights');
        echo '</button>';
        
        echo '<div id="wpsi-seo-loading" style="display: none;">';
        echo '<p>' . esc_html__('Analyzing SEO...', 'wp-smart-insights') . '</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    public function display_seo_results($analysis) {
        echo '<div class="wpsi-seo-results">';
        
        // Overall SEO Score
        if (isset($analysis['overall_score'])) {
            echo '<div class="wpsi-score-section">';
            echo '<h4>' . esc_html__('SEO Score', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-score-circle" data-score="' . esc_attr($analysis['overall_score']) . '">';
            echo '<span class="wpsi-score-number">' . esc_html($analysis['overall_score']) . '</span>';
            echo '<span class="wpsi-score-label">/100</span>';
            echo '</div>';
            echo '</div>';
        }
        
        // Headings Structure
        if (isset($analysis['headings'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . esc_html__('Headings Structure', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-headings-list">';
            foreach ($analysis['headings']['structure'] as $heading) {
                $class = $heading['valid'] ? 'wpsi-valid' : 'wpsi-invalid';
                echo '<div class="wpsi-heading-item ' . esc_attr($class) . '">';
                echo '<span class="wpsi-heading-tag">' . esc_html($heading['tag']) . '</span>';
                echo '<span class="wpsi-heading-text">' . esc_html($heading['text']) . '</span>';
                echo '</div>';
            }
            echo '</div>';
            if (!empty($analysis['headings']['issues'])) {
                echo '<ul class="wpsi-suggestions">';
                foreach ($analysis['headings']['issues'] as $issue) {
                    echo '<li>' . esc_html($issue) . '</li>';
                }
                echo '</ul>';
            }
            echo '</div>';
        }
        
        // Meta Tags
        if (isset($analysis['meta_tags'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . esc_html__('Meta Tags', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-meta-tags">';
            foreach ($analysis['meta_tags'] as $tag => $status) {
                $class = $status['valid'] ? 'wpsi-valid' : 'wpsi-invalid';
                echo '<div class="wpsi-meta-item ' . esc_attr($class) . '">';
                echo '<span class="wpsi-meta-name">' . esc_html($tag) . '</span>';
                echo '<span class="wpsi-meta-status">' . esc_html($status['message']) . '</span>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        // Internal Links
        if (isset($analysis['internal_links'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . esc_html__('Internal Links', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-links-info">';
            echo '<p>' . esc_html__('Total: ', 'wp-smart-insights') . esc_html($analysis['internal_links']['count']) . '</p>';
            echo '<p>' . esc_html__('Valid: ', 'wp-smart-insights') . esc_html($analysis['internal_links']['valid']) . '</p>';
            echo '<p>' . esc_html__('Broken: ', 'wp-smart-insights') . esc_html($analysis['internal_links']['broken']) . '</p>';
            echo '</div>';
            if (!empty($analysis['internal_links']['broken_links'])) {
                echo '<div class="wpsi-broken-links">';
                echo '<h5>' . esc_html__('Broken Links:', 'wp-smart-insights') . '</h5>';
                echo '<ul class="wpsi-suggestions">';
                foreach ($analysis['internal_links']['broken_links'] as $link) {
                    echo '<li>' . esc_html($link) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            echo '</div>';
        }
        
        // Images
        if (isset($analysis['images'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . esc_html__('Images', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-images-info">';
            echo '<p>' . esc_html__('Total: ', 'wp-smart-insights') . esc_html($analysis['images']['count']) . '</p>';
            echo '<p>' . esc_html__('With Alt: ', 'wp-smart-insights') . esc_html($analysis['images']['with_alt']) . '</p>';
            echo '<p>' . esc_html__('Missing Alt: ', 'wp-smart-insights') . esc_html($analysis['images']['missing_alt']) . '</p>';
            echo '</div>';
            if (!empty($analysis['images']['missing_alt_images'])) {
                echo '<div class="wpsi-missing-alt">';
                echo '<h5>' . esc_html__('Images Missing Alt Text:', 'wp-smart-insights') . '</h5>';
                echo '<ul class="wpsi-suggestions">';
                foreach ($analysis['images']['missing_alt_images'] as $image) {
                    echo '<li>' . esc_html($image) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            echo '</div>';
        }
        
        // Quick Fixes
        if (isset($analysis['quick_fixes']) && !empty($analysis['quick_fixes'])) {
            echo '<div class="wpsi-metric-section">';
            echo '<h4>' . esc_html__('Quick Fixes', 'wp-smart-insights') . '</h4>';
            echo '<div class="wpsi-quick-fixes">';
            foreach ($analysis['quick_fixes'] as $fix) {
                echo '<button type="button" class="button wpsi-quick-fix" data-fix="' . esc_attr($fix['action']) . '">';
                echo esc_html($fix['label']);
                echo '</button>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    public function analyze_post($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        $content = $post->post_content;
        $title = $post->post_title;
        
        $analysis = array(
            'overall_score' => 0,
            'headings' => $this->analyze_headings($content),
            'meta_tags' => $this->analyze_meta_tags($post_id),
            'internal_links' => $this->analyze_internal_links($content),
            'images' => $this->analyze_images($content),
            'quick_fixes' => array(),
            'timestamp' => current_time('mysql'),
        );
        
        // Calculate overall score
        $analysis['overall_score'] = $this->calculate_seo_score($analysis);
        
        // Generate quick fixes
        $analysis['quick_fixes'] = $this->generate_quick_fixes($analysis);
        
        return $analysis;
    }
    
    private function analyze_headings($content) {
        $headings = array();
        $structure = array();
        $issues = array();
        
        // Extract headings from content
        preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $level = intval($match[1]);
            $text = wp_strip_all_tags($match[2]);
            
            $headings[] = array(
                'level' => $level,
                'text' => $text,
                'tag' => 'h' . $level
            );
            
            $structure[] = array(
                'tag' => 'h' . $level,
                'text' => $text,
                'valid' => true
            );
        }
        
        // Check for heading structure issues
        if (empty($headings)) {
            $issues[] = __('No headings found. Add H1-H6 tags to improve structure.', 'wp-smart-insights');
        } else {
            // Check for H1
            $h1_count = 0;
            foreach ($headings as $heading) {
                if ($heading['level'] === 1) {
                    $h1_count++;
                }
            }
            
            if ($h1_count === 0) {
                $issues[] = __('No H1 heading found. Add a main heading to improve SEO.', 'wp-smart-insights');
            } elseif ($h1_count > 1) {
                $issues[] = __('Multiple H1 headings found. Use only one H1 per page.', 'wp-smart-insights');
            }
            
            // Check heading hierarchy
            $current_level = 0;
            foreach ($headings as $heading) {
                if ($heading['level'] > $current_level + 1) {
                    $issues[] = __('Heading hierarchy is broken. Don\'t skip heading levels.', 'wp-smart-insights');
                    break;
                }
                $current_level = $heading['level'];
            }
        }
        
        return array(
            'count' => count($headings),
            'structure' => $structure,
            'issues' => $issues
        );
    }
    
    private function analyze_meta_tags($post_id) {
        $meta_tags = array();
        
        // Title
        $title = get_the_title($post_id);
        $meta_tags['title'] = array(
            'valid' => !empty($title) && strlen($title) <= 60,
            'message' => !empty($title) ? (strlen($title) <= 60 ? __('Good length', 'wp-smart-insights') : __('Too long', 'wp-smart-insights')) : __('Missing', 'wp-smart-insights'),
            'value' => $title
        );
        
        // Meta description
        $meta_description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        if (empty($meta_description)) {
            $meta_description = get_post_meta($post_id, '_aioseo_description', true);
        }
        
        $meta_tags['description'] = array(
            'valid' => !empty($meta_description) && strlen($meta_description) >= 120 && strlen($meta_description) <= 160,
            'message' => !empty($meta_description) ? (strlen($meta_description) >= 120 && strlen($meta_description) <= 160 ? __('Good length', 'wp-smart-insights') : __('Length issue', 'wp-smart-insights')) : __('Missing', 'wp-smart-insights'),
            'value' => $meta_description
        );
        
        // Focus keyword
        $focus_keyword = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        if (empty($focus_keyword)) {
            $focus_keyword = get_post_meta($post_id, '_aioseo_keywords', true);
        }
        
        $meta_tags['focus_keyword'] = array(
            'valid' => !empty($focus_keyword),
            'message' => !empty($focus_keyword) ? __('Set', 'wp-smart-insights') : __('Missing', 'wp-smart-insights'),
            'value' => $focus_keyword
        );
        
        return $meta_tags;
    }
    
    private function analyze_internal_links($content) {
        $links = array();
        $internal_links = array();
        $broken_links = array();
        
        // Extract all links
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/i', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $url = $match[1];
            $text = wp_strip_all_tags($match[2]);
            
            // Check if it's an internal link
            if (strpos($url, home_url()) === 0 || strpos($url, '/') === 0) {
                $internal_links[] = array(
                    'url' => $url,
                    'text' => $text
                );
                
                // Check if link is broken
                if (!$this->is_link_valid($url)) {
                    $broken_links[] = $url;
                }
            }
        }
        
        return array(
            'count' => count($internal_links),
            'valid' => count($internal_links) - count($broken_links),
            'broken' => count($broken_links),
            'broken_links' => $broken_links
        );
    }
    
    private function analyze_images($content) {
        $images = array();
        $with_alt = 0;
        $missing_alt = 0;
        $missing_alt_images = array();
        
        // Extract all images
        preg_match_all('/<img[^>]+>/i', $content, $matches);
        
        foreach ($matches[0] as $img_tag) {
            $images[] = $img_tag;
            
            // Check for alt attribute
            if (preg_match('/alt=["\']([^"\']*)["\']/i', $img_tag, $alt_match)) {
                if (!empty($alt_match[1])) {
                    $with_alt++;
                } else {
                    $missing_alt++;
                    $missing_alt_images[] = $img_tag;
                }
            } else {
                $missing_alt++;
                $missing_alt_images[] = $img_tag;
            }
        }
        
        return array(
            'count' => count($images),
            'with_alt' => $with_alt,
            'missing_alt' => $missing_alt,
            'missing_alt_images' => array_slice($missing_alt_images, 0, 5) // Limit to first 5
        );
    }
    
    private function calculate_seo_score($analysis) {
        $score = 0;
        
        // Headings (25 points)
        if (!empty($analysis['headings']['structure'])) {
            $score += 15; // Basic structure
            if (empty($analysis['headings']['issues'])) {
                $score += 10; // No issues
            }
        }
        
        // Meta tags (30 points)
        $meta_score = 0;
        foreach ($analysis['meta_tags'] as $tag) {
            if ($tag['valid']) {
                $meta_score += 10;
            }
        }
        $score += min(30, $meta_score);
        
        // Internal links (20 points)
        if ($analysis['internal_links']['count'] > 0) {
            $score += 10;
            if ($analysis['internal_links']['broken'] === 0) {
                $score += 10;
            }
        }
        
        // Images (15 points)
        if ($analysis['images']['count'] > 0) {
            $alt_ratio = $analysis['images']['with_alt'] / $analysis['images']['count'];
            $score += round($alt_ratio * 15);
        }
        
        // Content length (10 points)
        $content_length = strlen($analysis['content'] ?? '');
        if ($content_length > 300) {
            $score += 10;
        } elseif ($content_length > 150) {
            $score += 5;
        }
        
        return min(100, $score);
    }
    
    private function generate_quick_fixes($analysis) {
        $fixes = array();
        
        // Add missing meta description
        if (isset($analysis['meta_tags']['description']) && !$analysis['meta_tags']['description']['valid']) {
            $fixes[] = array(
                'action' => 'add_meta_description',
                'label' => __('Add Meta Description', 'wp-smart-insights')
            );
        }
        
        // Add focus keyword
        if (isset($analysis['meta_tags']['focus_keyword']) && !$analysis['meta_tags']['focus_keyword']['valid']) {
            $fixes[] = array(
                'action' => 'add_focus_keyword',
                'label' => __('Add Focus Keyword', 'wp-smart-insights')
            );
        }
        
        // Fix broken links
        if ($analysis['internal_links']['broken'] > 0) {
            $fixes[] = array(
                'action' => 'fix_broken_links',
                'label' => __('Fix Broken Links', 'wp-smart-insights')
            );
        }
        
        // Add missing alt text
        if ($analysis['images']['missing_alt'] > 0) {
            $fixes[] = array(
                'action' => 'add_alt_text',
                'label' => __('Add Alt Text to Images', 'wp-smart-insights')
            );
        }
        
        return $fixes;
    }
    
    private function is_link_valid($url) {
        // For internal links, check if the post/page exists
        if (strpos($url, home_url()) === 0) {
            $path = str_replace(home_url(), '', $url);
            $post_id = url_to_postid($url);
            return $post_id > 0;
        }
        
        // For relative links, assume they're valid
        if (strpos($url, '/') === 0) {
            return true;
        }
        
        return false;
    }
    
    public function auto_check_seo($post_id) {
        // Don't check on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Only check posts and pages
        if (!in_array(get_post_type($post_id), array('post', 'page'))) {
            return;
        }
        
        $analysis = $this->analyze_post($post_id);
        if ($analysis) {
            update_post_meta($post_id, '_wpsi_seo_analysis', $analysis);
        }
    }
} 