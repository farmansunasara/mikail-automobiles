/**
 * Invoice Form Validation Module
 * Handles all validation logic for invoice forms
 */

class InvoiceValidator {
    constructor() {
        this.addedProducts = new Set();
        this.errorCount = 0;
    }

    // Enhanced validation with real-time feedback
    validateForm() {
        let isValid = true;
        this.errorCount = 0;
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
        
        // Customer validation
        if (!$('#customer_id').val()) {
            $('#customer_id').addClass('is-invalid');
            $('#customer-error').text('Please select a customer').show();
            isValid = false;
            this.errorCount++;
        } else {
            $('#customer_id').removeClass('is-invalid');
            $('#customer-error').hide();
        }
        
        // Date validation
        const invoiceDate = $('input[name="invoice_date"]').val();
        const dueDate = $('input[name="due_date"]').val();
        
        if (!invoiceDate) {
            $('input[name="invoice_date"]').addClass('is-invalid');
            this.showError('Invoice date is required');
            isValid = false;
            this.errorCount++;
        }
        
        if (dueDate && new Date(dueDate) < new Date(invoiceDate)) {
            $('input[name="due_date"]').addClass('is-invalid');
            this.showError('Due date cannot be before invoice date');
            isValid = false;
            this.errorCount++;
        }
        
        // Items validation
        let hasValidItems = false;
        let totalItems = 0;
        let validItems = 0;
        
        $('.product-row').each(function() {
            const $row = $(this);
            const categoryId = $row.find('.category-select').val();
            const productId = $row.find('.product-select').val();
            const price = parseFloat($row.find('.price-input').val()) || 0;
            let hasQuantity = false;
            
            // Count total items
            totalItems++;
            
            // Check quantities
            $row.find('.quantity-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                if (qty > 0) {
                    hasQuantity = true;
                }
            });
            
            // Category validation
            if (!categoryId) {
                $row.find('.category-select').addClass('is-invalid');
                $row.find('.category-select').siblings('.invalid-feedback').text('Please select a category').show();
                isValid = false;
                this.errorCount++;
            } else {
                $row.find('.category-select').removeClass('is-invalid');
                $row.find('.category-select').siblings('.invalid-feedback').hide();
            }
            
            // Product validation
            if (!productId && categoryId) {
                $row.find('.product-select').addClass('is-invalid');
                $row.find('.product-select').siblings('.invalid-feedback').text('Please select a product').show();
                isValid = false;
                this.errorCount++;
            } else if (productId && categoryId) {
                // Check for duplicate products
                if (this.checkDuplicateProduct(productId, $row)) {
                    $row.find('.product-select').addClass('is-invalid');
                    $row.find('.product-select').siblings('.invalid-feedback').text('This product is already added to the invoice').show();
                    isValid = false;
                    this.errorCount++;
                } else {
                    $row.find('.product-select').removeClass('is-invalid');
                    $row.find('.product-select').siblings('.invalid-feedback').hide();
                }
            }
            
            // Price validation
            if (price <= 0 && productId) {
                $row.find('.price-input').addClass('is-invalid');
                $row.find('.price-input').siblings('.invalid-feedback').show().text('Price must be greater than zero');
                isValid = false;
                this.errorCount++;
            } else if (price > 0) {
                $row.find('.price-input').removeClass('is-invalid');
                $row.find('.price-input').siblings('.invalid-feedback').hide();
            }
            
            // Quantity validation
            if (productId && price > 0 && !hasQuantity) {
                $row.find('.quantity-input').first().addClass('is-invalid');
                $row.find('.quantity-input').first().siblings('.invalid-feedback').text('Please enter quantity').show();
                isValid = false;
                this.errorCount++;
            } else {
                $row.find('.quantity-input').removeClass('is-invalid');
                $row.find('.quantity-input').siblings('.invalid-feedback').hide();
            }
            
            // Stock validation
            if (productId && hasQuantity) {
                $row.find('.quantity-input').each(function() {
                    const qty = parseInt($(this).val()) || 0;
                    const stock = parseInt($(this).data('stock')) || 0;
                    if (qty > stock && stock > 0) {
                        $(this).addClass('is-invalid');
                        $(this).siblings('.invalid-feedback').text(`Only ${stock} items available in stock`).show();
                        isValid = false;
                        this.errorCount++;
                    }
                });
            }
            
            // Valid item check
            if (hasQuantity && categoryId && productId && price > 0) {
                hasValidItems = true;
                validItems++;
            }
        });
        
        // Items summary validation
        if (totalItems === 0) {
            this.showError('Please add at least one item to the invoice');
            isValid = false;
            this.errorCount++;
        } else if (!hasValidItems) {
            this.showError(`Please complete ${totalItems - validItems} incomplete item(s) with valid quantity`);
            isValid = false;
            this.errorCount++;
        }
        
        // Show validation summary
        if (!isValid && this.errorCount > 0) {
            this.showError(`Please fix ${this.errorCount} error(s) before submitting`);
        }
        
        return isValid;
    }

    // Check for duplicate products
    checkDuplicateProduct(productId, currentRow) {
        if (!productId) return false;
        
        let isDuplicate = false;
        $('.product-row').each(function() {
            const $row = $(this);
            const rowProductId = $row.find('.product-select').val();
            
            // Skip current row and check others
            if ($row[0] !== currentRow[0] && rowProductId === productId) {
                isDuplicate = true;
                return false; // Break the loop
            }
        });
        
        return isDuplicate;
    }

    // Update product tracking when rows are removed
    updateProductTracking() {
        this.addedProducts.clear();
        $('.product-row').each(function() {
            const productId = $(this).find('.product-select').val();
            if (productId) {
                this.addedProducts.add(productId);
            }
        });
    }

    // Real-time validation setup
    setupRealTimeValidation() {
        // Customer validation
        $('#customer_id').on('change', function() {
            if ($(this).val()) {
                $(this).removeClass('is-invalid');
                $('#customer-error').hide();
            } else {
                $(this).addClass('is-invalid');
                $('#customer-error').text('Please select a customer').show();
            }
        });
        
        // Date validation
        $('input[name="invoice_date"], input[name="due_date"]').on('change', function() {
            const invoiceDate = $('input[name="invoice_date"]').val();
            const dueDate = $('input[name="due_date"]').val();
            
            if (dueDate && invoiceDate && new Date(dueDate) < new Date(invoiceDate)) {
                $('input[name="due_date"]').addClass('is-invalid');
                this.showError('Due date cannot be before invoice date');
            } else {
                $('input[name="due_date"]').removeClass('is-invalid');
            }
        });
        
        // Product validation with duplicate check
        $(document).on('change', '.category-select, .product-select', function() {
            const $row = $(this).closest('tr');
            const categoryId = $row.find('.category-select').val();
            const productId = $row.find('.product-select').val();
            
            if (categoryId) {
                $row.find('.category-select').removeClass('is-invalid');
                $row.find('.category-select').siblings('.invalid-feedback').hide();
            }
            
            if (productId && categoryId) {
                // Check for duplicate products
                if (this.checkDuplicateProduct(productId, $row)) {
                    $row.find('.product-select').addClass('is-invalid');
                    $row.find('.product-select').siblings('.invalid-feedback').text('This product is already added to the invoice').show();
                    this.showError('Product already exists in the invoice. Please select a different product.', 'warning');
                } else {
                    $row.find('.product-select').removeClass('is-invalid');
                    $row.find('.product-select').siblings('.invalid-feedback').hide();
                    // Add to tracking set
                    this.addedProducts.add(productId);
                }
            }
        });
        
        // Price validation
        $(document).on('input change', '.price-input', function() {
            const price = parseFloat($(this).val()) || 0;
            const $row = $(this).closest('tr');
            const productId = $row.find('.product-select').val();
            
            if (productId) {
                if (price <= 0) {
                    $(this).addClass('is-invalid');
                    $(this).siblings('.invalid-feedback').text('Price must be greater than zero').show();
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').hide();
                }
            }
        });
        
        // Quantity validation
        $(document).on('input change', '.quantity-input', function() {
            const qty = parseInt($(this).val()) || 0;
            const stock = parseInt($(this).data('stock')) || 0;
            
            if (qty > stock && stock > 0) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text(`Only ${stock} items available in stock`).show();
            } else if (qty < 0) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('Quantity cannot be negative').show();
            } else {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').hide();
            }
        });
    }

    // Setup duplicate product prevention
    setupDuplicatePrevention() {
        // Update tracking when rows are removed
        $(document).on('click', '.remove-item', function() {
            setTimeout(() => {
                this.updateProductTracking();
            }, 100);
        });
        
        // Update tracking when product selection changes
        $(document).on('change', '.product-select', function() {
            setTimeout(() => {
                this.updateProductTracking();
            }, 100);
        });
        
        // Add visual indicator for duplicate products
        $(document).on('change', '.product-select', function() {
            const $row = $(this).closest('tr');
            const productId = $(this).val();
            
            if (productId && this.checkDuplicateProduct(productId, $row)) {
                $row.addClass('duplicate-product');
                $row.find('.product-select').addClass('is-invalid');
            } else {
                $row.removeClass('duplicate-product');
            }
        });
    }

    // Show error message
    showError(message, type = 'error') {
        // Remove existing alerts
        $('.alert').remove();
        
        const alertClass = type === 'warning' ? 'alert-warning' : 
                          type === 'info' ? 'alert-info' : 'alert-danger';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <strong>${type === 'warning' ? 'Warning!' : type === 'info' ? 'Info:' : 'Error!'}</strong> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        $('#invoice-form').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        
        // Scroll to top on error
        if (type === 'error') {
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        }
    }
}

// Export for use in other modules
window.InvoiceValidator = InvoiceValidator;
