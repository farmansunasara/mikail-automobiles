
@extends('layouts.admin')

@section('title', 'Create New Order')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@push('styles')
<style>
.variant-item {
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 8px;
    margin-bottom: 8px;
    background-color: #f8f9fa;
}

.variant-item:hover {
    background-color: #e9ecef;
}

.badge {
    font-size: 0.75rem;
}

.quantity-input {
    width: 80px;
    text-align: center;
}

.price-input:read-only {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

.price-input:not(:read-only) {
    background-color: #fff3cd;
}

.loading {
    opacity: 0.6;
    pointer-events: none;
}

.quantity-input.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.stock-info {
    font-size: 0.8rem;
    color: #17a2b8;
    margin-top: 2px;
}

#manufacturing-info-alert {
    border-left: 4px solid #17a2b8;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}
</style>
@endpush

@section('content')
            <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                @csrf
                
                <!-- Order Details -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Order Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="order_number">Order Number</label>
                                    <input type="text" class="form-control" id="order_number" 
                                           value="{{ $orderNumber }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="customer_id">Customer <span class="text-danger">*</span></label>
                                    <select class="form-control @error('customer_id') is-invalid @enderror" 
                                            id="customer_id" name="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                    {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="order_date">Order Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('order_date') is-invalid @enderror" 
                                           id="order_date" name="order_date" 
                                           value="{{ old('order_date', now()->toDateString()) }}" required>
                                    @error('order_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="delivery_date">Delivery Date</label>
                                    <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                           id="delivery_date" name="delivery_date" 
                                           value="{{ old('delivery_date') }}">
                                    @error('delivery_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" 
                                              placeholder="Additional notes for this order">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Order Items</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="items-table">
                                <thead>
                                    <tr>
                                        <th width="150px">Category</th>
                                        <th width="200px">Product</th>
                                        <th>Color & Quantity</th>
                                        <th width="120px">Price</th>
                                        <th width="100px">Total</th>
                                        <th width="50px">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="items-tbody">
                                    <!-- Items will be added here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Add Item Button -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-primary" id="addItem">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                        
                        <div class="text-center" id="noItemsMessage" style="display: none;">
                            <p class="text-muted">No items added yet. Click "Add Item" to start.</p>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Order Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Items:</label>
                                    <span id="totalItems" class="form-control-plaintext">0</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Quantity:</label>
                                    <span id="totalQuantity" class="form-control-plaintext">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Total Amount:</label>
                                    <span id="totalAmount" class="form-control-plaintext h4 text-primary">₹0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> Create Order
                                </button>
                                <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 0;
    let variantIndex = 0;

    // Add initial item row
    addItem();

    // Add item
    $('#addItem').click(function() {
        addItem();
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        updateSummary();
        
        // Show no items message if no rows left
        if ($('.item-row').length === 0) {
            $('#noItemsMessage').show();
        }
    });

    // Remove variant
    $(document).on('click', '.remove-variant', function() {
        $(this).closest('.variant-row').remove();
        updateSummary();
    });

    // Category selection change
    $(document).on('change', '.category-select', function() {
        const categoryId = $(this).val();
        const $row = $(this).closest('tr');
        const $productSelect = $row.find('.product-select');
        const variantsContainer = $row.find('.variants-container');
        
        if (!categoryId) {
            $productSelect.html('<option value="">Select Product</option>').prop('disabled', true);
            variantsContainer.html('<div class="text-muted">Select a product first</div>');
            $row.find('.price-input').val('').prop('readonly', true);
            return;
        }
        
        $productSelect.prop('disabled', true).html('<option value="">Loading...</option>');
        $row.addClass('loading');
        
        // Load products for selected category
        $.get('/api/products/by-category', { category_id: categoryId })
            .done(function(products) {
                let options = '<option value="">Select Product</option>';
                products.forEach(function(product) {
                    options += `<option value="${product.id}">${product.name}</option>`;
                });
                $productSelect.html(options).prop('disabled', false);
            })
            .fail(function() {
                $productSelect.html('<option value="">Error loading products</option>');
                showOrderError('Error loading products. Please try again.');
            })
            .always(function() {
                $row.removeClass('loading');
            });
    });

    // Product selection change
    $(document).on('change', '.product-select', function() {
        const productId = $(this).val();
        const $row = $(this).closest('tr');
        const variantsContainer = $row.find('.variants-container');
        const $priceInput = $row.find('.price-input');
        
        if (!productId) {
            variantsContainer.html('<div class="text-muted">Select a product first</div>');
            $priceInput.val('').prop('readonly', true);
            return;
        }
        
        $row.addClass('loading');
        
        // Load product variants
        $.get(`/api/products/variants/${productId}`)
            .done(function(data) {
                console.log('API Response:', data); // Debug log
                if (data && data.variants && data.variants.length > 0) {
                    let html = '';
                    data.variants.forEach(function(variant, variantIndex) {
                        html += `
                            <div class="variant-item mb-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="badge badge-info">${variant.color || 'Default'}</span>
                                        <input type="hidden" name="items[${$row.data('index')}][variants][${variantIndex}][product_id]" value="${variant.id}">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control quantity-input" 
                                               name="items[${$row.data('index')}][variants][${variantIndex}][quantity]" 
                                               min="0" value="0" placeholder="Qty" 
                                               data-max-stock="${variant.quantity}"
                                               data-variant-name="${variant.color || 'Default'}">
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Stock: ${variant.quantity}</small>
                                        ${variant.quantity === 0 ? '<span class="badge badge-secondary ml-2">Out of Stock</span>' : ''}
                                        ${variant.quantity > 0 && variant.quantity <= 10 ? '<span class="badge badge-warning ml-2">Low Stock</span>' : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    variantsContainer.html(html);
                    
                    // Set price from first variant
                    const firstVariant = data.variants[0];
                    $priceInput.val(firstVariant.price || 0).prop('readonly', false);
                } else {
                    variantsContainer.html('<div class="text-muted">No variants available</div>');
                    $priceInput.val('').prop('readonly', true);
                }
            })
            .fail(function() {
                showOrderError('Error loading product variants. Please try again.');
            })
            .always(function() {
                $row.removeClass('loading');
            });
        
        updateSummary();
    });

    // Price change
    $(document).on('input', '.price-input', function() {
        updateSummary();
    });

    // Quantity change with stock validation
    $(document).on('input', '.quantity-input', function() {
        validateStockAvailability($(this));
        updateSummary();
    });

    function addItem() {
        const rowHtml = `
            <tr class="item-row" data-index="${itemIndex}">
                <td>
                    <select class="form-control category-select" name="items[${itemIndex}][category_id]" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </td>
                <td>
                    <select class="form-control product-select" name="items[${itemIndex}][product_id]" required disabled>
                        <option value="">Select Product</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </td>
                <td class="variants-container">
                    <div class="text-muted">Select a product first</div>
                </td>
                <td>
                    <input type="number" class="form-control price-input" name="items[${itemIndex}][price]" 
                           step="0.01" min="0.01" required placeholder="0.00" readonly>
                    <div class="invalid-feedback"></div>
                </td>
                <td class="text-right">
                    <strong class="row-total">₹0.00</strong>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#items-tbody').append(rowHtml);
        $('#noItemsMessage').hide();
        itemIndex++;
        updateSummary();
        
        // Add animation
        $('.item-row:last').hide().fadeIn(300);
    }


    function addVariant(variant, itemRow, container) {
        const itemIndex = itemRow.data('index');
        const variantHtml = `
            <div class="variant-item mb-2">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <span class="badge badge-info">${variant.color || 'Default'}</span>
                        <input type="hidden" name="items[${itemIndex}][variants][${variantIndex}][product_id]" value="${variant.id}">
                    </div>
                    <div class="col-md-2">
                        <span class="text-muted">Stock: ${variant.quantity}</span>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control quantity-input" 
                               name="items[${itemIndex}][variants][${variantIndex}][quantity]" 
                               min="0" value="0" 
                               placeholder="Qty">
                    </div>
                    <div class="col-md-2">
                        <span class="badge ${variant.quantity > 0 ? 'badge-success' : 'badge-danger'}">
                            ${variant.quantity > 0 ? 'In Stock' : 'Out of Stock'}
                        </span>
                    </div>
                </div>
            </div>
        `;
        
        container.append(variantHtml);
        variantIndex++;
    }

    function validateStockAvailability($input) {
        // Simplified: No longer prevents order creation based on stock
        // Just shows informational tooltip
        const quantity = parseInt($input.val()) || 0;
        const maxStock = parseInt($input.data('max-stock')) || 0;
        
        if (quantity > maxStock && maxStock > 0) {
            $input.attr('title', `Available stock: ${maxStock}, Requested: ${quantity}. Additional quantity will be manufactured as needed.`);
        } else {
            $input.removeAttr('title');
        }
    }

    function updateSummary() {
        let grandTotal = 0;
        let totalItems = 0;
        let totalQuantity = 0;
        let stockShortages = [];
        
        $('.item-row').each(function() {
            const $row = $(this);
            const price = parseFloat($row.find('.price-input').val()) || 0;
            let rowTotal = 0;
            let itemQuantity = 0;
            
            $row.find('.quantity-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                const maxStock = parseInt($(this).data('max-stock')) || 0;
                const variantName = $(this).data('variant-name') || 'Unknown';
                
                if (qty > 0) {
                    rowTotal += qty * price;
                    itemQuantity += qty;
                    
                    if (qty > maxStock && maxStock >= 0) {
                        stockShortages.push(`${variantName}: Need ${qty - maxStock} more`);
                    }
                }
            });
            
            $row.find('.row-total').text('₹' + rowTotal.toFixed(2));
            grandTotal += rowTotal;
            
            if (itemQuantity > 0) {
                totalItems++;
                totalQuantity += itemQuantity;
            }
        });
        
        // Update order summary
        $('#totalAmount').text('₹' + grandTotal.toFixed(2));
        $('#totalItems').text(totalItems);
        $('#totalQuantity').text(totalQuantity);
        
        // Show/hide no items message
        if ($('.item-row').length === 0) {
            $('#noItemsMessage').show();
        } else {
            $('#noItemsMessage').hide();
        }
        
        // Show manufacturing info if any shortages
        if (stockShortages.length > 0) {
            showManufacturingInfo(stockShortages);
        } else {
            hideManufacturingInfo();
        }
    }

    function showManufacturingInfo(shortages) {
        let infoHtml = `
            <div class="alert alert-info alert-dismissible fade show" id="manufacturing-info-alert" style="margin-top: 10px;">
                <h6><i class="fas fa-info-circle"></i> Manufacturing Information</h6>
                <p>The following items may need manufacturing after order creation:</p>
                <ul class="mb-0">
        `;
        
        shortages.forEach(shortage => {
            infoHtml += `<li>${shortage}</li>`;
        });
        
        infoHtml += `
                </ul>
                <small class="text-muted">Manufacturing requirements can be calculated and managed after order creation.</small>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Remove existing info if any
        $('#manufacturing-info-alert').remove();
        
        // Add info after the order summary
        $('.card:has(#totalAmount)').after(infoHtml);
    }

    function hideManufacturingInfo() {
        $('#manufacturing-info-alert').remove();
    }

    // Comprehensive form validation
    function validateOrderForm() {
        try {
            let isValid = true;
            let errors = [];
            
            console.log('Starting form validation...');
            
            // Clear previous validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // Check if customer is selected
            if (!$('#customer_id').val()) {
                $('#customer_id').addClass('is-invalid');
                errors.push('Please select a customer');
                isValid = false;
                console.log('Customer validation failed');
            }
            
            // Check if at least one item is added
            if ($('.item-row').length === 0) {
                errors.push('Please add at least one item');
                isValid = false;
                console.log('No items found');
            }
            
            console.log('Number of items found:', $('.item-row').length);
            
            // Check if at least one item has valid quantities
            let hasValidItems = false;
            
            // Validate each item
            $('.item-row').each(function(index) {
                const $row = $(this);
                const categoryId = $row.find('.category-select').val();
                const productId = $row.find('.product-select').val();
                const price = parseFloat($row.find('.price-input').val()) || 0;
                let hasValidQuantity = false;
                
                console.log(`Validating item ${index}:`, {
                    categoryId: categoryId,
                    productId: productId,
                    price: price
                });
                
                // Check category selection
                if (!categoryId) {
                    $row.find('.category-select').addClass('is-invalid');
                    $row.find('.category-select').siblings('.invalid-feedback').text('Please select a category');
                    errors.push(`Item ${index + 1}: Please select a category`);
                    isValid = false;
                }
                
                // Check product selection
                if (!productId) {
                    $row.find('.product-select').addClass('is-invalid');
                    $row.find('.product-select').siblings('.invalid-feedback').text('Please select a product');
                    errors.push(`Item ${index + 1}: Please select a product`);
                    isValid = false;
            }
            
            // Check price
            if (price <= 0) {
                $row.find('.price-input').addClass('is-invalid');
                $row.find('.price-input').siblings('.invalid-feedback').text('Please set a valid price');
                errors.push(`Item ${index + 1}: Please set a valid price`);
                isValid = false;
            }
            
                // Check quantities
                $row.find('.quantity-input').each(function() {
                    const qty = parseInt($(this).val()) || 0;
                    console.log(`Quantity found: ${qty}`);
                    if (qty > 0) {
                        hasValidQuantity = true;
                    }
                });
                
                console.log(`Item ${index} has valid quantity:`, hasValidQuantity);
                
                if (!hasValidQuantity) {
                    errors.push(`Item ${index + 1}: Please enter quantity greater than 0 for at least one variant`);
                    isValid = false;
                } else if (categoryId && productId && price > 0) {
                    hasValidItems = true;
                }
            });
            
            console.log('Has valid items:', hasValidItems);
            
            if (!hasValidItems && $('.item-row').length > 0) {
                errors.push('Please add at least one item with valid quantities');
                isValid = false;
            }
            
            // Check for stock shortages and add warnings (informational only in simplified system)
            let stockWarnings = [];
            $('.quantity-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                const maxStock = parseInt($(this).data('max-stock')) || 0;
                const variantName = $(this).data('variant-name') || 'Unknown';
                
                if (qty > maxStock && maxStock >= 0) {
                    stockWarnings.push(`${variantName}: Requested ${qty}, Available ${maxStock}`);
                }
            });
            
            if (stockWarnings.length > 0) {
                console.log('Stock warnings found:', stockWarnings);
                console.log('Proceeding with order creation (simplified system allows stock shortages)');
                // Note: In simplified system, we proceed anyway and generate manufacturing requirements
            }
            
            if (!isValid) {
                console.log('Validation failed with errors:', errors);
                showValidationErrors(errors);
            } else {
                console.log('Validation passed successfully');
            }
            
            return isValid;
            
        } catch (error) {
            console.error('Error in form validation:', error);
            showOrderError('An error occurred during validation: ' + error.message);
            return false;
        }
    }    // Form submission
    $('#orderForm').submit(function(e) {
        console.log('Form submission started');
        e.preventDefault(); // Stop the default submission first
        
        console.log('Number of item rows:', $('.item-row').length);
        
        // Log all form elements for debugging
        console.log('Customer ID:', $('#customer_id').val());
        console.log('Order Date:', $('#order_date').val());
        
        // Check each item row
        $('.item-row').each(function(index) {
            const $row = $(this);
            console.log(`Item ${index}:`, {
                category: $row.find('.category-select').val(),
                product: $row.find('.product-select').val(),
                price: $row.find('.price-input').val(),
                quantities: $row.find('.quantity-input').map(function() { return $(this).val(); }).get()
            });
        });
        
        if (!validateOrderForm()) {
            console.log('Form validation failed');
            return false;
        }
        
        // Filter out zero-quantity variants before submission
        console.log('=== FILTERING ZERO QUANTITIES ===');
        $('.item-row').each(function() {
            const $row = $(this);
            $row.find('.quantity-input').each(function() {
                const $input = $(this);
                const qty = parseInt($input.val()) || 0;
                if (qty === 0) {
                    // Remove the input from form submission by disabling it
                    $input.prop('disabled', true);
                    console.log('Disabled zero quantity input:', $input.attr('name'));
                }
            });
        });
        
        // Also remove the corresponding product_id inputs for disabled quantities
        $('.item-row').each(function() {
            const $row = $(this);
            $row.find('.quantity-input:disabled').each(function() {
                const $quantityInput = $(this);
                const quantityName = $quantityInput.attr('name');
                // Find the corresponding product_id input and disable it
                if (quantityName) {
                    const productIdName = quantityName.replace('[quantity]', '[product_id]');
                    $row.find(`input[name="${productIdName}"]`).prop('disabled', true);
                    console.log('Disabled corresponding product_id:', productIdName);
                }
            });
        });
        
        // Debug: Log filtered form data
        const formData = new FormData(this);
        console.log('Filtered form data being submitted:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
        
        console.log('Form validation passed, submitting filtered data...');
        
        // Track the actual HTTP request
        const form = this;
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        
        // Now submit the form with filtered data
        this.submit();
        
        // Let the form submit naturally but track if it fails
        setTimeout(function() {
            console.log('Form should have submitted by now. If you see this message and the page refreshed without success, there\'s a server-side issue.');
        }, 1000);
    });
});
</script>
@endpush
