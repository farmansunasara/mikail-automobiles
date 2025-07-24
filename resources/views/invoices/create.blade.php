@extends('layouts.admin')

@section('title', 'Create Invoice')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.color-qty-input {
    width: 60px;
    text-align: center;
}
.stock-info {
    font-size: 0.8em;
    color: #6c757d;
}
.stock-warning {
    color: #dc3545;
}
.stock-low {
    color: #ffc107;
}
.product-row {
    border-bottom: 1px solid #dee2e6;
}
.color-header {
    text-align: center;
    vertical-align: middle;
}
.remove-btn {
    border: none;
    background: none;
    color: #dc3545;
    font-size: 1.2em;
}
</style>
@endpush

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<form action="{{ route('invoices.gst.store') }}" method="POST" id="invoice-form">
    @csrf
    
    <!-- Invoice Details at Top -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Invoice Details</h3>
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
                        <small class="form-text text-muted">Leave empty for 30 days default</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="customer_id">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-details="{{ json_encode($customer) }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div id="customer-details" style="display: none;">
                        <p class="mb-1"><strong>Address:</strong> <span id="cust-address"></span></p>
                        <p class="mb-0"><strong>GSTIN:</strong> <span id="cust-gstin"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Items in Middle -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Invoice Items</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="items-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Product</th>
                            <th>Colors & Quantities</th>
                            <th>Price</th>
                            <th>GST%</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="product-row" data-product-index="0">
                            <td>
                                <select name="items[0][category_id]" class="form-control category-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="items[0][product_id]" class="form-control product-select" required disabled>
                                    <option value="">Select Product</option>
                                </select>
                            </td>
                            <td class="colors-container">
                                <!-- Colors & quantities will be dynamically loaded here -->
                            </td>
                            <td>
                                <input type="number" name="items[0][price]" class="form-control price-input" step="0.01" readonly>
                            </td>
                            <td>
                                <input type="number" name="items[0][gst_rate]" class="form-control gst-input" step="0.01" readonly>
                            </td>
                            <td class="text-right">
                                <strong class="row-total">₹0.00</strong>
                            </td>
                            <td>
                                <button type="button" class="remove-btn" onclick="removeProduct(0)" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-3">
                <button type="button" class="btn btn-primary" id="add-new-item-btn">Add New Item</button>
            </div>
            
            <div class="text-center mt-3" id="no-products-message" style="display:none;">
                <p class="text-muted">No products added yet.</p>
            </div>
        </div>
    </div>

    <!-- Totals at Bottom -->
    <div class="row">
        <div class="col-md-6 offset-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Invoice Summary</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Subtotal:</th>
                            <td class="text-right" id="subtotal">₹0.00</td>
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
                    
                    <button type="submit" class="btn btn-success btn-block btn-lg mt-3">
                        <i class="fas fa-save"></i> Create Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Product Selection Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Product</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="product-select">Product Name</label>
                    <select id="product-select" class="form-control">
                        <option value="">Select a product...</option>
                        @foreach($productNames as $productName)
                            <option value="{{ $productName }}">{{ $productName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add-selected-product">Add Product</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#customer_id').select2();
    $('#product-select').select2({
        dropdownParent: $('#productModal')
    });
    
    var productIndex = 0;

    // Add product button click
    $('#add-product-btn').click(function() {
        $('#productModal').modal('show');
    });

    // Add selected product
    $('#add-selected-product').click(function() {
        var selectedProduct = $('#product-select').val();
        if (selectedProduct) {
            addProductToInvoice(selectedProduct);
            $('#productModal').modal('hide');
            $('#product-select').val('').trigger('change');
        }
    });

    function addProductToInvoice(productName) {
        // Fetch product variants
        $.get('/api/products/variants/' + encodeURIComponent(productName))
            .done(function(data) {
                if (data.variants && data.variants.length > 0) {
                    createProductRow(data, productIndex);
                    productIndex++;
                    updateNoProductsMessage();
                    updateTotals();
                }
            })
            .fail(function() {
                alert('Error loading product variants');
            });
    }

    // Fix productIndex incrementation for new rows
    $('#add-new-item-btn').click(function() {
        var newIndex = productIndex;
        var newRowHtml = `
            <tr class="product-row" data-product-index="${newIndex}">
                <td>
                    <select name="items[${newIndex}][category_id]" class="form-control category-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="items[${newIndex}][product_id]" class="form-control product-select" required disabled>
                        <option value="">Select Product</option>
                    </select>
                </td>
                <td class="colors-container"></td>
                <td>
                    <input type="number" name="items[${newIndex}][price]" class="form-control price-input" step="0.01" readonly>
                </td>
                <td>
                    <input type="number" name="items[${newIndex}][gst_rate]" class="form-control gst-input" step="0.01" readonly>
                </td>
                <td class="text-right">
                    <strong class="row-total">₹0.00</strong>
                </td>
                <td>
                    <button type="button" class="remove-btn" onclick="removeProduct(${newIndex})" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#items-table tbody').append(newRowHtml);
        updateNoProductsMessage();
        productIndex++;
    });

    // Update product select change handler to use product id instead of name
    $(document).on('change', '.product-select', function() {
        var $row = $(this).closest('tr');
        var productId = $(this).val();

        if (!productId) {
            $row.find('.colors-container').empty();
            $row.find('.price-input').val('');
            $row.find('.gst-input').val('');
            $row.find('.row-total').text('₹0.00');
            updateTotals();
            return;
        }

        // Fetch product variants by product id
        $.get('/api/products/variants/' + productId)
            .done(function(data) {
                if (data.variants && data.variants.length > 0) {
                    // Recreate colors container
                    var colorsHtml = '';
                    data.variants.forEach(function(variant, index) {
                        var colorName = variant.color || 'No Color';
                        var stockInfo = getStockInfo(variant.quantity);
                        var colorBadgeClass = getColorBadgeClass(colorName);
                        colorsHtml += `
                            <div class="color-item mb-2">
                                <div class="row align-items-center">
                                    <div class="col-3">
                                        <span class="badge ${colorBadgeClass}">${colorName}</span>
                                    </div>
                                    <div class="col-3">
                                        <input type="number" name="items[${$row.data('product-index')}][colors][${index}][quantity]" 
                                               class="form-control form-control-sm quantity-input" 
                                               min="0" max="${variant.quantity}" value="0" 
                                               data-max-stock="${variant.quantity}"
                                               onchange="validateQuantity(this); updateTotals();"
                                               placeholder="Qty">
                                        <input type="hidden" name="items[${$row.data('product-index')}][colors][${index}][product_id]" value="${variant.id}">
                                    </div>
                                    <div class="col-6">
                                        <small class="stock-info">${stockInfo}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    $row.find('.colors-container').html(colorsHtml);
                    $row.find('.price-input').val(data.variants[0].price);
                    $row.find('.gst-input').val(data.variants[0].gst_rate);
                    $row.find('.row-total').text('₹0.00');
                    updateTotals();
                }
            })
            .fail(function() {
                alert('Error loading product variants');
            });
    });

    window.removeProduct = function(index) {
        $(`.product-row[data-product-index="${index}"]`).remove();
        updateNoProductsMessage();
        updateTotals();
    };

    window.validateQuantity = function(input) {
        var quantity = parseInt(input.value) || 0;
        var maxStock = parseInt(input.getAttribute('data-max-stock')) || 0;
        
        if (quantity > maxStock) {
            input.value = maxStock;
            alert(`Maximum available quantity for this color is ${maxStock}`);
        }
    };

    function updateNoProductsMessage() {
        if ($('.product-row').length === 0) {
            $('#no-products-message').show();
        } else {
            $('#no-products-message').hide();
        }
    }

    function updateTotals() {
        var grandSubtotal = 0;
        var grandCgst = 0;
        var grandSgst = 0;

        $('.product-row').each(function() {
            var row = $(this);
            var price = parseFloat(row.find('.price-input').val()) || 0;
            var gst_rate = parseFloat(row.find('.gst-input').val()) || 0;
            var rowTotal = 0;

            // Calculate total quantity and amount for this row
            row.find('.quantity-input').each(function() {
                var qty = parseInt($(this).val()) || 0;
                rowTotal += qty * price;
            });

            var gstAmount = (rowTotal * gst_rate) / 100;
            
            row.find('.row-total').text('₹' + rowTotal.toFixed(2));
            
            grandSubtotal += rowTotal;
            grandCgst += gstAmount / 2;
            grandSgst += gstAmount / 2;
        });

        var grand_total = grandSubtotal + grandCgst + grandSgst;

        $('#subtotal').text('₹' + grandSubtotal.toFixed(2));
        $('#cgst').text('₹' + grandCgst.toFixed(2));
        $('#sgst').text('₹' + grandSgst.toFixed(2));
        $('#grand_total').text('₹' + grand_total.toFixed(2));
    }

    // Customer selection
    $('#customer_id').on('change', function() {
        var selected = $(this).find('option:selected');
        if(selected.val()) {
            var details = selected.data('details');
            $('#cust-address').text(details.address);
            $('#cust-gstin').text(details.gstin);
            $('#customer-details').show();
        } else {
            $('#customer-details').hide();
        }
    });

    // Form submission validation
    $('#invoice-form').on('submit', function(e) {
        var hasItems = false;
        $('.quantity-input').each(function() {
            if (parseInt($(this).val()) > 0) {
                hasItems = true;
                return false;
            }
        });

        if (!hasItems) {
            e.preventDefault();
            alert('Please add at least one item with quantity greater than 0');
        }
    });

    function getColorBadgeClass(colorName) {
        const colorClasses = {
            'black': 'badge-dark',
            'red': 'badge-danger',
            'blue': 'badge-primary',
            'white': 'badge-light',
            'green': 'badge-success',
            'yellow': 'badge-warning',
            'silver': 'badge-secondary',
            'golden': 'badge-warning',
            'clear': 'badge-info'
        };
        return colorClasses[colorName?.toLowerCase()] || 'badge-secondary';
    }

    function getStockInfo(quantity) {
        if (quantity <= 0) {
            return '<span class="stock-warning">Out of Stock</span>';
        } else if (quantity <= 10) {
            return '<span class="stock-low">Stock: ' + quantity + '</span>';
        } else {
            return '<span>Stock: ' + quantity + '</span>';
        }
    }

    // Event handler for category select change to update product dropdown
    $(document).on('change', '.category-select', function() {
        var $row = $(this).closest('tr');
        var categoryId = $(this).val();
        var $productSelect = $row.find('.product-select');

        if (!categoryId) {
            $productSelect.html('<option value="">Select Product</option>');
            $productSelect.prop('disabled', true);
            $row.find('.colors-container').empty();
            $row.find('.price-input').val('');
            $row.find('.gst-input').val('');
            $row.find('.row-total').text('₹0.00');
            updateTotals();
            return;
        }

        // Fetch products by category
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        $.get('/api/products/by-category', { category_id: categoryId })
            .done(function(products) {
                var options = '<option value="">Select Product</option>';
                products.forEach(function(product) {
                    options += `<option value="${product.id}">${product.name}</option>`;
                });
                $productSelect.html(options);
                $productSelect.prop('disabled', false);
                $row.find('.colors-container').empty();
                $row.find('.price-input').val('');
                $row.find('.gst-input').val('');
                $row.find('.row-total').text('₹0.00');
                updateTotals();
            })
            .fail(function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error loading products for selected category: ' + error);
            });
    });
});
</script>
@endpush
