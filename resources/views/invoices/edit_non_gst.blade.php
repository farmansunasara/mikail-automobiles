@extends('layouts.admin')

@section('title', 'Edit Non-GST Invoice')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />
<style>
/* General Styling */
.invoice-form {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Card Styling */
.card {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
}

.card-header {
    border-radius: 10px 10px 0 0;
    padding: 1.5rem;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    font-weight: 500;
}

.card-header h4 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Progress Bar */
.progress-bar-custom {
    height: 6px;
    background: linear-gradient(90deg, #28a745, #17a2b8);
    border-radius: 3px;
    margin-bottom: 25px;
    transition: width 0.4s ease;
}

/* Form Controls */
.form-control, .select2-container--default .select2-selection--single {
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    font-size: 0.95rem;
    pointer-events: auto; /* Ensure interactivity */
}

.form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15);
}

.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 6px 12px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px;
}

.select2-dropdown {
    border: 1px solid #28a745;
    border-radius: 6px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.select2-results__option {
    padding: 8px 12px;
    font-size: 0.95rem;
}

.select2-results__option--highlighted {
    background-color: #28a745 !important;
    color: white !important;
}

/* Buttons */
.btn {
    border-radius: 6px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: background 0.3s ease, transform 0.2s ease;
    pointer-events: auto; /* Ensure buttons are clickable */
}

.btn-primary {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    border: none;
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    border: none;
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8, #117a8b);
    border: none;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-outline-secondary {
    border-color: #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

/* Table Styling */
.table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.table th {
    background: #f8f9fa;
    font-weight: 500;
    color: #343a40;
}

.table td {
    vertical-align: middle;
    padding: 0.75rem;
}

.product-row {
    transition: background 0.3s ease;
}

.product-row:hover {
    background: #f1f3f5;
}

/* Color Items */
.color-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 10px;
    transition: transform 0.3s ease;
}

.color-item:hover {
    transform: translateY(-2px);
}

.color-badge {
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 0.85rem;
    color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.quantity-input {
    width: 80px;
    text-align: center;
    border-radius: 6px;
    border: 1px solid #ced4da;
}

.quantity-input:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15);
}

.price-input {
    border-radius: 6px;
    border: 1px solid #ced4da;
}

.price-input:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15);
}

.price-input.editable {
    background: #fff3cd;
}

.price-input.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.price-input.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Stock Information */
.stock-info {
    font-size: 0.85rem;
    color: #6c757d;
}

.stock-warning {
    color: #dc3545;
    animation: pulse 2s infinite;
}

.stock-low {
    color: #ffc107;
}

.stock-good {
    color: #28a745;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.6; }
    100% { opacity: 1; }
}

/* Quick Actions */
.quick-actions {
    position: fixed;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 12px;
    z-index: 1000;
}

.quick-action-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.quick-action-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Modal Styling */
.modal-content {
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.modal-header {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
    border-radius: 10px 10px 0 0;
}

/* Form Validation */
.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    font-size: 0.85rem;
    color: #dc3545;
}

