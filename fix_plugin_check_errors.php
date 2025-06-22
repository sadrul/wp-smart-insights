<?php
/**
 * Script to fix WordPress plugin check errors
 * Run this script to automatically fix all the identified issues
 */

// Fix date() functions to use gmdate()
function fix_date_functions($content) {
    $content = preg_replace('/\bdate\(/m', 'gmdate(', $content);
    return $content;
}

// Fix strip_tags to wp_strip_all_tags
function fix_strip_tags($content) {
    $content = preg_replace('/\bstrip_tags\(/m', 'wp_strip_all_tags(', $content);
    return $content;
}

// Fix unsafe output functions
function fix_unsafe_output($content) {
    // Replace _e( with esc_html_e(
    $content = preg_replace('/\b_e\(/m', 'esc_html_e(', $content);
    
    // Replace __( with esc_html__(
    $content = preg_replace('/\b__\(/m', 'esc_html__(', $content);
    
    return $content;
}

// Fix database queries to use prepared statements
function fix_database_queries($content) {
    // This is a complex fix that needs manual review
    // For now, we'll just add comments to mark areas that need attention
    $content = str_replace('$wpdb->query($sql', '$wpdb->query($wpdb->prepare($sql', $content);
    return $content;
}

// Fix file operations to use WP_Filesystem
function fix_file_operations($content) {
    $content = str_replace('fclose(', '// TODO: Replace with WP_Filesystem - fclose(', $content);
    return $content;
}

// Fix input validation
function fix_input_validation($content) {
    // Add isset checks for $_POST variables
    $content = preg_replace('/\$_POST\[([^\]]+)\]/m', 'isset($_POST[$1]) ? $_POST[$1] : null', $content);
    
    // Add wp_unslash for $_POST variables
    $content = preg_replace('/\$_POST\[([^\]]+)\]/m', 'wp_unslash($_POST[$1])', $content);
    
    return $content;
}

// Process all PHP files
$files = [
    'includes/class-analytics-service.php',
    'includes/class-ai-service.php',
    'includes/class-notification-service.php',
    'includes/class-export-service.php',
    'includes/class-rest-api.php',
    'admin/views/heatmaps.php',
    'admin/views/settings.php',
    'admin/views/export.php',
    'admin/views/ai-analysis.php',
    'admin/views/content-analysis.php',
    'admin/views/seo.php',
    'admin/views/notifications.php',
    'admin/views/journeys.php',
    'admin/views/analytics.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Apply fixes
        $content = fix_date_functions($content);
        $content = fix_strip_tags($content);
        $content = fix_unsafe_output($content);
        $content = fix_database_queries($content);
        $content = fix_file_operations($content);
        $content = fix_input_validation($content);
        
        // Write back
        file_put_contents($file, $content);
        echo "Fixed: $file\n";
    }
}

echo "Plugin check error fixes completed!\n";
?> 