/**
 * WP Smart Insights - Frontend JavaScript
 * Handles user interaction tracking for heatmaps and user journeys
 */

(function() {
    'use strict';
    
    // Check if tracking is enabled and user has consented
    if (!window.wpsi_frontend || !window.wpsi_frontend.tracking_enabled) {
        return;
    }
    
    // Initialize tracking
    var WPSITracker = {
        sessionId: generateSessionId(),
        startTime: Date.now(),
        interactions: [],
        heatmapData: {
            clicks: [],
            scrolls: [],
            hovers: []
        },
        isTracking: false,
        consentGiven: false,
        
        init: function() {
            this.checkConsent();
            this.setupEventListeners();
            this.startTracking();
        },
        
        checkConsent: function() {
            // Check if user has given consent
            var consent = localStorage.getItem('wpsi_consent');
            if (consent === 'true') {
                this.consentGiven = true;
            } else {
                this.showConsentBanner();
            }
        },
        
        showConsentBanner: function() {
            var banner = document.createElement('div');
            banner.id = 'wpsi-consent-banner';
            banner.innerHTML = `
                <div class="wpsi-consent-content">
                    <p>This website uses cookies and tracking to improve user experience. 
                    We respect your privacy and only collect anonymous data.</p>
                    <div class="wpsi-consent-buttons">
                        <button id="wpsi-accept-tracking" class="wpsi-btn wpsi-btn-primary">Accept</button>
                        <button id="wpsi-decline-tracking" class="wpsi-btn wpsi-btn-secondary">Decline</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(banner);
            
            // Add event listeners
            document.getElementById('wpsi-accept-tracking').addEventListener('click', function() {
                WPSITracker.acceptConsent();
            });
            
            document.getElementById('wpsi-decline-tracking').addEventListener('click', function() {
                WPSITracker.declineConsent();
            });
        },
        
        acceptConsent: function() {
            this.consentGiven = true;
            localStorage.setItem('wpsi_consent', 'true');
            this.hideConsentBanner();
            this.startTracking();
        },
        
        declineConsent: function() {
            this.consentGiven = false;
            localStorage.setItem('wpsi_consent', 'false');
            this.hideConsentBanner();
        },
        
        hideConsentBanner: function() {
            var banner = document.getElementById('wpsi-consent-banner');
            if (banner) {
                banner.remove();
            }
        },
        
        setupEventListeners: function() {
            // Click tracking
            document.addEventListener('click', this.handleClick.bind(this), true);
            
            // Scroll tracking
            var scrollTimeout;
            document.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(function() {
                    WPSITracker.handleScroll();
                }, 100);
            }, { passive: true });
            
            // Hover tracking
            document.addEventListener('mouseover', this.handleHover.bind(this), true);
            
            // Form interactions
            document.addEventListener('focus', this.handleFormInteraction.bind(this), true);
            document.addEventListener('input', this.handleFormInteraction.bind(this), true);
            document.addEventListener('submit', this.handleFormInteraction.bind(this), true);
            
            // Page visibility
            document.addEventListener('visibilitychange', this.handleVisibilityChange.bind(this));
            
            // Before unload
            window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
        },
        
        handleClick: function(event) {
            if (!this.consentGiven || !this.isTracking) return;
            
            var element = event.target;
            var rect = element.getBoundingClientRect();
            
            var clickData = {
                x: event.clientX,
                y: event.clientY,
                element: this.getElementInfo(element),
                timestamp: Date.now() - this.startTime,
                pageX: event.pageX,
                pageY: event.pageY
            };
            
            this.heatmapData.clicks.push(clickData);
            this.interactions.push({
                type: 'click',
                data: clickData,
                timestamp: Date.now() - this.startTime
            });
        },
        
        handleScroll: function() {
            if (!this.consentGiven || !this.isTracking) return;
            
            var scrollData = {
                scrollX: window.pageXOffset,
                scrollY: window.pageYOffset,
                scrollTop: document.documentElement.scrollTop || document.body.scrollTop,
                scrollHeight: document.documentElement.scrollHeight,
                clientHeight: document.documentElement.clientHeight,
                timestamp: Date.now() - this.startTime
            };
            
            this.heatmapData.scrolls.push(scrollData);
            this.interactions.push({
                type: 'scroll',
                data: scrollData,
                timestamp: Date.now() - this.startTime
            });
        },
        
        handleHover: function(event) {
            if (!this.consentGiven || !this.isTracking) return;
            
            var element = event.target;
            var rect = element.getBoundingClientRect();
            
            var hoverData = {
                x: event.clientX,
                y: event.clientY,
                element: this.getElementInfo(element),
                timestamp: Date.now() - this.startTime
            };
            
            this.heatmapData.hovers.push(hoverData);
        },
        
        handleFormInteraction: function(event) {
            if (!this.consentGiven || !this.isTracking) return;
            
            var element = event.target;
            var formData = {
                type: event.type,
                element: this.getElementInfo(element),
                timestamp: Date.now() - this.startTime
            };
            
            this.interactions.push({
                type: 'form',
                data: formData,
                timestamp: Date.now() - this.startTime
            });
        },
        
        handleVisibilityChange: function() {
            if (!this.consentGiven) return;
            
            if (document.hidden) {
                this.pauseTracking();
            } else {
                this.resumeTracking();
            }
        },
        
        handleBeforeUnload: function() {
            if (!this.consentGiven) return;
            
            this.stopTracking();
            this.sendData();
        },
        
        getElementInfo: function(element) {
            return {
                tagName: element.tagName.toLowerCase(),
                className: element.className,
                id: element.id,
                text: element.textContent ? element.textContent.substring(0, 50) : '',
                href: element.href || '',
                type: element.type || ''
            };
        },
        
        startTracking: function() {
            if (!this.consentGiven) return;
            
            this.isTracking = true;
            this.startTime = Date.now();
            
            // Send initial data
            this.sendData();
        },
        
        pauseTracking: function() {
            this.isTracking = false;
        },
        
        resumeTracking: function() {
            this.isTracking = true;
        },
        
        stopTracking: function() {
            this.isTracking = false;
        },
        
        sendData: function() {
            if (!this.consentGiven || this.interactions.length === 0) return;
            
            var data = {
                session_id: this.sessionId,
                post_id: window.wpsi_frontend.post_id,
                page_url: window.wpsi_frontend.page_url,
                interactions: this.interactions,
                heatmap_data: this.heatmapData,
                duration: Date.now() - this.startTime,
                timestamp: new Date().toISOString()
            };
            
            // Send via AJAX
            this.sendAjaxData(data);
            
            // Clear data after sending
            this.interactions = [];
            this.heatmapData = {
                clicks: [],
                scrolls: [],
                hovers: []
            };
        },
        
        sendAjaxData: function(data) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', window.wpsi_frontend.ajax_url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        console.log('WPSI: Data sent successfully');
                    } else {
                        console.error('WPSI: Error sending data');
                    }
                }
            };
            
            var formData = new FormData();
            formData.append('action', 'wpsi_save_user_journey');
            formData.append('nonce', window.wpsi_frontend.nonce);
            formData.append('journey_data', JSON.stringify(data));
            
            xhr.send(formData);
        },
        
        // Send data periodically
        sendPeriodicData: function() {
            if (this.consentGiven && this.isTracking) {
                this.sendData();
            }
        }
    };
    
    // Helper function to generate session ID
    function generateSessionId() {
        return 'wpsi_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            WPSITracker.init();
        });
    } else {
        WPSITracker.init();
    }
    
    // Send data every 30 seconds
    setInterval(function() {
        WPSITracker.sendPeriodicData();
    }, 30000);
    
    // Send data when page becomes hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            WPSITracker.sendData();
        }
    });
    
})();

// Add CSS for consent banner
(function() {
    var style = document.createElement('style');
    style.textContent = `
        #wpsi-consent-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 15px;
            z-index: 999999;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        .wpsi-consent-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .wpsi-consent-content p {
            margin: 0;
            flex: 1;
            min-width: 300px;
        }
        
        .wpsi-consent-buttons {
            display: flex;
            gap: 10px;
        }
        
        .wpsi-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .wpsi-btn-primary {
            background: #007cba;
            color: white;
        }
        
        .wpsi-btn-primary:hover {
            background: #005a87;
        }
        
        .wpsi-btn-secondary {
            background: transparent;
            color: white;
            border: 1px solid white;
        }
        
        .wpsi-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        @media (max-width: 768px) {
            .wpsi-consent-content {
                flex-direction: column;
                text-align: center;
            }
            
            .wpsi-consent-content p {
                min-width: auto;
            }
        }
    `;
    document.head.appendChild(style);
})(); 