/**
 * Main Invoice Module
 * Combines all invoice functionality modules
 */

class InvoiceMain {
    constructor() {
        this.validator = null;
        this.performance = null;
        this.accessibility = null;
        
        this.init();
    }

    // Initialize all modules
    init() {
        // Initialize performance module
        this.performance = new InvoicePerformance();
        
        // Initialize accessibility module
        this.accessibility = new InvoiceAccessibility();
        
        // Initialize validator module
        this.validator = new InvoiceValidator();
        
        // Setup form handlers
        this.setupFormHandlers();
        
        // Setup performance monitoring
        this.setupPerformanceMonitoring();
        
        // Initialize lazy loading
        this.performance.lazyLoadFeatures();
        
        console.log('Invoice modules initialized successfully');
    }

    // Setup form submission handlers
    setupFormHandlers() {
        $('#invoice-form').on('submit', (e) => {
            console.log('Form submission started');
            
            // Log performance stats before submission
            const perfStats = this.performance.getPerformanceStats();
            console.log('Performance Stats:', perfStats);
            
            // Enhanced validation before submission
            if (!this.validator.validateForm()) {
                e.preventDefault();
                console.log('Form validation failed');
                
                // Scroll to first error
                const $firstError = $('.is-invalid').first();
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 500);
                    $firstError.focus();
                }
                
                return false;
            }
            
            console.log('Form validation passed, submitting...');
            $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Invoice...');
            
            // Add timeout protection
            setTimeout(() => {
                if ($('#submit-btn').prop('disabled')) {
                    $('#submit-btn').prop('disabled', false).html('<i class="fas fa-file-invoice"></i> Create Invoice');
                    this.validator.showError('Request timeout. Please try again.');
                }
            }, 30000); // 30 second timeout
        });
    }

    // Setup performance monitoring
    setupPerformanceMonitoring() {
        // Show performance indicator on page load
        setTimeout(() => {
            this.performance.showPerformanceIndicator();
        }, 2000);
        
        // Log performance stats periodically
        setInterval(() => {
            const stats = this.performance.getPerformanceStats();
            if (stats.apiCalls > 0) {
                console.log('Performance Stats:', stats);
            }
        }, 10000); // Every 10 seconds
    }

    // Get all modules
    getModules() {
        return {
            validator: this.validator,
            performance: this.performance,
            accessibility: this.accessibility
        };
    }

    // Cleanup function
    destroy() {
        // Clear caches
        if (this.performance) {
            this.performance.clearCache();
        }
        
        // Remove event listeners
        $(document).off('keydown');
        $('#invoice-form').off('submit');
        
        console.log('Invoice modules destroyed');
    }
}

// Auto-initialize when DOM is ready
$(document).ready(() => {
    window.invoiceMain = new InvoiceMain();
});

// Export for global access
window.InvoiceMain = InvoiceMain;
