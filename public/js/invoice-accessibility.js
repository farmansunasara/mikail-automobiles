/**
 * Invoice Accessibility Module
 * Handles ARIA labels, keyboard navigation, and screen reader support
 */

class InvoiceAccessibility {
    constructor() {
        this.setupKeyboardNavigation();
        this.setupScreenReaderSupport();
        this.setupFocusManagement();
    }

    // Enhanced keyboard shortcuts
    setupKeyboardNavigation() {
        $(document).on('keydown', (e) => {
            // Global shortcuts
            if (e.ctrlKey) {
                switch(e.key) {
                    case 'i':
                    case 'I':
                        e.preventDefault();
                        if (typeof addNewItem === 'function') {
                            addNewItem();
                            this.announceToScreenReader('New item row added');
                        }
                        break;
                    case 'u':
                    case 'U':
                        e.preventDefault();
                        if (typeof showCustomerModal === 'function') {
                            showCustomerModal();
                            this.announceToScreenReader('Customer modal opened');
                        }
                        break;
                    case 's':
                    case 'S':
                        e.preventDefault();
                        $('#invoice-form').submit();
                        this.announceToScreenReader('Form submitted');
                        break;
                    case 'r':
                    case 'R':
                        e.preventDefault();
                        this.resetForm();
                        this.announceToScreenReader('Form reset');
                        break;
                }
            }
            
            // Tab navigation enhancement
            if (e.key === 'Tab') {
                this.handleTabNavigation(e);
            }
            
            // Enter key handling for form elements
            if (e.key === 'Enter' && $(e.target).is('input, select, textarea')) {
                this.handleEnterKey(e);
            }
            
            // Escape key handling
            if (e.key === 'Escape') {
                this.handleEscapeKey(e);
            }
        });
    }

