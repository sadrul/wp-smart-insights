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
    <h1><?php _e('Export Data', 'wp-smart-insights'); ?></h1>
    
    <div class="wpsi-admin-container">
        <!-- Export Configuration Section -->
        <div class="wpsi-card">
            <h2><?php _e('Export Configuration', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_export_format"><?php _e('Export Format:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_export_format" name="wpsi_export_format">
                    <option value="csv"><?php _e('CSV', 'wp-smart-insights'); ?></option>
                    <option value="json"><?php _e('JSON', 'wp-smart-insights'); ?></option>
                    <option value="xml"><?php _e('XML', 'wp-smart-insights'); ?></option>
                    <option value="excel"><?php _e('Excel (XLSX)', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_export_date_range"><?php _e('Date Range:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_export_date_range" name="wpsi_export_date_range">
                    <option value="all"><?php _e('All Time', 'wp-smart-insights'); ?></option>
                    <option value="today"><?php _e('Today', 'wp-smart-insights'); ?></option>
                    <option value="yesterday"><?php _e('Yesterday', 'wp-smart-insights'); ?></option>
                    <option value="last_7_days"><?php _e('Last 7 Days', 'wp-smart-insights'); ?></option>
                    <option value="last_30_days"><?php _e('Last 30 Days', 'wp-smart-insights'); ?></option>
                    <option value="last_90_days"><?php _e('Last 90 Days', 'wp-smart-insights'); ?></option>
                    <option value="custom"><?php _e('Custom Range', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group" id="wpsi_custom_date_range" style="display: none;">
                <label><?php _e('Custom Date Range:', 'wp-smart-insights'); ?></label>
                <input type="date" id="wpsi_export_start_date" name="wpsi_export_start_date">
                <span><?php _e('to', 'wp-smart-insights'); ?></span>
                <input type="date" id="wpsi_export_end_date" name="wpsi_export_end_date">
            </div>
        </div>
        
        <!-- Heatmap Data Export -->
        <div class="wpsi-card">
            <h2><?php _e('Heatmap Data Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_heatmap_post_filter"><?php _e('Filter by Post:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_heatmap_post_filter" name="wpsi_heatmap_post_filter">
                    <option value="all"><?php _e('All Posts', 'wp-smart-insights'); ?></option>
                    <?php
                    $posts = get_posts(array(
                        'post_type' => array('post', 'page'),
                        'numberposts' => 100,
                        'orderby' => 'title'
                    ));
                    foreach ($posts as $post) {
                        echo '<option value="' . $post->ID . '">' . esc_html($post->post_title) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_heatmap_data_type"><?php _e('Data Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_heatmap_data_type" name="wpsi_heatmap_data_type">
                    <option value="all"><?php _e('All Data', 'wp-smart-insights'); ?></option>
                    <option value="clicks"><?php _e('Click Data Only', 'wp-smart-insights'); ?></option>
                    <option value="scrolls"><?php _e('Scroll Data Only', 'wp-smart-insights'); ?></option>
                    <option value="hovers"><?php _e('Hover Data Only', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_export_heatmaps" class="button button-primary"><?php _e('Export Heatmap Data', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- User Journey Export -->
        <div class="wpsi-card">
            <h2><?php _e('User Journey Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_journey_post_filter"><?php _e('Filter by Post:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_journey_post_filter" name="wpsi_journey_post_filter">
                    <option value="all"><?php _e('All Posts', 'wp-smart-insights'); ?></option>
                    <?php
                    foreach ($posts as $post) {
                        echo '<option value="' . $post->ID . '">' . esc_html($post->post_title) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_journey_session_filter"><?php _e('Session Filter:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_journey_session_filter" name="wpsi_journey_session_filter">
                    <option value="all"><?php _e('All Sessions', 'wp-smart-insights'); ?></option>
                    <option value="completed"><?php _e('Completed Sessions Only', 'wp-smart-insights'); ?></option>
                    <option value="abandoned"><?php _e('Abandoned Sessions Only', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_export_journeys" class="button button-primary"><?php _e('Export User Journeys', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Analytics Data Export -->
        <div class="wpsi-card">
            <h2><?php _e('Analytics Data Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_analytics_event_type"><?php _e('Event Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_analytics_event_type" name="wpsi_analytics_event_type">
                    <option value="all"><?php _e('All Events', 'wp-smart-insights'); ?></option>
                    <option value="page_view"><?php _e('Page Views', 'wp-smart-insights'); ?></option>
                    <option value="click"><?php _e('Clicks', 'wp-smart-insights'); ?></option>
                    <option value="scroll"><?php _e('Scrolls', 'wp-smart-insights'); ?></option>
                    <option value="form_submit"><?php _e('Form Submissions', 'wp-smart-insights'); ?></option>
                    <option value="conversion"><?php _e('Conversions', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_analytics_group_by"><?php _e('Group By:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_analytics_group_by" name="wpsi_analytics_group_by">
                    <option value="none"><?php _e('No Grouping', 'wp-smart-insights'); ?></option>
                    <option value="date"><?php _e('Date', 'wp-smart-insights'); ?></option>
                    <option value="post"><?php _e('Post', 'wp-smart-insights'); ?></option>
                    <option value="session"><?php _e('Session', 'wp-smart-insights'); ?></option>
                    <option value="event_type"><?php _e('Event Type', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_export_analytics" class="button button-primary"><?php _e('Export Analytics Data', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Content Analysis Export -->
        <div class="wpsi-card">
            <h2><?php _e('Content Analysis Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label for="wpsi_content_post_type"><?php _e('Post Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_content_post_type" name="wpsi_content_post_type">
                    <option value="all"><?php _e('All Content', 'wp-smart-insights'); ?></option>
                    <option value="post"><?php _e('Posts Only', 'wp-smart-insights'); ?></option>
                    <option value="page"><?php _e('Pages Only', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_content_analysis_type"><?php _e('Analysis Type:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_content_analysis_type" name="wpsi_content_analysis_type">
                    <option value="all"><?php _e('All Analysis', 'wp-smart-insights'); ?></option>
                    <option value="readability"><?php _e('Readability Scores', 'wp-smart-insights'); ?></option>
                    <option value="seo"><?php _e('SEO Analysis', 'wp-smart-insights'); ?></option>
                    <option value="engagement"><?php _e('Engagement Metrics', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_export_content_analysis" class="button button-primary"><?php _e('Export Content Analysis', 'wp-smart-insights'); ?></button>
        </div>
        
        <!-- Bulk Export Section -->
        <div class="wpsi-card">
            <h2><?php _e('Bulk Export', 'wp-smart-insights'); ?></h2>
            
            <div class="wpsi-form-group">
                <label><?php _e('Select Data Types to Export:', 'wp-smart-insights'); ?></label>
                <div class="wpsi-checkbox-group">
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="heatmaps" checked> <?php _e('Heatmap Data', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="journeys" checked> <?php _e('User Journeys', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="analytics" checked> <?php _e('Analytics Events', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="content_analysis" checked> <?php _e('Content Analysis', 'wp-smart-insights'); ?></label>
                    <label><input type="checkbox" name="wpsi_bulk_export[]" value="seo_scores" checked> <?php _e('SEO Scores', 'wp-smart-insights'); ?></label>
                </div>
            </div>
            
            <div class="wpsi-form-group">
                <label for="wpsi_bulk_export_format"><?php _e('Export as:', 'wp-smart-insights'); ?></label>
                <select id="wpsi_bulk_export_format" name="wpsi_bulk_export_format">
                    <option value="separate_files"><?php _e('Separate Files', 'wp-smart-insights'); ?></option>
                    <option value="zip_archive"><?php _e('ZIP Archive', 'wp-smart-insights'); ?></option>
                    <option value="combined_file"><?php _e('Combined File', 'wp-smart-insights'); ?></option>
                </select>
            </div>
            
            <button type="button" id="wpsi_bulk_export" class="button button-secondary"><?php _e('Start Bulk Export', 'wp-smart-insights'); ?></button>
            
            <div id="wpsi_bulk_export_progress" class="wpsi-progress" style="display: none;">
                <div class="wpsi-progress-bar">
                    <div class="wpsi-progress-fill"></div>
                </div>
                <div class="wpsi-progress-text">0%</div>
            </div>
        </div>
        
        <!-- Export History -->
        <div class="wpsi-card">
            <h2><?php _e('Export History', 'wp-smart-insights'); ?></h2>
            
            <div id="wpsi_export_history">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'wp-smart-insights'); ?></th>
                            <th><?php _e('Type', 'wp-smart-insights'); ?></th>
                            <th><?php _e('Format', 'wp-smart-insights'); ?></th>
                            <th><?php _e('Records', 'wp-smart-insights'); ?></th>
                            <th><?php _e('Actions', 'wp-smart-insights'); ?></th>
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