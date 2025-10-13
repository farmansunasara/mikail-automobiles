/**
 * Invoice Performance Module
 * Handles caching, skeleton loaders, and performance monitoring
 */

class InvoicePerformance {
    constructor() {
        this.dataCache = new Map();
        this.CACHE_TTL = 5 * 60 * 1000; // 5 minutes
        this.performanceMonitor = {
            startTime: Date.now(),
            apiCalls: 0,
            cacheHits: 0,
            
            recordApiCall() {
                this.apiCalls++;
            },
            
            recordCacheHit() {
                this.cacheHits++;
            },
            
            getStats() {
                return {
                    loadTime: Date.now() - this.startTime,
                    apiCalls: this.apiCalls,
                    cacheHits: this.cacheHits,
                    cacheHitRate: this.apiCalls > 0 ? (this.cacheHits / this.apiCalls * 100).toFixed(2) + '%' : '0%'
                };
            }
        };
    }

    // Check if cache is valid
    isCacheValid(key) {
        const cached = this.dataCache.get(key);
        if (!cached) return false;
        return Date.now() - cached.timestamp < this.CACHE_TTL;
    }

    // Cached API call
    cachedApiCall(url, cacheKey) {
        if (this.isCacheValid(cacheKey)) {
            console.log(`Cache hit for ${cacheKey}`);
            this.performanceMonitor.recordCacheHit();
            return Promise.resolve(this.dataCache.get(cacheKey).data);
        }
        
        console.log(`Cache miss for ${cacheKey}, fetching from API`);
        this.performanceMonitor.recordApiCall();
        
        return fetch(url)
            .then(response => response.json())
            .then(data => {
                this.dataCache.set(cacheKey, {
                    data: data,
                    timestamp: Date.now()
                });
                return data;
            });
    }

    // Skeleton loader
    showSkeletonLoader($container, type = 'default') {
        const skeletonHtml = {
            default: '<div class="skeleton-loader"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>',
            table: '<tr><td><div class="skeleton-loader"><div class="skeleton-line"></div></div></td><td><div class="skeleton-loader"><div class="skeleton-line"></div></div></td></tr>',
            select: '<div class="skeleton-loader"><div class="skeleton-line"></div></div>'
        };
        
        $container.html(skeletonHtml[type] || skeletonHtml.default);
    }

    // Lazy loading for non-critical features
    lazyLoadFeatures() {
        const lazyFeatures = {
            'advancedValidation': () => {
                // Load advanced validation features
                console.log('Loading advanced validation features...');
            },
            'keyboardShortcuts': () => {
                // Load keyboard shortcuts
                console.log('Loading keyboard shortcuts...');
            },
            'mobileOptimizations': () => {
                // Load mobile optimizations
                console.log('Loading mobile optimizations...');
            }
        };

        // Initialize lazy features on demand
        window.initializeLazyFeature = function(featureName) {
            if (lazyFeatures[featureName]) {
                lazyFeatures[featureName]();
            }
        };
    }

    // Performance monitoring
    getPerformanceStats() {
        return this.performanceMonitor.getStats();
    }

    // Show performance indicator
    showPerformanceIndicator() {
        const stats = this.getPerformanceStats();
        const indicator = $(`
            <div class="performance-indicator">
                Load: ${stats.loadTime}ms | 
                API: ${stats.apiCalls} | 
                Cache: ${stats.cacheHitRate}
            </div>
        `);
        
        $('body').append(indicator);
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            indicator.fadeOut();
        }, 3000);
    }

    // Clear cache
    clearCache() {
        this.dataCache.clear();
        console.log('Cache cleared');
    }

    // Cache statistics
    getCacheStats() {
        return {
            size: this.dataCache.size,
            keys: Array.from(this.dataCache.keys())
        };
    }
}

// Export for use in other modules
window.InvoicePerformance = InvoicePerformance;
