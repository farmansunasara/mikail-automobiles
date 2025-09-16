@extends('layouts.admin')

@section('title', 'Edit Order - ' . $order->order_number)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
<li class="breadcrumb-item"><a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
            @if($order->status !== 'pending')
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Notice:</strong> This order cannot be edited because its status is "{{ ucfirst($order->status) }}". 
                    Only pending orders can be modified.
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-primary ml-2">
                        <i class="fas fa-eye"></i> View Order
                    </a>
                </div>
            @endif
            
            <form action="{{ route('orders.update', $order) }}" method="POST" id="orderForm">
                @csrf
                @method('PUT')
                
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
                                           value="{{ $order->order_number }}" readonly>
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
                                                    {{ old('customer_id', $order->customer_id) == $customer->id ? 'selected' : '' }}>
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
                                           value="{{ old('order_date', $order->order_date->toDateString()) }}" required>
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
                                           value="{{ old('delivery_date', $order->delivery_date?->toDateString()) }}">
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
                                              placeholder="Additional notes for this order">{{ old('notes', $order->notes) }}</textarea>
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
                                    @foreach($productVariants as $productId => $productData)
                                        @php
                                            $itemIndex = $loop->index;
                                        @endphp
                                        <tr class="item-row" data-index="{{ $itemIndex }}">
                                            <td>
                                                <select class="form-control category-select" name="items[{{ $itemIndex }}][category_id]" required>
                                                    <option value="">Select Category</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" 
                                                                {{ $productData['product']->category_id == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </td>
                                            <td>
                                                <select class="form-control product-select" name="items[{{ $itemIndex }}][product_id]" required>
                                                    <option value="">Select Product</option>
                                                    @foreach(\App\Models\Product::where('category_id', $productData['product']->category_id)->get() as $product)
                                                        <option value="{{ $product->id }}" 
                                                                {{ $product->id == $productId ? 'selected' : '' }}>
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </td>
                                            <td class="variants-container">
                                                @foreach($productData['variants'] as $variant)
                                                    @php
                                                        $variantIndex = $loop->index;
                                                        $existingQuantity = $productData['existing_quantities'][$variant->id] ?? 0;
                                                    @endphp
                                                    <div class="variant-item mb-2">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <span class="badge badge-info">{{ $variant->color ?: 'Default' }}</span>
                                                                <input type="hidden" name="items[{{ $itemIndex }}][variants][{{ $variantIndex }}][product_id]" value="{{ $variant->id }}">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <input type="number" class="form-control quantity-input" 
                                                                       name="items[{{ $itemIndex }}][variants][{{ $variantIndex }}][quantity]" 
                                                                       min="0" value="{{ $existingQuantity }}" placeholder="Qty" 
                                                                       data-max-stock="{{ $variant->quantity }}"
                                                                       data-variant-name="{{ $variant->color ?: 'Default' }}">
                                                                <div class="stock-warning" style="display: none; color: #dc3545; font-size: 0.8rem; margin-top: 2px;">
                                                                    <i class="fas fa-exclamation-triangle"></i> Exceeds available stock
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <small class="text-muted">Stock: {{ $variant->quantity }}</small>
                                                                @if($variant->quantity === 0)
                                                                    <span class="badge badge-danger ml-2">Out of Stock</span>
                                                                @elseif($variant->quantity <= 10)
                                                                    <span class="badge badge-warning ml-2">Low Stock</span>
                                                                @endif
                                                                @if($existingQuantity > 0)
                                                                    <span class="badge badge-success ml-2">In Order</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td>
                                                <input type="number" class="form-control price-input" name="items[{{ $itemIndex }}][price]" 
                                                       step="0.01" min="0.01" required placeholder="0.00" 
                                                       value="{{ $productData['price'] }}">
                                                <div class="invalid-feedback"></div>
                                            </td>
                                            <td class="text-right">
                                                <strong class="row-total">₹{{ number_format($productData['price'] * array_sum($productData['existing_quantities']), 2) }}</strong>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Total Items:</label>
                                    <span id="totalItems" class="form-control-plaintext">{{ count($productVariants) }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Total Quantity:</label>
                                    <span id="totalQuantity" class="form-control-plaintext">{{ $order->total_quantity }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Total Amount:</label>
                                    <span id="totalAmount" class="form-control-plaintext h4 text-primary">₹{{ number_format($order->total_amount, 2) }}</span>
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
                                    <i class="fas fa-save"></i> Update Order
                                </button>
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

<!-- Item Template -->
<template id="itemTemplate">
    <tr class="item-row" data-index="">
        <td>
            <select class="form-control category-select" name="items[ITEM_INDEX][category_id]" required>
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </td>
        <td>
            <select class="form-control product-select" name="items[ITEM_INDEX][product_id]" required>
                <option value="">Select Product</option>
            </select>
            <div class="invalid-feedback"></div>
        </td>
        <td class="variants-container">
            <!-- Variants will be loaded here -->
        </td>
        <td>
            <input type="number" class="form-control price-input" name="items[ITEM_INDEX][price]" 
                   step="0.01" min="0.01" required placeholder="0.00">
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
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = {{ count($productVariants) }};
    let variantIndex = 0;

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

    // Category selection change
    $(document).on('change', '.category-select', function() {
        const categoryId = $(this).val();
        const itemRow = $(this).closest('.item-row');
        const productSelect = itemRow.find('.product-select');
        const variantsContainer = itemRow.find('.variants-container');
        
        // Clear product and variants
        productSelect.empty().append('<option value="">Select Product</option>');
        variantsContainer.empty();
        
        if (categoryId) {
            loadProductsByCategory(categoryId, productSelect);
        }
        updateSummary();
    });

    // Product selection change
    $(document).on('change', '.product-select', function() {
        const productId = $(this).val();
        const itemRow = $(this).closest('.item-row');
        const variantsContainer = itemRow.find('.variants-container');
        
        if (productId) {
            loadProductVariants(productId, itemRow, variantsContainer);
        } else {
            variantsContainer.empty();
        }
        updateSummary();
    });

    // Price change
    $(document).on('input', '.price-input', function() {
        updateSummary();
    });

    // Quantity change
    $(document).on('input', '.quantity-input', function() {
        validateStockAvailability($(this));
        updateSummary();
    });

    // Initialize stock validation for existing variants
    $('.quantity-input').each(function() {
        validateStockAvailability($(this));
    });

    function addItem() {
        const template = $('#itemTemplate').html();
        const itemHtml = template.replace(/ITEM_INDEX/g, itemIndex);
        const itemElement = $(itemHtml);
        
        // Set the data-index attribute for the new item
        itemElement.attr('data-index', itemIndex);
        
        $('#items-tbody').append(itemElement);
        $('#noItemsMessage').hide();
        itemIndex++;
        updateSummary();
        
        // Add animation
        $('.item-row:last').hide().fadeIn(300);
    }

    function loadProductsByCategory(categoryId, productSelect) {
        $.ajax({
            url: '/api/products/by-category',
            method: 'GET',
            data: { category_id: categoryId },
            success: function(products) {
                productSelect.empty().append('<option value="">Select Product</option>');
                products.forEach(function(product) {
                    productSelect.append(`<option value="${product.id}">${product.name}</option>`);
                });
            },
            error: function() {
                console.error('Error loading products');
            }
        });
    }

    function loadProductVariants(productId, itemRow, container) {
        $.ajax({
            url: `/api/products/variants/${productId}`,
            method: 'GET',
            success: function(data) {
                console.log('API Response:', data);
                container.empty();
                
                if (data && data.variants && data.variants.length > 0) {
                    data.variants.forEach(function(variant) {
                        addVariant(variant, itemRow, container);
                    });
                } else {
                    container.html('<small class="text-muted">No variants available</small>');
                }
            },
            error: function() {
                console.error('Error loading product variants');
                container.html('<small class="text-danger">Error loading variants</small>');
            }
        });
    }

    function addVariant(variant, itemRow, container) {
        const itemIndex = itemRow.data('index');
        const currentVariantIndex = container.find('.variant-item').length; // Get the current count of variants in this container
        
        const variantHtml = `
            <div class="variant-item mb-2">
                <div class="row">
                    <div class="col-md-4">
                        <span class="badge badge-info">${variant.color || 'Default'}</span>
                        <input type="hidden" name="items[${itemIndex}][variants][${currentVariantIndex}][product_id]" value="${variant.id}">
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control quantity-input" 
                               name="items[${itemIndex}][variants][${currentVariantIndex}][quantity]" 
                               min="0" value="0" placeholder="Qty" 
                               data-max-stock="${variant.quantity}"
                               data-variant-name="${variant.color || 'Default'}">
                        <div class="stock-warning" style="display: none; color: #dc3545; font-size: 0.8rem; margin-top: 2px;">
                            <i class="fas fa-exclamation-triangle"></i> Exceeds available stock
                        </div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Stock: ${variant.quantity}</small>
                        ${variant.quantity === 0 ? '<span class="badge badge-danger ml-2">Out of Stock</span>' : ''}
                        ${variant.quantity > 0 && variant.quantity <= 10 ? '<span class="badge badge-warning ml-2">Low Stock</span>' : ''}
                    </div>
                </div>
            </div>
        `;
        
        container.append(variantHtml);
    }

    function validateStockAvailability($input) {
        const quantity = parseInt($input.val()) || 0;
        const maxStock = parseInt($input.data('max-stock')) || 0;
        const variantName = $input.data('variant-name') || 'Unknown';
        const $warning = $input.siblings('.stock-warning');

        if (quantity > maxStock) {
            $input.addClass('is-invalid');
            $warning.show();
            $input.attr('title', `Available stock: ${maxStock}, Requested: ${quantity}. Manufacturing requirement will be generated.`);
        } else {
            $input.removeClass('is-invalid');
            $warning.hide();
            $input.removeAttr('title');
        }
    }

    function updateSummary() {
        let totalItems = 0;
        let totalQuantity = 0;
        let totalAmount = 0;
        let stockWarnings = [];

        $('.item-row').each(function() {
            const $row = $(this);
            const price = parseFloat($row.find('.price-input').val()) || 0;
            let rowTotal = 0;
            let rowQuantity = 0;

            $row.find('.quantity-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                const maxStock = parseInt($(this).data('max-stock')) || 0;
                const variantName = $(this).data('variant-name') || 'Unknown';

                if (qty > 0) {
                    rowQuantity += qty;
                    rowTotal += qty * price;
                    
                    if (qty > maxStock) {
                        stockWarnings.push(`${variantName}: Requested ${qty}, Available ${maxStock}`);
                    }
                }
            });

            if (rowQuantity > 0) {
                totalItems++;
                totalQuantity += rowQuantity;
                totalAmount += rowTotal;
            }

            $row.find('.row-total').text('₹' + rowTotal.toFixed(2));
        });

        // Update order summary
        $('#totalAmount').text('₹' + totalAmount.toFixed(2));
        $('#totalItems').text(totalItems);
        $('#totalQuantity').text(totalQuantity);
        
        // Show/hide no items message
        if ($('.item-row').length === 0) {
            $('#noItemsMessage').show();
        } else {
            $('#noItemsMessage').hide();
        }
        
        // Show stock warnings if any
        if (stockWarnings.length > 0) {
            showStockWarnings(stockWarnings);
        } else {
            hideStockWarnings();
        }
    }

    function showStockWarnings(warnings) {
        let warningHtml = `
            <div class="alert alert-warning alert-dismissible fade show" id="stock-warning-alert" style="margin-top: 10px;">
                <h6><i class="fas fa-exclamation-triangle"></i> Stock Shortage Warning</h6>
                <p>The following items exceed available stock. Manufacturing requirements will be generated:</p>
                <ul class="mb-0">
        `;

        warnings.forEach(warning => {
            warningHtml += `<li>${warning}</li>`;
        });

        warningHtml += `
                </ul>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

        $('#stock-warning-alert').remove();
        $('.card:has(#totalAmount)').after(warningHtml);
    }

    function hideStockWarnings() {
        $('#stock-warning-alert').remove();
    }

    function validateOrderForm() {
        let errors = [];
        let isValid = true;

        // Check customer
        if (!$('#customer_id').val()) {
            errors.push('Please select a customer');
            isValid = false;
        }

        // Check if at least one item is added
        if ($('.item-row').length === 0) {
            errors.push('Please add at least one item');
            isValid = false;
        }

        // Check each item
        let hasValidItems = false;
        $('.item-row').each(function() {
            const $row = $(this);
            const categoryId = $row.find('.category-select').val();
            const productId = $row.find('.product-select').val();
            const price = parseFloat($row.find('.price-input').val()) || 0;

            if (!categoryId) {
                errors.push('Please select a category for all items');
                isValid = false;
            }

            if (!productId) {
                errors.push('Please select a product for all items');
                isValid = false;
            }

            if (price <= 0) {
                errors.push('Please set a valid price for all items');
                isValid = false;
            }

            // Check if item has any quantity > 0
            $row.find('.quantity-input').each(function() {
                if (parseInt($(this).val()) > 0) {
                    hasValidItems = true;
                }
            });
        });

        if (!hasValidItems) {
            errors.push('Please add at least one item with quantity greater than 0');
            isValid = false;
        }

        // Check for stock shortages and add warnings
        let stockWarnings = [];
        $('.quantity-input').each(function() {
            const qty = parseInt($(this).val()) || 0;
            const maxStock = parseInt($(this).data('max-stock')) || 0;
            const variantName = $(this).data('variant-name') || 'Unknown';

            if (qty > maxStock) {
                stockWarnings.push(`${variantName}: Requested ${qty}, Available ${maxStock}`);
            }
        });

        if (stockWarnings.length > 0) {
            showStockWarning(stockWarnings, function() {
                // User confirmed, continue with form submission
                if (errors.length > 0) {
                    showValidationErrors(errors);
                    return false;
                }
                return true;
            });
            return false; // Prevent form submission until user confirms
        }

        if (errors.length > 0) {
            showValidationErrors(errors);
            return false;
        }

        return isValid;
    }

    // Form submission
    $('#orderForm').submit(function(e) {
        console.log('Edit form submission started');
        e.preventDefault(); // Stop the default submission first
        
        if (!validateOrderForm()) {
            console.log('Form validation failed');
            return false;
        }
        
        // Filter out zero-quantity variants before submission (same as create form)
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
                    
                    // Also disable the corresponding hidden product_id input
                    const quantityName = $input.attr('name');
                    if (quantityName) {
                        // Extract the variant index from the quantity input name
                        const match = quantityName.match(/items\[(\d+)\]\[variants\]\[(\d+)\]\[quantity\]/);
                        if (match) {
                            const itemIndex = match[1];
                            const variantIndex = match[2];
                            const productIdName = `items[${itemIndex}][variants][${variantIndex}][product_id]`;
                            $row.find(`input[name="${productIdName}"]`).prop('disabled', true);
                            console.log('Disabled corresponding product_id:', productIdName);
                        }
                    }
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
        
        // Now submit the form with filtered data
        this.submit();
    });

    // Initialize summary
    updateSummary();
});
</script>

<style>
.quantity-input.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.stock-warning {
    font-size: 0.8rem;
    color: #dc3545;
    margin-top: 2px;
}

#stock-warning-alert {
    border-left: 4px solid #ffc107;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}
</style>
@endpush
