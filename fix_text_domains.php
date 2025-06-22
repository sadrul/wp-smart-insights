<?php
/**
 * Script to fix text domain mismatches
 * Replace all occurrences of 'smart-insights-content-intelligence-ux-heatmap' with 'wp-smart-insights'
 */

function fix_text_domains($content) {
    // Replace the long text domain with the correct one
    $content = str_replace(
        "'smart-insights-content-intelligence-ux-heatmap'",
        "'wp-smart-insights'",
        $content
    );
    
    $content = str_replace(
        '"smart-insights-content-intelligence-ux-heatmap"',
        '"wp-smart-insights"',
        $content
    );
    
    return $content;
}

// Files to process
$files = [
    'wp-smart-insights.php',
    'includes/class-content-analyzer.php',
    'includes/class-seo-checker.php',
    'includes/class-heatmap-tracker.php',
    'includes/class-user-journey.php',
    'includes/class-privacy-manager.php',
    'includes/class-analytics-service.php',
    'includes/class-ai-service.php',
    'includes/class-notification-service.php',
    'includes/class-export-service.php',
    'includes/class-rest-api.php',
    'admin/views/dashboard.php',
    'admin/views/heatmaps.php',
    'admin/views/settings.php',
    'admin/views/export.php',
    'admin/views/ai-analysis.php',
    'admin/views/content-analysis.php',
    'admin/views/seo.php',
    'admin/views/notifications.php',
    'admin/views/journeys.php',
    'admin/views/analytics.php',
    'assets/js/admin.js',
    'assets/js/frontend.js'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $original_content = $content;
        
        // Apply fixes
        $content = fix_text_domains($content);
        
        // Write back if changes were made
        if ($content !== $original_content) {
            file_put_contents($file, $content);
            echo "Fixed text domains in: $file\n";
        }
    }
}

echo "Text domain fixes completed!\n";
?> 