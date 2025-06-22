<?php
/**
 * Export Data Admin View
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
    <h1><?php esc_html_e('Export Data', 'wp-smart-insights'); ?></h1>
    
    <div class="wpsi-admin-container">
        <!-- Export Configuration Section -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Export Configuration', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_export_format"><?php esc_html_e('Export Format:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_export_format" name="wpsi_export_format">
                    <option value="csv"><?php esc_html_e('CSV', 'wp-smart-insights'); ?></option>
                    <option value="json"><?php esc_html_e('JSON', 'wp-smart-insights'); ?></option>
                    <option value="xml"><?php esc_html_e('XML', 'wp-smart-insights'); ?></option>
                    <option value="excel"><?php esc_html_e('Excel (XLSX)', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_export_date_range"><?php esc_html_e('Date Range:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_export_date_range" name="wpsi_export_date_range">
                    <option value="all"><?php esc_html_e('All Time', 'wp-smart-insights'); ?></option>
                    <option value="today"><?php esc_html_e('Today', 'wp-smart-insights'); ?></option>
                    <option value="yesterday"><?php esc_html_e('Yesterday', 'wp-smart-insights'); ?></option>
                    <option value="last_7_days"><?php esc_html_e('Last 7 Days', 'wp-smart-insights'); ?></option>
                    <option value="last_30_days"><?php esc_html_e('Last 30 Days', 'wp-smart-insights'); ?></option>
                    <option value="last_90_days"><?php esc_html_e('Last 90 Days', 'wp-smart-insights'); ?></option>
                    <option value="custom"><?php esc_html_e('Custom Range', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group" id="wpsi_custom_date_range" style="display: none;">
                <label><?php esc_html_e('Custom Date Range:', 'wp-smart-insights'); ?></label>
                <input type="date" id="wpsi_export_start_date" name="wpsi_export_start_date">
                <span><?php esc_html_e('to', 'wp-smart-insights'); ?></span>
                <input type="date" id="wpsi_export_end_date" name="wpsi_export_end_date">
            </div>
        </div>
        
        <!-- Heatmap Data Export -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Heatmap Data Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_heatmap_post_filter"><?php esc_html_e('Filter by Post:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_heatmap_post_filter" name="wpsi_heatmap_post_filter">
                    <option value="all"><?php esc_html_e('All Posts', 'wp-smart-insights'); ?></option>
                    <?php
                    $posts = get_posts(array(
                        'post_type' => array('post', 'page'),
                        'numberposts' => 100,
                        'orderby' => 'title'
                    ));
                    foreach ($posts as $post) {
                        echo '<option value="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_heatmap_data_type"><?php esc_html_e('Data Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_heatmap_data_type" name="wpsi_heatmap_data_type">
                    <option value="all"><?php esc_html_e('All Data', 'wp-smart-insights'); ?></option>
                    <option value="clicks"><?php esc_html_e('Click Data Only', 'wp-smart-insights'); ?></option>
                    <option value="scrolls"><?php esc_html_e('Scroll Data Only', 'wp-smart-insights'); ?></option>
                    <option value="hovers"><?php esc_html_e('Hover Data Only', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_export_heatmaps" class="button button-primary"><?php esc_html_e('Export Heatmap Data', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- User Journey Export -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('User Journey Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_journey_post_filter"><?php esc_html_e('Filter by Post:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_journey_post_filter" name="wpsi_journey_post_filter">
                    <option value="all"><?php esc_html_e('All Posts', 'wp-smart-insights'); ?></option>
                    <?php
                    foreach ($posts as $post) {
                        echo '<option value="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_journey_session_filter"><?php esc_html_e('Session Filter:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_journey_session_filter" name="wpsi_journey_session_filter">
                    <option value="all"><?php esc_html_e('All Sessions', 'wp-smart-insights'); ?></option>
                    <option value="completed"><?php esc_html_e('Completed Sessions Only', 'wp-smart-insights'); ?></option>
                    <option value="abandoned"><?php esc_html_e('Abandoned Sessions Only', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_export_journeys" class="button button-primary"><?php esc_html_e('Export User Journeys', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Analytics Data Export -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Analytics Data Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_analytics_event_type"><?php esc_html_e('Event Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_analytics_event_type" name="wpsi_analytics_event_type">
                    <option value="all"><?php esc_html_e('All Events', 'wp-smart-insights'); ?></option>
                    <option value="page_view"><?php esc_html_e('Page Views', 'wp-smart-insights'); ?></option>
                    <option value="click"><?php esc_html_e('Clicks', 'wp-smart-insights'); ?></option>
                    <option value="scroll"><?php esc_html_e('Scrolls', 'wp-smart-insights'); ?></option>
                    <option value="form_submit"><?php esc_html_e('Form Submissions', 'wp-smart-insights'); ?></option>
                    <option value="conversion"><?php esc_html_e('Conversions', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_analytics_group_by"><?php esc_html_e('Group By:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_analytics_group_by" name="wpsi_analytics_group_by">
                    <option value="none"><?php esc_html_e('No Grouping', 'wp-smart-insights'); ?></option>
                    <option value="date"><?php esc_html_e('Date', 'wp-smart-insights'); ?></option>
                    <option value="post"><?php esc_html_e('Post', 'wp-smart-insights'); ?></option>
                    <option value="session"><?php esc_html_e('Session', 'wp-smart-insights'); ?></option>
                    <option value="event_type"><?php esc_html_e('Event Type', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_export_analytics" class="button button-primary"><?php esc_html_e('Export Analytics Data', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Content Analysis Export -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Content Analysis Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_content_post_type"><?php esc_html_e('Post Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_content_post_type" name="wpsi_content_post_type">
                    <option value="all"><?php esc_html_e('All Content', 'wp-smart-insights'); ?></option>
                    <option value="post"><?php esc_html_e('Posts Only', 'wp-smart-insights'); ?></option>
                    <option value="page"><?php esc_html_e('Pages Only', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_content_analysis_type"><?php esc_html_e('Analysis Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_content_analysis_type" name="wpsi_content_analysis_type">
                    <option value="all"><?php esc_html_e('All Analysis', 'wp-smart-insights'); ?></option>
                    <option value="readability"><?php esc_html_e('Readability Scores', 'wp-smart-insights'); ?></option>
                    <option value="seo"><?php esc_html_e('SEO Analysis', 'wp-smart-insights'); ?></option>
                    <option value="engagement"><?php esc_html_e('Engagement Metrics', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_export_content_analysis" class="button button-primary"><?php esc_html_e('Export Content Analysis', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Bulk Export Section -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Bulk Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label><?php esc_html_e('Select Data Types to Export:', 'wp-smart-insights'); ?></label>
                <div class="wpsi-checkbox-group">
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="heatmaps" checked> <?php esc_html_e('Heatmap Data', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="journeys" checked> <?php esc_html_e('User Journeys', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="analytics" checked> <?php esc_html_e('Analytics Events', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="content_analysis" checked> <?php esc_html_e('Content Analysis', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="seo_scores" checked> <?php esc_html_e('SEO Scores', 'wp-smart-insights'); ?></label>
                </div>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_bulk_export_format"><?php esc_html_e('Export as:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_bulk_export_format" name="wpsi_bulk_export_format">
                    <option value="separate_files"><?php esc_html_e('Separate Files', 'wp-smart-insights'); ?></option>
                    <option value="zip_archive"><?php esc_html_e('ZIP Archive', 'wp-smart-insights'); ?></option>
                    <option value="combined_file"><?php esc_html_e('Combined File', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_bulk_export" class="button button-secondary"><?php esc_html_e('Start Bulk Export', 'wp-smart-insights'); ?></button>
            
            <div id="wpsi_bulk_export_progress" class="wpsi-progress" style="display: none;">
                <div class="wpsi-progress-bar">
                    <div class="wpsi-progress-fill"></div>
                </div>
                <div class="wpsi-progress-text">0%</div>
            </div>
        </div>
        
        <!-- Export History -->
        <div class="wpsi-card">
            <h2><?php esc_html_e('Export History', 'wp-smart-insights'); ?></h2>
            
            <div id="wpsi_export_history">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Type', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Format', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Records', 'wp-smart-insights'); ?></th>
                            <th><?php esc_html_e('Actions', 'wp-smart-insights'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wpsi_export_history_body">
                        <!-- Export history will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Show/hide custom date range
    $('#wpsi_export_date_range').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#wpsi_custom_date_range').show();
        } else {
            $('#wpsi_custom_date_range').hide();
        }
    });
    
    // Export Heatmap Data
    $('#wpsi_export_heatmaps').on('click', function() {
        var data = {
            action: 'wpsi_export_heatmaps',
            nonce: wpsi_ajax.nonce,
            format: $('#wpsi_export_format').val(),
            date_range: $('#wpsi_export_date_range').val(),
            start_date: $('#wpsi_export_start_date').val(),
            end_date: $('#wpsi_export_end_date').val(),
            post_filter: $('#wpsi_heatmap_post_filter').val(),
            data_type: $('#wpsi_heatmap_data_type').val()
        };
        
        $(this).prop('disabled', true).text('Exporting...');
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_export_heatmaps').prop('disabled', false).text('Export Heatmap Data');
            
            if (response.success) {
                window.location.href = response.data.download_url;
            } else {
                alert('Error exporting data: ' + response.data);
            }
        });
    });
    
    // Export User Journeys
    $('#wpsi_export_journeys').on('click', function() {
        var data = {
            action: 'wpsi_export_journeys',
            nonce: wpsi_ajax.nonce,
            format: $('#wpsi_export_format').val(),
            date_range: $('#wpsi_export_date_range').val(),
            start_date: $('#wpsi_export_start_date').val(),
            end_date: $('#wpsi_export_end_date').val(),
            post_filter: $('#wpsi_journey_post_filter').val(),
            session_filter: $('#wpsi_journey_session_filter').val()
        };
        
        $(this).prop('disabled', true).text('Exporting...');
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_export_journeys').prop('disabled', false).text('Export User Journeys');
            
            if (response.success) {
                window.location.href = response.data.download_url;
            } else {
                alert('Error exporting data: ' + response.data);
            }
        });
    });
    
    // Export Analytics Data
    $('#wpsi_export_analytics').on('click', function() {
        var data = {
            action: 'wpsi_export_analytics',
            nonce: wpsi_ajax.nonce,
            format: $('#wpsi_export_format').val(),
            date_range: $('#wpsi_export_date_range').val(),
            start_date: $('#wpsi_export_start_date').val(),
            end_date: $('#wpsi_export_end_date').val(),
            event_type: $('#wpsi_analytics_event_type').val(),
            group_by: $('#wpsi_analytics_group_by').val()
        };
        
        $(this).prop('disabled', true).text('Exporting...');
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_export_analytics').prop('disabled', false).text('Export Analytics Data');
            
            if (response.success) {
                window.location.href = response.data.download_url;
            } else {
                alert('Error exporting data: ' + response.data);
            }
        });
    });
    
    // Export Content Analysis
    $('#wpsi_export_content_analysis').on('click', function() {
        var data = {
            action: 'wpsi_export_content_analysis',
            nonce: wpsi_ajax.nonce,
            format: $('#wpsi_export_format').val(),
            post_type: $('#wpsi_content_post_type').val(),
            analysis_type: $('#wpsi_content_analysis_type').val()
        };
        
        $(this).prop('disabled', true).text('Exporting...');
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_export_content_analysis').prop('disabled', false).text('Export Content Analysis');
            
            if (response.success) {
                window.location.href = response.data.download_url;
            } else {
                alert('Error exporting data: ' + response.data);
            }
        });
    });
    
    // Bulk Export
    $('#wpsi_bulk_export').on('click', function() {
        var selected_types = $('input[name="wpsi_bulk_export[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        if (selected_types.length === 0) {
            alert('Please select at least one data type to export.');
            return;
        }
        
        var data = {
            action: 'wpsi_bulk_export',
            nonce: wpsi_ajax.nonce,
            format: $('#wpsi_export_format').val(),
            date_range: $('#wpsi_export_date_range').val(),
            start_date: $('#wpsi_export_start_date').val(),
            end_date: $('#wpsi_export_end_date').val(),
            export_types: selected_types,
            bulk_format: $('#wpsi_bulk_export_format').val()
        };
        
        $(this).prop('disabled', true).text('Starting bulk export...');
        $('#wpsi_bulk_export_progress').show();
        
        $.post(wpsi_ajax.ajax_url, data, function(response) {
            $('#wpsi_bulk_export').prop('disabled', false).text('Start Bulk Export');
            $('#wpsi_bulk_export_progress').hide();
            
            if (response.success) {
                window.location.href = response.data.download_url;
            } else {
                alert('Error starting bulk export: ' + response.data);
            }
        });
    });
    
    // Load export history
    function loadExportHistory() {
        $.post(wpsi_ajax.ajax_url, {
            action: 'wpsi_get_export_history',
            nonce: wpsi_ajax.nonce
        }, function(response) {
            if (response.success) {
                $('#wpsi_export_history_body').html(response.data.html);
            }
        });
    }
    
    // Load export history on page load
    loadExportHistory();
});
</script> 