/* Responsive Design */
@media (max-width: 768px) {
    .invoice-form {
        padding: 15px;
    }

    .quick-actions {
        right: 10px;
        gap: 8px;
    }

    .quick-action-btn {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .table-responsive {
        margin-bottom: 20px;
    }

    .color-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .quantity-input {
        width: 100%;
        max-width: 100px;
    }
}

/* Loading State */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #28a745;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoices.non_gst.index') }}">Non-GST Invoices</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Progress Bar -->
<div class="progress-bar-custom" id="progress-bar" style="width: 100%;"></div>

<!-- Quick Actions -->
<div class="quick-actions">
    <button type="button" class="quick-action-btn bg-primary" onclick="addNewItem()" title="Add Item (Ctrl+I)">
        <i class="fas fa-plus"></i>
    </button>
    <button type="button" class="quick-action-btn bg-success" onclick="showCustomerModal()" title="Add Customer (Ctrl+U)">
        <i class="fas fa-user-plus"></i>
    </button>
</div>

<div class="invoice-form">
    <form action="{{ route('invoices.non_gst.update', $invoice) }}" method="POST" id="invoice-form">
        @csrf
        @method('PUT')
        
        <!-- Invoice Header -->
        <div class="card mb-4 form-step" id="step-1">
            <div class="card-header">
                <h4>
                    <i class="fas fa-file-invoice"></i> Invoice Details
                    <span class="badge badge-light ml-2" id="step-indicator-1">Step 1 of 3</span>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control" value="{{ $invoice->invoice_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="invoice_date">Invoice Date</label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="customer_id">Customer *</label>
                            <div class="input-group">
                                <select name="customer_id" id="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                                {{ $customer->id == $invoice->customer_id ? 'selected' : '' }}
                                                data-address="{{ $customer->address }}" 
                                                data-mobile="{{ $customer->mobile }}"
                                                data-email="{{ $customer->email }}">
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-success" onclick="showCustomerModal()" title="Add New Customer">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="customer-error"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div id="customer-details">
                            <div class="alert alert-info">
                                <strong>Address:</strong> <span id="cust-address">{{ $invoice->customer->address }}</span><br>
                                <strong>Mobile:</strong> <span id="cust-mobile">{{ $invoice->customer->mobile ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes...">{{ $invoice->notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="card mb-4 form-step" id="step-2">
            <div class="card-header bg-success">
                <h4>
                    <i class="fas fa-shopping-cart"></i> Invoice Items
                    <span class="badge badge-light ml-2" id="step-indicator-2">Step 2 of 3</span>
                    <span class="float-right">
                        <small id="items-count">{{ $invoice->items->count() }} items</small>
                    </span>
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="items-table">
                        <thead class="thead-light">
                            <tr>
                                <th width="180px">Category</th>
                                <th width="180px">Product</th>
                                <th>Colors & Quantities</th>
                                <th width="120px">Price</th>
                                <th width="100px">Total</th>
                                <th width="50px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            @php
                                $groupedItems = $invoice->items->groupBy('product_id');
                                $index = 0;
                            @endphp
                            @foreach($groupedItems as $productId => $items)
                            @php
                                $productData = $productVariants[$productId];
                                $allVariants = $productData['variants'];
                                $existingQuantities = $productData['existing_quantities'];
                            @endphp
                            <tr class="product-row animate__animated animate__fadeIn" data-index="{{ $index }}">
                                <td>
                                    <select name="items[{{ $index }}][category_id]" class="form-control category-select" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ $items->first()->product->category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </td>
                                <td>
                                    <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                                        <option value="">Select Product</option>
                                        <option value="{{ $items->first()->product->id }}" 
                                                data-is-composite="{{ $items->first()->product->is_composite ? 'true' : 'false' }}"
                                                selected>{{ $items->first()->product->name }}</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </td>
                                <td class="colors-container">
                                    @foreach($allVariants as $variant)
                                    @php
                                        $existingQty = $existingQuantities[$variant->id] ?? 0;
                                        $totalStock = $variant->quantity + $existingQty;
                                        $stockClass = $totalStock > 10 ? 'stock-good' : ($totalStock > 5 ? 'stock-low' : 'stock-warning');
                                    @endphp
                                    <div class="color-item">
                                        <input type="hidden" name="items[{{ $index }}][variants][{{ $loop->index }}][product_id]" value="{{ $variant->id }}">
                                        @php
                                            $colorHex = $variant->colorModel ? $variant->colorModel->hex_code : \App\Helpers\ColorHelper::getColorCode($variant->color);
                                            $textColor = $variant->colorModel ? (\App\Helpers\ColorHelper::getTextColor($variant->color)) : '#ffffff';
                                        @endphp
                                        <div class="color-badge" style="background-color: {{ $colorHex }}; color: {{ $textColor }}; padding: 3px 8px; border-radius: 8px; font-size: 0.8rem; display: inline-block; margin-right: 5px;">
                                            {{ $variant->color }}
                                        </div>
                                        <span class="stock-info {{ $stockClass }}">({{ $totalStock }})</span>
                                        <input type="number" class="quantity-input" 
                                               name="items[{{ $index }}][variants][{{ $loop->index }}][quantity]" 
                                               value="{{ $existingQty }}" 
                                               min="0" max="{{ $totalStock }}" 
                                               data-max="{{ $totalStock }}"
                                               placeholder="0"
                                               onchange="calculateTotals()"
                                               data-is-composite="{{ $variant->product->is_composite ? 'true' : 'false' }}">
                                    </div>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="price-container">
                                        <input type="number" name="items[{{ $index }}][price]" class="form-control price-input editable" 
                                               step="0.01" min="0.01" value="{{ $items->first()->price }}" data-original-price="{{ $items->first()->price }}">
                                        <div class="invalid-feedback">Price must be greater than zero</div>
                                        <small class="price-history text-muted" style="display: {{ $items->first()->price ? 'block' : 'none' }};">
                                            Original: ₹<span class="original-price">{{ number_format($items->first()->price, 2) }}</span>
                                        </small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <strong class="row-total">₹{{ number_format($items->sum(function($item) { return $item->quantity * $item->price; }), 2) }}</strong>
                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-sm btn-outline-info mb-1" onclick="duplicateItem({{ $index }})" title="Duplicate Item">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-btn" onclick="removeItem({{ $index }})" title="Remove Item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @php $index++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-primary" id="add-item-btn">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
                
                <div id="no-items-message" class="text-center py-4" style="display:{{ $invoice->items->isEmpty() ? 'block' : 'none' }};">
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
                <div class="card form-step" id="step-3">
                    <div class="card-header bg-info">
                        <h4>
                            <i class="fas fa-calculator"></i> Invoice Summary
                            <span class="badge badge-light ml-2" id="step-indicator-3">Step 3 of 3</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th>Subtotal:</th>
                                <td class="text-right" id="subtotal">₹{{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Discount:</th>
                                <td class="text-right">
                                    <div class="input-group input-group-sm">
                                        <select name="discount_type" id="discount_type" class="form-control" style="max-width: 80px;">
                                            <option value="0" {{ $invoice->discount_type == 0 ? 'selected' : '' }}>₹</option>
                                            <option value="1" {{ $invoice->discount_type == 1 ? 'selected' : '' }}>%</option>
                                        </select>
                                        <input type="number" name="discount_value" id="discount_value" class="form-control"
                                               placeholder="0" min="0" step="0.01" value="{{ $invoice->discount_value }}" style="max-width: 80px;">
                                    </div>
                                    <small class="text-muted" id="discount_amount_display">₹{{ number_format($invoice->discount_amount, 2) }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Packaging Fees:</th>
                                <td class="text-right">
                                    <input type="number" name="packaging_fees" id="packaging_fees" class="form-control form-control-sm text-right"
                                           placeholder="0.00" min="0" step="0.01" value="{{ $invoice->packaging_fees ?? 0 }}" style="max-width: 100px; display: inline-block;">
                                </td>
                            </tr>
                            <tr class="border-top">
                                <th class="h5">Grand Total:</th>
                                <td class="text-right h5 font-weight-bold text-success" id="grand_total">₹{{ number_format($invoice->grand_total, 2) }}</td>
                            </tr>
                        </table>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success btn-block btn-lg" id="submit-btn">
                                    <i class="fas fa-file-invoice"></i> Update Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Customer Quick Add Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">
                    <i class="fas fa-user-plus"></i> Add New Customer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customer-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_name">Name *</label>
                                <input type="text" class="form-control" id="customer_name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_mobile">Mobile *</label>
                                <input type="text" class="form-control" id="customer_mobile" name="mobile">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_email">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="email">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_state">State</label>
                                <input type="text" class="form-control" id="customer_state" name="state">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="customer_address">Address *</label>
                        <textarea class="form-control" id="customer_address" name="address" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveCustomer()">
                    <i class="fas fa-save"></i> Save Customer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Ensure jQuery is loaded -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap and AdminLTE dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let itemIndex = {{ $invoice->items->groupBy('product_id')->count() }};

    // Initialize Select2 with error handling
    try {
        $('#customer_id').select2({
            placeholder: 'Select Customer',
            allowClear: true,
            templateResult: formatCustomer,
            templateSelection: formatCustomerSelection
        });
    } catch (e) {
        console.error('Select2 initialization failed:', e);
    }
    
    // Customer dropdown formatting
    function formatCustomer(customer) {
        if (!customer.id) return customer.text;
        
        return $(
            '<div class="customer-option">' +
                '<div class="customer-name">' + customer.text + '</div>' +
                '<div class="customer-details"><small class="text-muted">' + 
                ($(customer.element).data('mobile') || 'No mobile') + '</small></div>' +
            '</div>'
        );
    }
    
    function formatCustomerSelection(customer) {
        return customer.text;
    }
    
    // Customer selection handler
    $('#customer_id').on('change', function() {
        const selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#cust-address').text(selected.data('address') || '');
            $('#cust-mobile').text(selected.data('mobile') || '');
            $('#customer-details').show();
            updateProgress();
        } else {
            $('#customer-details').hide();
        }
    });
    
    // Progress tracking
    function updateProgress() {
        let progress = 25;
        if ($('#customer_id').val()) progress += 25;
        if ($('#items-tbody tr').length > 0) progress += 25;
        
        let grandTotal = 0;
        $('.product-row').each(function() {
            const $row = $(this);
            const price = parseFloat($row.find('.price-input').val()) || 0;
            $row.find('.quantity-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                grandTotal += qty * price;
            });
        });
        
        if (grandTotal > 0) progress += 25;
        
        $('#progress-bar').css('width', progress + '%');
        
        if (progress >= 50) $('#step-1').addClass('completed');
        if (progress >= 75) $('#step-2').addClass('completed');
        if (progress >= 100) $('#step-3').addClass('completed');
    }
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        if (e.ctrlKey) {
            switch(e.key) {
                case 'i':
                case 'I':
                    e.preventDefault();
                    addNewItem();
                    break;
                case 'u':
                case 'U':
                    e.preventDefault();
                    showCustomerModal();
                    break;
            }
        }
    });
    
    // Add item handlers
    $('#add-item-btn, #add-first-item').on('click', function() {
        addNewItem();
    });
    
    function addNewItem() {
        const rowHtml = `
            <tr class="product-row animate__animated animate__fadeIn" data-index="${itemIndex}">
                <td>
                    <select name="items[${itemIndex}][category_id]" class="form-control billing_category_select category-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </td>
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-control billing_product_select product-select" required disabled>
                        <option value="">Select Product</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </td>
                <td class="colors-container">
                    <div class="text-muted">Select a product first</div>
                </td>
                <td>
                    <div class="price-container">
                        <input type="number" name="items[${itemIndex}][price]" class="form-control price-input" 
                               step="0.01" min="0.01" readonly data-original-price="0">
                        <div class="invalid-feedback">Price must be greater than zero</div>
                        <small class="price-history text-muted" style="display: none;">
                            Original: ₹<span class="original-price">0.00</span>
                        </small>
                    </div>
                </td>
                <td class="text-right">
                    <strong class="row-total">₹0.00</strong>
                </td>
                <td>
                    <div class="btn-group-vertical">
                        <button type="button" class="btn btn-sm btn-outline-info mb-1" onclick="duplicateItem(${itemIndex})" title="Duplicate Item">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-btn" onclick="removeItem(${itemIndex})" title="Remove Item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        
        $('#items-tbody').append(rowHtml);
        $('#no-items-message').hide();
        
        try {
            initializeCategorySelect($(`#items-tbody tr:last .category-select`));
        } catch (e) {
            console.error('Category select initialization failed:', e);
        }
        
        updateItemsCount();
        updateProgress();

        itemIndex++;
    }
    
    function updateItemsCount() {
        const count = $('#items-tbody tr').length;
        $('#items-count').text(count + (count === 1 ? ' item' : ' items'));
    }
    
    window.duplicateItem = function(index) {
        const $originalRow = $(`.product-row[data-index="${index}"]`);
        const categoryId = $originalRow.find('.category-select').val();
        const productId = $originalRow.find('.product-select').val();
        const price = $originalRow.find('.price-input').val();
        
        if (!categoryId || !productId) {
            alert('Please complete the original item first');
            return;
        }
        
        addNewItem();
        
        const $newRow = $('#items-tbody tr:last');
        $newRow.find('.category-select').val(categoryId).trigger('change');
        
        setTimeout(function() {
            $newRow.find('.product-select').val(productId).trigger('change');
            setTimeout(function() {
                if (price) {
                    $newRow.find('.price-input').val(price);
                }
            }, 500);
        }, 500);
    };
    
    function initializeCategorySelect($select) {
        try {
            $select.select2({
                placeholder: 'Search category...',
                allowClear: true,
                width: '100%'
            });
        } catch (e) {
            console.error('Select2 initialization for category failed:', e);
        }
    }

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
                
                try {
                    $productSelect.select2({
                        placeholder: 'Search product...',
                        allowClear: true,
                        width: '100%'
                    });
                } catch (e) {
                    console.error('Select2 initialization for product failed:', e);
                }
            })
            .fail(function() {
                $productSelect.html('<option value="">Error loading products</option>');
                alert('Error loading products. Please try again.');
            });
    });
    
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
                    
                    const firstVariant = data.variants[0];
                    const $priceInput = $row.find('.price-input');
                    
                    $priceInput.val(firstVariant.price);
                    $priceInput.attr('data-original-price', firstVariant.price);
                    
                    $row.find('.original-price').text(parseFloat(firstVariant.price).toFixed(2));
                    $row.find('.price-history').show();
                    
                    makePriceEditable($priceInput);
                    
                    updateProgress();

                }
            })
            .fail(function() {
                showError('Error loading product variants. Please try again.');
            })
            .always(function() {
                $row.removeClass('loading');
            });
    });
    
    // Price validation function
    function validatePriceInput($priceInput) {
        const price = parseFloat($priceInput.val()) || 0;
        
        if (price <= 0) {
            $priceInput.addClass('is-invalid');
            $priceInput.siblings('.invalid-feedback').show();
            return false;
        } else {
            $priceInput.removeClass('is-invalid');
            $priceInput.siblings('.invalid-feedback').hide();
            return true;
        }
    }

    function makePriceEditable($priceInput) {
        $priceInput.removeClass('readonly').prop('readonly', false).addClass('editable');
        
        $priceInput.on('click', function() {
            $(this).select();
        });
        
        $priceInput.on('change keyup', function() {
            const originalPrice = parseFloat($(this).attr('data-original-price')) || 0;
            const currentPrice = parseFloat($(this).val()) || 0;
            
            // Validate price
            validatePriceInput($(this));
            
            if (currentPrice !== originalPrice) {
                $(this).addClass('editable');
                $(this).closest('tr').find('.price-history').addClass('text-warning');
            } else {
                $(this).removeClass('editable');
                $(this).closest('tr').find('.price-history').removeClass('text-warning');
            }
            
            calculateTotals();

        });
    }
    
    function showError(message) {
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <strong>Error!</strong> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        $('body').append(errorHtml);
        
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }
    
    function createColorInputs($row, variants) {
        const index = $row.data('index');
        let html = '';
        
        variants.forEach(function(variant, variantIndex) {
            const colorName = variant.color || 'Default';
            const stockClass = getStockClass(variant.quantity);
            const colorStyle = getColorStyle(colorName);
            
            html += `
                <div class="color-item">
                    <div class="color-badge" style="${colorStyle}">${colorName}</div>
                    <input type="number" 
                           name="items[${index}][variants][${variantIndex}][quantity]" 
                           class="quantity-input" 
                           min="0" 
                           max="${variant.quantity}" 
                           value="0" 
                           placeholder="0"
                           onchange="calculateTotals()">
                    <input type="hidden" name="items[${index}][variants][${variantIndex}][product_id]" value="${variant.id}">
                    <span class="stock-info ${stockClass}">(${variant.quantity})</span>
                </div>
            `;
        });
        
        $row.find('.colors-container').html(html);
        calculateTotals();
    }
    
    function clearProductData($row) {
        $row.find('.colors-container').html('<div class="text-muted">Select a product first</div>');
        $row.find('.price-input').val('');
        $row.find('.row-total').text('₹0.00');
        calculateTotals();
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
        const $row = $(`.product-row[data-index="${index}"]`);
        $row.addClass('animate__animated animate__fadeOut');
        
        setTimeout(function() {
            $row.remove();
            if ($('#items-tbody tr').length === 0) {
                $('#no-items-message').show();
            }
            updateItemsCount();
            calculateTotals();
            updateProgress();

        }, 500);
    };
    
    function calculateTotals() {
        let grandSubtotal = 0;
        
        $('.product-row').each(function() {
            const $row = $(this);
            const price = parseFloat($row.find('.price-input').val()) || 0;
            let rowTotal = 0;
            
            // Validate price input
            const $priceInput = $row.find('.price-input');
            if ($priceInput.length && !$priceInput.prop('readonly')) {
                validatePriceInput($priceInput);
            }
            
            $row.find('.quantity-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                rowTotal += qty * price;
            });
            
            $row.find('.row-total').text('₹' + rowTotal.toFixed(2));
            grandSubtotal += rowTotal;
        });

        const discountType = parseFloat($('#discount_type').val()) || 0;
        const discountValue = parseFloat($('#discount_value').val()) || 0;
        let discountAmount = 0;

        if (discountValue > 0) {
            if (discountType === 1) {
                discountAmount = (grandSubtotal * discountValue) / 100;
            } else {
                discountAmount = Math.min(discountValue, grandSubtotal);
            }
        }

        const packagingFees = parseFloat($('#packaging_fees').val()) || 0;
        const grand_total = grandSubtotal - discountAmount + packagingFees;

        $('#subtotal').text('₹' + grandSubtotal.toFixed(2));
        $('#discount_amount_display').text('₹' + discountAmount.toFixed(2));
        $('#grand_total').text('₹' + grand_total.toFixed(2));
        
        setTimeout(function() {
            updateProgress();
        }, 10);
        
        return grand_total;
    }
    
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
        
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    });
    
    $('#discount_type, #discount_value, #packaging_fees').on('change keyup', function() {
        calculateTotals();
    });
    
    window.showCustomerModal = function() {
        $('#customerModal').modal('show');
        $('#customer_name').focus();
    };
    
    window.saveCustomer = function() {
        const formData = {
            name: $('#customer_name').val(),
            mobile: $('#customer_mobile').val(),
            email: $('#customer_email').val(),
            address: $('#customer_address').val(),
            state: $('#customer_state').val(),
            _token: $('meta[name="csrf-token"]').attr('content') || ''
        };
        
        if (!formData.name) {
            showError('Please fill name required fields');
            return;
        }
        
        $.ajax({
            url: '/customers',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    const newOption = new Option(
                        response.customer.name, 
                        response.customer.id, 
                        true, 
                        true
                    );
                    
                    $(newOption).attr('data-address', response.customer.address);
                    $(newOption).attr('data-mobile', response.customer.mobile);
                    $(newOption).attr('data-email', response.customer.email);
                    
                    $('#customer_id').append(newOption).trigger('change');
                    
                    $('#customerModal').modal('hide');
                    $('#customer-form')[0].reset();
                    
                    showSuccess('Customer added successfully!');
                } else {
                    showError('Error adding customer: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error adding customer';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                }
                showError(errorMessage);
            }
        });
    };
    
    function showSuccess(message) {
        const successHtml = `
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <strong>Success!</strong> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        $('body').append(successHtml);
        
        setTimeout(function() {
            $('.alert-success').alert('close');
        }, 3000);
    }
    
    function validateForm() {
        let isValid = true;
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        if (!$('#customer_id').val()) {
            $('#customer_id').addClass('is-invalid');
            $('#customer-error').text('Please select a customer');
            isValid = false;
        }
        
        let hasValidItems = false;
        $('.product-row').each(function() {
            const $row = $(this);
            const categoryId = $row.find('.category-select').val();
            const productId = $row.find('.product-select').val();
            const price = parseFloat($row.find('.price-input').val()) || 0;
            let hasQuantity = false;
            
            $row.find('.quantity-input').each(function() {
                if (parseInt($(this).val()) > 0) {
                    hasQuantity = true;
                }
            });
            
            if (!categoryId) {
                $row.find('.category-select').addClass('is-invalid');
                $row.find('.category-select').siblings('.invalid-feedback').text('Please select a category');
                isValid = false;
            }
            
            if (!productId && categoryId) {
                $row.find('.product-select').addClass('is-invalid');
                $row.find('.product-select').siblings('.invalid-feedback').text('Please select a product');
                isValid = false;
            }
            
            if (price <= 0 && productId) {
                $row.find('.price-input').addClass('is-invalid');
                $row.find('.price-input').siblings('.invalid-feedback').show().text('Price must be greater than zero');
                isValid = false;
            } else {
                $row.find('.price-input').removeClass('is-invalid');
                $row.find('.price-input').siblings('.invalid-feedback').hide();
            }
            
            if (hasQuantity && categoryId && productId && price > 0) {
                hasValidItems = true;
            }
        });
        
        if (!hasValidItems) {
            showError('Please add at least one item with valid quantity');
            isValid = false;
        }
        
        return isValid;
    }
    
    $('#invoice-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return false;
        }
        
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating Invoice...');
        
        this.submit();
    });
    
    // Initialize existing selects
    $('.category-select').each(function() {
        initializeCategorySelect($(this));
    });
    
    $('.product-select').each(function() {
        try {
            $(this).select2({
                placeholder: 'Search product...',
                allowClear: true,
                width: '100%'
            });
        } catch (e) {
            console.error('Select2 initialization for product failed:', e);
        }
    });
    
    $('.price-input').each(function() {
        makePriceEditable($(this));
    });
    
    updateProgress();
    calculateTotals();
    
    // Calculate totals on page load
    calculateTotals();
    
    // Auto-calculate when inputs change
    $(document).on('input', '.quantity-input, .price-input, #discount_value', calculateTotals);
    $(document).on('change', '#discount_type', calculateTotals);
});
</script>
@endpush
