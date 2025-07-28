@extends('layouts.admin')

@section('title', 'Create Invoice')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.invoice-form {
    max-width: 1200px;
    margin: 0 auto;
}
.color-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    color: white;
    font-size: 0.8em;
    margin-right: 5px;
}
.quantity-input {
    width: 80px;
    text-align: center;
}
.stock-info {
    font-size: 0.75em;
    color: #6c757d;
}
.stock-warning { color: #dc3545; }
.stock-low { color: #ffc107; }
.stock-good { color: #28a745; }
.product-row {
    border-bottom: 1px solid #dee2e6;
}
.remove-btn {
    border: none;
    background: none;
    color: #dc3545;
    font-size: 1.2em;
    cursor: pointer;
}
.color-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    padding: 5px;
    border: 1px solid #e9ecef;
    border-radius: 4px;
}
.loading {
    opacity: 0.6;
    pointer-events: none;
}
.composite-badge {
    background-color: #28a745;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.7em;
    margin-left: 5px;
}
.component-info {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 8px;
    margin-top: 5px;
    font-size: 0.85em;
}
.component-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px 0;
}
.component-stock-warning {
    color: #dc3545;
    font-weight: bold;
}
</style>
@endpush

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="invoice-form">
    <form action="{{ route('invoices.gst.store') }}" method="POST" id="invoice-form">
        @csrf
        
        <!-- Invoice Header -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-file-invoice"></i> Invoice Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control" value="{{ $invoice_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="invoice_date">Invoice Date</label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="customer_id">Customer *</label>
                            <select name="customer_id" id="customer_id" class="form-control" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            data-address="{{ $customer->address }}" 
                                            data-gstin="{{ $customer->gstin }}">
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div id="customer-details" style="display: none;">
                            <div class="alert alert-info">
                                <strong>Address:</strong> <span id="cust-address"></span><br>
                                <strong>GSTIN:</strong> <span id="cust-gstin"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gst_rate">GST Rate (%)</label>
                                    <input type="number" name="gst_rate" id="gst_rate" class="form-control" 
                                           value="18" min="0" max="100" step="0.01" required>
                                    <small class="form-text text-muted">This GST rate will be applied to the entire invoice</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> Invoice Items</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="items-table">
                        <thead class="thead-light">
                            <tr>
                                <th width="200px">Category</th>
                                <th width="200px">Product</th>
                                <th>Colors & Quantities</th>
                                <th width="100px">Price</th>
                                <th width="100px">Total</th>
                                <th width="50px"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <!-- Items will be added here -->
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-primary" id="add-item-btn">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
                
                <div id="no-items-message" class="text-center py-4" style="display:none;">
                    <p class="text-muted mb-3">No items added yet</p>
                    <button type="button" class="btn btn-primary" id="add-first-item">
                        <i class="fas fa-plus"></i> Add First Item
                    </button>
                </div>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="row">
            <div class="col-md-6 offset-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-calculator"></i> Invoice Summary</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th>Subtotal:</th>
                                <td class="text-right" id="subtotal">₹0.00</td>
                            </tr>
                            <tr>
                                <th>Discount:</th>
                                <td class="text-right">
                                    <div class="input-group input-group-sm">
                                        <select name="discount_type" id="discount_type" class="form-control" style="max-width: 80px;">
                                            <option value="0">₹</option>
                                            <option value="1">%</option>
                                        </select>
                                        <input type="number" name="discount_value" id="discount_value" class="form-control"
                                               placeholder="0" min="0" step="0.01" value="0" style="max-width: 80px;">
                                    </div>
                                    <small class="text-muted" id="discount_amount_display">₹0.00</small>
                                </td>
                            </tr>
                            <tr>
                                <th>After Discount:</th>
                                <td class="text-right" id="after_discount">₹0.00</td>
                            </tr>
                            <tr>
                                <th>CGST:</th>
                                <td class="text-right" id="cgst">₹0.00</td>
                            </tr>
                            <tr>
                                <th>SGST:</th>
                                <td class="text-right" id="sgst">₹0.00</td>
                            </tr>
                            <tr class="border-top">
                                <th class="h5">Grand Total:</th>
                                <td class="text-right h5 font-weight-bold text-primary" id="grand_total">₹0.00</td>
                            </tr>
                        </table>
                        
                        <button type="submit" class="btn btn-success btn-block btn-lg mt-3" id="submit-btn">
                            <i class="fas fa-save"></i> Create Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let itemIndex = 0;
    
    // Initialize Select2
    $('#customer_id').select2({
        placeholder: 'Select Customer',
        allowClear: true
    });
    
    // Customer selection handler
    $('#customer_id').on('change', function() {
        const selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#cust-address').text(selected.data('address'));
            $('#cust-gstin').text(selected.data('gstin'));
            $('#customer-details').show();
        } else {
            $('#customer-details').hide();
        }
    });
    
    // Add item handlers
    $('#add-item-btn, #add-first-item').on('click', function() {
        addNewItem();
    });
    
    function addNewItem() {
        const rowHtml = `
            <tr class="product-row" data-index="${itemIndex}">
                <td>
                    <select name="items[${itemIndex}][category_id]" class="form-control category-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-control product-select" required disabled>
                        <option value="">Select Product</option>
                    </select>
                </td>
                <td class="colors-container">
                    <div class="text-muted">Select a product first</div>
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][price]" class="form-control price-input" step="0.01" readonly>
                </td>
                <td class="text-right">
                    <strong class="row-total">₹0.00</strong>
                </td>
                <td>
                    <button type="button" class="remove-btn" onclick="removeItem(${itemIndex})" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#items-tbody').append(rowHtml);
        $('#no-items-message').hide();
        itemIndex++;
    }
    
    // Category change handler
    $(document).on('change', '.category-select', function() {
        const $row = $(this).closest('tr');
        const categoryId = $(this).val();
        const $productSelect = $row.find('.product-select');
        
        if (!categoryId) {
            $productSelect.html('<option value="">Select Product</option>').prop('disabled', true);
            clearProductData($row);
            return;
        }
        
        $productSelect.prop('disabled', true).html('<option value="">Loading...</option>');
        
        $.get('/api/products/by-category', { category_id: categoryId })
            .done(function(products) {
                let options = '<option value="">Select Product</option>';
                products.forEach(function(product) {
                    const compositeBadge = product.is_composite ? '<span class="composite-badge">Composite</span>' : '';
                    options += `<option value="${product.id}" data-is-composite="${product.is_composite}">${product.name}${compositeBadge}</option>`;
                });
                $productSelect.html(options).prop('disabled', false);
            })
            .fail(function() {
                $productSelect.html('<option value="">Error loading products</option>');
                alert('Error loading products. Please try again.');
            });
    });
    
    // Product change handler
    $(document).on('change', '.product-select', function() {
        const $row = $(this).closest('tr');
        const productId = $(this).val();
        
        if (!productId) {
            clearProductData($row);
            return;
        }
        
        $row.addClass('loading');
        
        $.get(`/api/products/variants/${productId}`)
            .done(function(data) {
                if (data.variants && data.variants.length > 0) {
                    createColorInputs($row, data.variants);
                    
                    // Set price and GST from first variant
                    const firstVariant = data.variants[0];
                    $row.find('.price-input').val(firstVariant.price);
                    $row.find('.gst-input').val(firstVariant.gst_rate);
                }
            })
            .fail(function() {
                alert('Error loading product variants');
            })
            .always(function() {
                $row.removeClass('loading');
            });
    });
    
    function createColorInputs($row, variants) {
        const index = $row.data('index');
        let html = '';
        
        variants.forEach(function(variant, variantIndex) {
            const colorName = variant.color || 'Default';
            const stockClass = getStockClass(variant.quantity);
            const colorStyle = getColorStyle(colorName);
            const isComposite = variant.is_composite;
            
            html += `
                <div class="color-item">
                    <div class="color-badge" style="${colorStyle}">${colorName}</div>
                    ${isComposite ? '<span class="composite-badge">Composite</span>' : ''}
                    <input type="number" 
                           name="items[${index}][variants][${variantIndex}][quantity]" 
                           class="form-control quantity-input" 
                           min="0" 
                           max="${variant.quantity}" 
                           value="0" 
                           placeholder="Qty"
                           onchange="updateTotals()"
                           data-is-composite="${isComposite}">
                    <input type="hidden" name="items[${index}][variants][${variantIndex}][product_id]" value="${variant.id}">
                    <div class="stock-info ${stockClass}">Stock: ${variant.quantity}</div>
                </div>
            `;
            
            // Add component information for composite products
            if (isComposite && variant.components && variant.components.length > 0) {
                html += `
                    <div class="component-info">
                        <strong>Components:</strong>
                        <div class="mt-1">
                `;
                variant.components.forEach(function(component) {
                    const componentStockClass = getStockClass(component.component_product.quantity);
                    html += `
                        <div class="component-item">
                            <span>${component.component_product.name} (${component.quantity_needed} pcs each)</span>
                            <span class="stock-info ${componentStockClass}">Stock: ${component.component_product.quantity}</span>
                        </div>
                    `;
                });
                html += `
                        </div>
                    </div>
                `;
            }
        });
        
        $row.find('.colors-container').html(html);
        updateTotals();
    }
    
    function clearProductData($row) {
        $row.find('.colors-container').html('<div class="text-muted">Select a product first</div>');
        $row.find('.price-input').val('');
        $row.find('.row-total').text('₹0.00');
        updateTotals();
    }
    
    function getStockClass(quantity) {
        if (quantity <= 0) return 'stock-warning';
        if (quantity <= 10) return 'stock-low';
        return 'stock-good';
    }
    
    function getColorStyle(colorName) {
        const colors = {
            'black': 'background-color: #343a40; color: white;',
            'white': 'background-color: #f8f9fa; color: black; border: 1px solid #dee2e6;',
            'red': 'background-color: #dc3545; color: white;',
            'blue': 'background-color: #007bff; color: white;',
            'green': 'background-color: #28a745; color: white;',
            'yellow': 'background-color: #ffc107; color: black;',
            'silver': 'background-color: #6c757d; color: white;',
            'golden': 'background-color: #ffd700; color: black;',
            'clear': 'background-color: #e9ecef; color: black;'
        };
        return colors[colorName.toLowerCase()] || 'background-color: #6c757d; color: white;';
    }
    
    window.removeItem = function(index) {
        $(`.product-row[data-index="${index}"]`).remove();
        if ($('#items-tbody tr').length === 0) {
            $('#no-items-message').show();
        }
        updateTotals();
    };
    
    window.updateTotals = function() {
        let grandSubtotal = 0;
        
        $('.product-row').each(function() {
            const $row = $(this);
            const price = parseFloat($row.find('.price-input').val()) || 0;
            let rowTotal = 0;
            
            $row.find('.quantity-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                rowTotal += qty * price;
            });
            
            $row.find('.row-total').text('₹' + rowTotal.toFixed(2));
            grandSubtotal += rowTotal;
        });

        // Calculate discount
        var discountType = parseFloat($('#discount_type').val()) || 0;
        var discountValue = parseFloat($('#discount_value').val()) || 0;
        var discountAmount = 0;

        if (discountValue > 0) {
            if (discountType == 1) {
                // Percentage discount
                discountAmount = (grandSubtotal * discountValue) / 100;
            } else {
                // Fixed amount discount
                discountAmount = Math.min(discountValue, grandSubtotal);
            }
        }

        var afterDiscount = grandSubtotal - discountAmount;
        
        // Calculate GST on discounted amount using single invoice-level GST rate
        var invoiceGstRate = parseFloat($('#gst_rate').val()) || 0;
        var totalGstAmount = (afterDiscount * invoiceGstRate) / 100;
        var cgstAmount = totalGstAmount / 2;
        var sgstAmount = totalGstAmount / 2;

        var grand_total = afterDiscount + cgstAmount + sgstAmount;

        // Update display
        $('#subtotal').text('₹' + grandSubtotal.toFixed(2));
        $('#discount_amount_display').text('₹' + discountAmount.toFixed(2));
        $('#after_discount').text('₹' + afterDiscount.toFixed(2));
        $('#cgst').text('₹' + cgstAmount.toFixed(2));
        $('#sgst').text('₹' + sgstAmount.toFixed(2));
        $('#grand_total').text('₹' + grand_total.toFixed(2));
    };
    
    // Form validation
    $('#invoice-form').on('submit', function(e) {
        let hasItems = false;
        let hasQuantity = false;
        
        $('.quantity-input').each(function() {
            if (parseInt($(this).val()) > 0) {
                hasItems = true;
                hasQuantity = true;
                return false;
            }
        });
        
        if (!hasItems || !hasQuantity) {
            e.preventDefault();
            alert('Please add at least one item with quantity greater than 0');
            return false;
        }
        
        // Disable submit button to prevent double submission
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
    });
    
    // Add discount and GST rate change handlers
    $('#discount_type, #discount_value, #gst_rate').on('change keyup', function() {
        updateTotals();
    });
    
    // Add first item automatically
    addNewItem();
});
</script>
@endpush