    // Screen reader announcements
    announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            if (document.body.contains(announcement)) {
                document.body.removeChild(announcement);
            }
        }, 1000);
    }

    // Enhanced tab navigation
    handleTabNavigation(e) {
        const $currentElement = $(e.target);
        
        // Skip to next section if at end of current section
        if ($currentElement.is('.section-end')) {
            e.preventDefault();
            const $nextSection = $currentElement.closest('.card').next('.card');
            if ($nextSection.length) {
                $nextSection.find('input, select, textarea').first().focus();
            }
        }
        
        // Skip to previous section if at beginning of current section
        if ($currentElement.is('.section-start')) {
            e.preventDefault();
            const $prevSection = $currentElement.closest('.card').prev('.card');
            if ($prevSection.length) {
                $prevSection.find('input, select, textarea').last().focus();
            }
        }
    }

    // Handle Enter key in form elements
    handleEnterKey(e) {
        const $currentElement = $(e.target);
        
        // If in product row, move to next field
        if ($currentElement.closest('.product-row').length) {
            e.preventDefault();
            const $nextField = $currentElement.closest('td').next('td').find('input, select, textarea');
            if ($nextField.length) {
                $nextField.focus();
            } else {
                // Move to next row or add new row
                const $nextRow = $currentElement.closest('tr').next('tr');
                if ($nextRow.length) {
                    $nextRow.find('input, select, textarea').first().focus();
                } else {
                    if (typeof addNewItem === 'function') {
                        addNewItem();
                        setTimeout(() => {
                            $('.product-row').last().find('input, select, textarea').first().focus();
                        }, 100);
                    }
                }
            }
        }
    }

    // Handle Escape key
    handleEscapeKey(e) {
        const $currentElement = $(e.target);
        
        // Close any open modals
        if ($('.modal').hasClass('show')) {
            $('.modal').modal('hide');
            this.announceToScreenReader('Modal closed');
        }
        
        // Clear current field if in input
        if ($currentElement.is('input, textarea')) {
            $currentElement.val('').trigger('change');
            this.announceToScreenReader('Field cleared');
        }
    }

    // Focus management for modals
    setupFocusManagement() {
        $('.modal').on('shown.bs.modal', function() {
            const $modal = $(this);
            const $firstInput = $modal.find('input, select, textarea').first();
            if ($firstInput.length) {
                $firstInput.focus();
            }
        });
        
        $('.modal').on('hidden.bs.modal', function() {
            const $trigger = $('[data-target="#' + $(this).attr('id') + '"]');
            if ($trigger.length) {
                $trigger.focus();
            }
        });
    }

    // Screen reader support
    setupScreenReaderSupport() {
        // Add ARIA labels to form elements
        this.addAriaLabels();
        
        // Setup live regions for dynamic content
        this.setupLiveRegions();
        
        // Announce form state changes
        this.setupFormStateAnnouncements();
    }

    // Add ARIA labels to form elements
    addAriaLabels() {
        // Customer select
        $('#customer_id').attr({
            'aria-label': 'Select customer for invoice',
            'aria-describedby': 'customer-error customer-help',
            'role': 'combobox'
        });

        // Add customer button
        $('[onclick="showCustomerModal()"]').attr({
            'aria-label': 'Add new customer',
            'title': 'Add new customer'
        });

        // Form inputs
        $('input[name="invoice_date"]').attr({
            'aria-label': 'Invoice date',
            'aria-describedby': 'invoice-date-help'
        });

        $('input[name="due_date"]').attr({
            'aria-label': 'Due date',
            'aria-describedby': 'due-date-help'
        });
    }

    // Setup live regions for dynamic content
    setupLiveRegions() {
        // Add live region for product additions/removals
        if (!$('#product-live-region').length) {
            $('body').append('<div id="product-live-region" aria-live="polite" aria-atomic="true" class="sr-only"></div>');
        }

        // Add live region for validation messages
        if (!$('#validation-live-region').length) {
            $('body').append('<div id="validation-live-region" aria-live="assertive" aria-atomic="true" class="sr-only"></div>');
        }
    }

    // Setup form state announcements
    setupFormStateAnnouncements() {
        // Announce when products are added
        $(document).on('click', '#add-item-btn, #add-first-item', () => {
            this.announceToScreenReader('New product row added');
        });

        // Announce when products are removed
        $(document).on('click', '.remove-item', () => {
            this.announceToScreenReader('Product row removed');
        });

        // Announce validation errors
        $(document).on('change', '.is-invalid', (e) => {
            const errorMessage = $(e.target).siblings('.invalid-feedback').text();
            if (errorMessage) {
                this.announceToScreenReader(`Error: ${errorMessage}`);
            }
        });
    }

    // Form reset function
    resetForm() {
        if (confirm('Are you sure you want to reset the form? All data will be lost.')) {
            $('#invoice-form')[0].reset();
            $('.product-row').not(':first').remove();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').hide();
            
            // Clear product tracking if available
            if (window.addedProducts) {
                window.addedProducts.clear();
            }
            if (typeof updateProductTracking === 'function') {
                updateProductTracking();
            }
            
            this.announceToScreenReader('Form reset successfully');
        }
    }

    // Add skip links for keyboard navigation
    addSkipLinks() {
        const skipLinks = `
            <div class="skip-links">
                <a href="#customer-section" class="skip-link">Skip to customer section</a>
                <a href="#items-section" class="skip-link">Skip to items section</a>
                <a href="#totals-section" class="skip-link">Skip to totals section</a>
            </div>
        `;
        
        $('body').prepend(skipLinks);
    }

    // Setup keyboard shortcuts help
    setupKeyboardHelp() {
        const shortcuts = {
            'Ctrl+I': 'Add new item',
            'Ctrl+U': 'Add new customer',
            'Ctrl+S': 'Submit form',
            'Ctrl+R': 'Reset form',
            'Tab': 'Navigate to next field',
            'Shift+Tab': 'Navigate to previous field',
            'Enter': 'Move to next field in product row',
            'Escape': 'Close modal or clear field'
        };

        // Add keyboard shortcuts help modal
        const helpModal = `
            <div class="modal fade" id="keyboardHelpModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Keyboard Shortcuts</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                ${Object.entries(shortcuts).map(([key, description]) => `
                                    <div class="col-md-6 mb-2">
                                        <kbd>${key}</kbd> - ${description}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(helpModal);
    }
}

// Export for use in other modules
window.InvoiceAccessibility = InvoiceAccessibility;
