/**
 * WP Smart Insights - Admin JavaScript
 * Handles admin interface interactions
 */

jQuery(document).ready(function($) {
    
    // Dashboard functionality
    if ($('#wpsi-dashboard').length) {
        initDashboard();
    }
    
    // Heatmaps functionality
    if ($('#wpsi-heatmaps').length) {
        initHeatmaps();
    }
    
    // Content Analysis functionality
    if ($('#wpsi-content-analysis').length) {
        initContentAnalysis();
    }
    
    // Settings functionality
    if ($('#wpsi-settings').length) {
        initSettings();
    }
    
    // SEO functionality
    if ($('#wpsi-seo').length) {
        initSEO();
    }
    
    // User Journeys functionality
    if ($('#wpsi-journeys').length) {
        initUserJourneys();
    }
    
    // Initialize Dashboard
    function initDashboard() {
        // Load dashboard stats
        loadDashboardStats();
        
        // Quick actions
        $('.wpsi-quick-action').on('click', function() {
            var action = $(this).data('action');
            handleQuickAction(action);
        });
        
        // System status checks
        checkSystemStatus();
    }
    
    // Initialize Heatmaps
    function initHeatmaps() {
        // Post selector change
        $('#wpsi-heatmap-post-selector').on('change', function() {
            var postId = $(this).val();
            if (postId) {
                loadHeatmapData(postId);
            }
        });
        
        // Heatmap type toggle
        $('.wpsi-heatmap-type').on('click', function() {
            var type = $(this).data('type');
            showHeatmapType(type);
        });
        
        // Export heatmap data
        $('#wpsi-export-heatmap').on('click', function() {
            exportHeatmapData();
        });
    }
    
    // Initialize Content Analysis
    function initContentAnalysis() {
        // Analyze content button
        $('#wpsi-analyze-content-btn').on('click', function() {
            var postId = $('#wpsi-content-post-selector').val();
            if (postId) {
                analyzeContent(postId);
            }
        });
        
        // Re-analyze button
        $('.wpsi-re-analyze').on('click', function() {
            var postId = $(this).data('post-id');
            analyzeContent(postId);
        });
    }
    
    // Initialize Settings
    function initSettings() {
        // Save settings
        $('#wpsi-save-settings').on('click', function() {
            saveSettings();
        });
        
        // Test AI connection
        $('#wpsi-test-ai').on('click', function() {
            testAIConnection();
        });
        
        // Privacy compliance toggle
        $('#wpsi-privacy-compliant').on('change', function() {
            togglePrivacySettings();
        });
    }
    
    // Initialize SEO
    function initSEO() {
        // SEO analysis
        $('#wpsi-analyze-seo').on('click', function() {
            var postId = $('#wpsi-post-selector').val();
            if (postId) {
                analyzeSEO(postId);
            }
        });
    }
    
    // Initialize User Journeys
    function initUserJourneys() {
        // Load journeys
        $('#wpsi-load-journeys').on('click', function() {
            loadUserJourneys();
        });
        
        // Play journey
        $(document).on('click', '.wpsi-play-journey', function() {
            var journeyId = $(this).data('journey-id');
            playUserJourney(journeyId);
        });
    }
    
    // Dashboard Functions
    function loadDashboardStats() {
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_get_dashboard_stats',
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateDashboardStats(response.data);
                }
            }
        });
    }
    
    function updateDashboardStats(stats) {
        $('#wpsi-total-posts').text(stats.total_posts);
        $('#wpsi-analyzed-posts').text(stats.analyzed_posts);
        $('#wpsi-heatmap-data').text(stats.heatmap_data);
        $('#wpsi-user-journeys').text(stats.user_journeys);
        $('#wpsi-avg-content-score').text(stats.avg_content_score);
        $('#wpsi-avg-seo-score').text(stats.avg_seo_score);
    }
    
    function handleQuickAction(action) {
        switch(action) {
            case 'analyze_all':
                analyzeAllContent();
                break;
            case 'export_data':
                exportAllData();
                break;
            case 'privacy_check':
                runPrivacyCheck();
                break;
        }
    }
    
    function checkSystemStatus() {
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_check_system_status',
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateSystemStatus(response.data);
                }
            }
        });
    }
    
    function updateSystemStatus(status) {
        var statusHtml = '';
        status.checks.forEach(function(check) {
            var statusClass = check.status ? 'success' : 'error';
            var statusIcon = check.status ? '✓' : '✗';
            statusHtml += '<div class="wpsi-status-item ' + statusClass + '">';
            statusHtml += '<span class="wpsi-status-icon">' + statusIcon + '</span>';
            statusHtml += '<span class="wpsi-status-text">' + check.message + '</span>';
            statusHtml += '</div>';
        });
        $('#wpsi-system-status').html(statusHtml);
    }
    
    // Heatmap Functions
    function loadHeatmapData(postId) {
        $('#wpsi-heatmap-loading').show();
        $('#wpsi-heatmap-results').hide();
        
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_get_heatmap_data',
                post_id: postId,
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                $('#wpsi-heatmap-loading').hide();
                if (response.success) {
                    displayHeatmapData(response.data);
                    $('#wpsi-heatmap-results').show();
                }
            }
        });
    }
    
    function displayHeatmapData(data) {
        // Update statistics
        $('#wpsi-total-visitors').text(data.total_visitors);
        $('#wpsi-avg-time').text(data.avg_time);
        $('#wpsi-bounce-rate').text(data.bounce_rate);
        
        // Update UX warnings
        var warningsHtml = '';
        data.ux_warnings.forEach(function(warning) {
            warningsHtml += '<div class="wpsi-warning-item ' + warning.severity + '">';
            warningsHtml += '<strong>' + warning.title + '</strong><br>';
            warningsHtml += warning.description;
            warningsHtml += '</div>';
        });
        $('#wpsi-ux-warnings').html(warningsHtml);
        
        // Draw heatmap
        drawHeatmap(data.heatmap_data);
    }
    
    function drawHeatmap(heatmapData) {
        var canvas = document.getElementById('wpsi-heatmap-canvas');
        var ctx = canvas.getContext('2d');
        
        // Clear canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw heatmap based on data
        heatmapData.forEach(function(point) {
            var intensity = point.intensity / 100;
            var gradient = ctx.createRadialGradient(point.x, point.y, 0, point.x, point.y, 50);
            gradient.addColorStop(0, 'rgba(255, 0, 0, ' + intensity + ')');
            gradient.addColorStop(1, 'rgba(255, 0, 0, 0)');
            
            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.arc(point.x, point.y, 50, 0, 2 * Math.PI);
            ctx.fill();
        });
    }
    
    function showHeatmapType(type) {
        $('.wpsi-heatmap-type').removeClass('active');
        $('[data-type="' + type + '"]').addClass('active');
        
        // Reload heatmap data for selected type
        var postId = $('#wpsi-heatmap-post-selector').val();
        if (postId) {
            loadHeatmapData(postId);
        }
    }
    
    function exportHeatmapData() {
        var postId = $('#wpsi-heatmap-post-selector').val();
        if (!postId) {
            alert('Please select a post first.');
            return;
        }
        
        window.location.href = wpsi_ajax.ajax_url + '?action=wpsi_export_heatmap&post_id=' + postId + '&nonce=' + wpsi_ajax.nonce;
    }
    
    // Content Analysis Functions
    function analyzeContent(postId) {
        $('#wpsi-content-loading').show();
        $('#wpsi-content-results').hide();
        
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_analyze_content',
                post_id: postId,
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                $('#wpsi-content-loading').hide();
                if (response.success) {
                    displayContentAnalysis(response.data);
                    $('#wpsi-content-results').show();
                }
            }
        });
    }
    
    function displayContentAnalysis(data) {
        // Update scores
        $('#wpsi-readability-score').text(data.readability_score);
        $('#wpsi-sentiment-score').text(data.sentiment_score);
        $('#wpsi-tone-score').text(data.tone_score);
        $('#wpsi-keyword-score').text(data.keyword_score);
        $('#wpsi-repetition-score').text(data.repetition_score);
        
        // Update recommendations
        var recommendationsHtml = '';
        data.recommendations.forEach(function(rec) {
            recommendationsHtml += '<div class="wpsi-recommendation-item ' + rec.priority + '">';
            recommendationsHtml += '<strong>' + rec.title + '</strong><br>';
            recommendationsHtml += rec.description;
            recommendationsHtml += '</div>';
        });
        $('#wpsi-recommendations').html(recommendationsHtml);
    }
    
    // Settings Functions
    function saveSettings() {
        var settings = {
            tracking_enabled: $('#wpsi-tracking-enabled').is(':checked'),
            ai_api_key: $('#wpsi-ai-api-key').val(),
            privacy_compliant: $('#wpsi-privacy-compliant').is(':checked'),
            cookie_consent: $('#wpsi-cookie-consent').is(':checked'),
            data_retention: $('#wpsi-data-retention').val(),
            heatmap_enabled: $('#wpsi-heatmap-enabled').is(':checked'),
            journey_enabled: $('#wpsi-journey-enabled').is(':checked')
        };
        
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_save_settings',
                settings: settings,
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage('Settings saved successfully!', 'success');
                } else {
                    showMessage('Error saving settings.', 'error');
                }
            }
        });
    }
    
    function testAIConnection() {
        var apiKey = $('#wpsi-ai-api-key').val();
        if (!apiKey) {
            showMessage('Please enter an AI API key first.', 'error');
            return;
        }
        
        $('#wpsi-test-ai').text('Testing...').prop('disabled', true);
        
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_test_ai_connection',
                api_key: apiKey,
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                $('#wpsi-test-ai').text('Test Connection').prop('disabled', false);
                if (response.success) {
                    showMessage('AI connection successful!', 'success');
                } else {
                    showMessage('AI connection failed: ' + response.data, 'error');
                }
            }
        });
    }
    
    function togglePrivacySettings() {
        var isCompliant = $('#wpsi-privacy-compliant').is(':checked');
        $('.wpsi-privacy-setting').prop('disabled', !isCompliant);
    }
    
    // SEO Functions
    function analyzeSEO(postId) {
        $('#wpsi-seo-loading').show();
        $('#wpsi-seo-results').hide();
        
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
                }
            }
        });
    }
    
    function displaySEOResults(data) {
        // Update scores
        $('#wpsi-overall-score').text(data.overall_score);
        $('#wpsi-headings-score').text(data.headings.score + '/100');
        $('#wpsi-meta-score').text(data.meta.score + '/100');
        $('#wpsi-links-score').text(data.links.score + '/100');
        $('#wpsi-images-score').text(data.images.score + '/100');
        
        // Update details
        $('#wpsi-headings-details').html(data.headings.details);
        $('#wpsi-meta-details').html(data.meta.details);
        $('#wpsi-links-details').html(data.links.details);
        $('#wpsi-images-details').html(data.images.details);
        
        // Update fixes
        var fixesHtml = '';
        data.fixes.forEach(function(fix) {
            fixesHtml += '<div class="wpsi-fix-item ' + fix.type + '">';
            fixesHtml += '<strong>' + fix.title + '</strong><br>';
            fixesHtml += fix.description;
            fixesHtml += '</div>';
        });
        $('#wpsi-fixes-list').html(fixesHtml);
    }
    
    // User Journey Functions
    function loadUserJourneys() {
        var postId = $('#wpsi-post-filter').val();
        var dateFilter = $('#wpsi-date-filter').val();
        
        $('#wpsi-journeys-loading').show();
        
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_get_user_journeys',
                post_id: postId,
                date_filter: dateFilter,
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                $('#wpsi-journeys-loading').hide();
                if (response.success) {
                    displayJourneys(response.data);
                }
            }
        });
    }
    
    function displayJourneys(journeys) {
        var tbody = $('#wpsi-journeys-tbody');
        tbody.empty();
        
        if (journeys.length === 0) {
            tbody.append('<tr><td colspan="5">No journeys found for the selected criteria.</td></tr>');
            return;
        }
        
        journeys.forEach(function(journey) {
            var row = '<tr>';
            row += '<td>' + journey.timestamp + '</td>';
            row += '<td>' + journey.post_title + '</td>';
            row += '<td>' + journey.duration + '</td>';
            row += '<td><button class="button wpsi-play-journey" data-journey-id="' + journey.id + '">Play</button></td>';
            row += '<td>' + journey.interaction_count + '</td>';
            row += '</tr>';
            tbody.append(row);
        });
    }
    
    function playUserJourney(journeyId) {
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_get_journey_details',
                journey_id: journeyId,
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    setupJourneyPlayback(response.data);
                    $('#wpsi-journey-playback').show();
                }
            }
        });
    }
    
    function setupJourneyPlayback(journey) {
        // Setup playback controls and display journey data
        $('#wpsi-page-content').html(journey.page_content);
        
        var interactionHtml = '';
        journey.interactions.forEach(function(interaction) {
            interactionHtml += '<div class="wpsi-interaction-item ' + interaction.type + '">';
            interactionHtml += '<strong>' + interaction.type.toUpperCase() + '</strong> at ' + formatTime(interaction.timestamp);
            interactionHtml += '<br>' + interaction.description;
            interactionHtml += '</div>';
        });
        $('#wpsi-interaction-list').html(interactionHtml);
        
        // Setup statistics
        $('#wpsi-total-clicks').text(journey.stats.clicks);
        $('#wpsi-total-scrolls').text(journey.stats.scrolls);
        $('#wpsi-total-forms').text(journey.stats.forms);
        $('#wpsi-time-on-page').text(journey.stats.duration + 's');
    }
    
    // Utility Functions
    function showMessage(message, type) {
        var alertClass = type === 'success' ? 'notice-success' : 'notice-error';
        var alertHtml = '<div class="notice ' + alertClass + ' is-dismissible"><p>' + message + '</p></div>';
        $('.wrap h1').after(alertHtml);
        
        setTimeout(function() {
            $('.notice').fadeOut();
        }, 3000);
    }
    
    function formatTime(seconds) {
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        return mins.toString().padStart(2, '0') + ':' + secs.toString().padStart(2, '0');
    }
    
    // Quick action handlers
    function analyzeAllContent() {
        if (confirm('This will analyze all published posts. Continue?')) {
            $.ajax({
                url: wpsi_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpsi_analyze_all_content',
                    nonce: wpsi_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('Content analysis completed!', 'success');
                        loadDashboardStats();
                    }
                }
            });
        }
    }
    
    function exportAllData() {
        window.location.href = wpsi_ajax.ajax_url + '?action=wpsi_export_all_data&nonce=' + wpsi_ajax.nonce;
    }
    
    function runPrivacyCheck() {
        $.ajax({
            url: wpsi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsi_privacy_check',
                nonce: wpsi_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage('Privacy check completed!', 'success');
                    checkSystemStatus();
                }
            }
        });
    }
}); 