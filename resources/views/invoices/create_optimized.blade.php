@extends('layouts.admin')

@section('title', 'Create Invoice')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />
<style>
/* Compact Spacing - Reduced Form Spacing */
.invoice-form {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px;
}

/* Reduce card spacing */
.card {
    margin-bottom: 1rem !important;
}

.card-body {
    padding: 1rem !important;
}

.card-header {
    padding: 0.75rem 1rem !important;
}

/* Reduce form group spacing */
.form-group {
    margin-bottom: 0.75rem !important;
}

/* Reduce row spacing */
.row {
    margin-bottom: 0.5rem !important;
}

/* Reduce table spacing */
.table td {
    padding: 0.5rem !important;
    vertical-align: middle;
}

.table th {
    padding: 0.5rem !important;
}

/* Reduce input group spacing */
.input-group {
    margin-bottom: 0.25rem !important;
}

.input-group-append {
    margin-left: 0 !important;
}

/* Reduce button spacing */
.btn {
    padding: 0.375rem 0.75rem !important;
    margin: 0.125rem !important;
}

/* Reduce alert spacing */
.alert {
    padding: 0.5rem 0.75rem !important;
    margin-bottom: 0.5rem !important;
}

/* Reduce label spacing */
label {
    margin-bottom: 0.25rem !important;
    font-size: 0.875rem;
}

/* Reduce small text spacing */
.form-text {
    margin-top: 0.125rem !important;
    margin-bottom: 0.25rem !important;
}

/* Reduce invalid feedback spacing */
.invalid-feedback {
    margin-top: 0.125rem !important;
}

/* Compact customer details */
#customer-details .alert {
    margin-bottom: 0.5rem !important;
    padding: 0.5rem !important;
}

/* Reduce section spacing */
.form-step {
    margin-bottom: 1rem !important;
}

/* Compact totals section */
.totals-section {
    padding: 0.75rem !important;
}

/* Reduce breadcrumb spacing */
.breadcrumb {
    margin-bottom: 0.5rem !important;
    padding: 0.25rem 0 !important;
}

/* Additional compact spacing for specific elements */
.col-md-3, .col-md-6, .col-sm-6 {
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
}

/* Compact form controls */
.form-control {
    padding: 0.375rem 0.5rem !important;
    font-size: 0.875rem;
}

/* Compact select2 */
.select2-container--default .select2-selection--single {
    height: 2.25rem !important;
    padding: 0.375rem 0.5rem !important;
}

/* Compact textarea */
textarea.form-control {
    padding: 0.375rem 0.5rem !important;
    font-size: 0.875rem;
}

/* Compact table rows */
.product-row td {
    padding: 0.375rem 0.5rem !important;
}

/* Compact mobile navigation */
.mobile-nav {
    padding: 0.5rem !important;
}

.mobile-nav .btn {
    margin: 0.125rem !important;
    padding: 0.25rem 0.5rem !important;
    font-size: 0.75rem;
}

/* Compact totals display */
.totals-row {
    padding: 0.25rem 0 !important;
}

.totals-row td {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem;
}

/* Compact action buttons */
.action-buttons .btn {
    margin: 0.125rem !important;
    padding: 0.25rem 0.5rem !important;
    font-size: 0.75rem;
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


/* Form Controls */
.form-control, .select2-container--default .select2-selection--single {
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    font-size: 0.95rem;
    pointer-events: auto; /* Ensure interactivity */
}

.form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
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
    border: 1px solid #007bff;
    border-radius: 6px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.select2-results__option {
    padding: 8px 12px;
    font-size: 0.95rem;
}

.select2-results__option--highlighted {
    background-color: #007bff !important;
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
    background: linear-gradient(135deg, #007bff, #0056b3);
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
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

.price-input {
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: all 0.3s ease;
}

.price-input:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15);
}

.price-input.editable {
    background: #fff3cd;
    border-color: #ffc107;
}

.price-input.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    animation: shake 0.5s ease-in-out;
}

.price-input.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.price-input:valid {
    border-color: #28a745;
    background: #d4edda;
}

/* Price container styling */
.price-container {
    position: relative;
}

.price-history {
    display: block !important;
    margin-top: 0.25rem;
    font-size: 0.75rem;
}

.price-history i {
    margin-right: 0.25rem;
}

/* Shake animation for invalid price */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
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

/* Enhanced Mobile Responsive Design */
@media (max-width: 768px) {
    .invoice-form {
        padding: 10px;
    }

    .quick-actions {
        right: 10px;
        gap: 8px;
        bottom: 20px;
        top: auto;
        transform: none;
        flex-direction: row;
        flex-wrap: wrap;
        max-width: calc(100vw - 20px);
    }

    .quick-action-btn {
        width: 44px;
        height: 44px;
        font-size: 1.1rem;
        min-width: 44px; /* Touch-friendly minimum size */
    }

    .table-responsive {
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .color-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 8px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        margin-bottom: 8px;
    }

    .quantity-input {
        width: 100%;
        max-width: 120px;
        min-height: 44px; /* Touch-friendly */
    }

    .form-control, .select2-container--default .select2-selection--single {
        min-height: 44px; /* Touch-friendly */
        font-size: 16px; /* Prevents zoom on iOS */
    }

    .btn {
        min-height: 44px;
        padding: 12px 16px;
    }

    .card-header h4 {
        font-size: 1.1rem;
    }

    .table th, .table td {
        padding: 8px 4px;
        font-size: 0.85rem;
    }

    .modal-dialog {
        margin: 10px;
        max-width: calc(100vw - 20px);
    }

    .modal-content {
        border-radius: 8px;
    }

    /* Mobile-specific improvements */
    .mobile-stack {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .mobile-full-width {
        width: 100% !important;
        max-width: none !important;
    }

    /* Touch gestures */
    .swipeable {
        touch-action: pan-x;
    }

    /* Mobile navigation */
    .mobile-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border-top: 1px solid #dee2e6;
        padding: 10px;
        z-index: 1000;
        display: flex;
        gap: 10px;
    }

    .mobile-nav .btn {
        flex: 1;
        min-height: 48px;
    }
}

/* Tablet optimizations */
@media (min-width: 769px) and (max-width: 1024px) {
    .invoice-form {
        padding: 20px;
    }

    .quick-actions {
        right: 15px;
        gap: 10px;
    }

    .quick-action-btn {
        width: 42px;
        height: 42px;
    }
}

/* Large screen optimizations */
@media (min-width: 1200px) {
    .invoice-form {
        max-width: 1400px;
        margin: 0 auto;
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
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Skeleton Loading */
.skeleton-loader {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 4px;
}

.skeleton-text {
    height: 16px;
    margin: 8px 0;
}

.skeleton-button {
    height: 32px;
    width: 80px;
    margin: 4px;
}

.skeleton-input {
    height: 38px;
    width: 100%;
    margin: 4px 0;
}

.skeleton-color-item {
    height: 40px;
    margin: 8px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.skeleton-color-badge {
    width: 60px;
    height: 24px;
    border-radius: 12px;
}

.skeleton-input-small {
    width: 80px;
    height: 32px;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Performance Optimizations */
.cached-data {
    opacity: 0.8;
    position: relative;
}

.cached-data::after {
    content: '📋';
    position: absolute;
    top: -5px;
    right: -5px;
    font-size: 12px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Duplicate Product Styling */
.duplicate-product {
    background-color: #fff3cd !important;
    border-left: 4px solid #ffc107 !important;
    animation: duplicateWarning 0.5s ease-in-out;
}

.duplicate-product .product-select {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
}

.duplicate-product .invalid-feedback {
    color: #856404 !important;
    font-weight: 500;
}

@keyframes duplicateWarning {
    0% { transform: translateX(-5px); }
    25% { transform: translateX(5px); }
    50% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
    100% { transform: translateX(0); }
}

/* Duplicate Product Indicator */
.duplicate-indicator {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #ffc107;
    color: #856404;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    z-index: 10;
}

.duplicate-indicator::before {
    content: '⚠';
}

/* Highlight existing product */
.highlight-existing {
    background-color: #d4edda !important;
    border-left: 4px solid #28a745 !important;
    animation: highlightPulse 1s ease-in-out;
}

@keyframes highlightPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}
</style>
@endpush

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">


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
    <form action="{{ $invoice_type === 'gst' ? route('invoices.gst.store') : route('invoices.non_gst.store') }}" method="POST" id="invoice-form">
        @csrf
        
        <!-- Hidden field to track order_id if invoice is created from an order -->
        @if(isset($order) && $order)
            <input type="hidden" name="order_id" value="{{ $order->id }}">
        @endif
        
        <!-- Invoice Header -->
        <div class="card mb-4 form-step" id="step-1">
            <div class="card-header">
                <h4>
                    <i class="fas fa-file-invoice"></i> Invoice Details
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control" value="{{ $invoice_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="invoice_date">Invoice Date</label>
                            <input type="date" name="invoice_date" class="form-control" 
                                   value="{{ isset($orderData) ? $orderData['invoice_date'] : date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" name="due_date" class="form-control" 
                                   value="{{ isset($orderData) ? $orderData['due_date'] : date('Y-m-d', strtotime('+30 days')) }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="customer_id">Customer *</label>
                            <div class="input-group">
                                <select name="customer_id" id="customer_id" class="form-control" required
                                        aria-label="Select customer for invoice" 
                                        aria-describedby="customer-error customer-help"
                                        role="combobox">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                                data-address="{{ $customer->address }}" 
                                                data-gstin="{{ $customer->gstin }}"
                                                data-mobile="{{ $customer->mobile }}"
                                                data-email="{{ $customer->email }}"
                                                {{ isset($orderData) && $orderData['customer_id'] == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-success" onclick="showCustomerModal()" 
                                            aria-label="Add new customer"
                                            title="Add New Customer">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <small id="customer-help" class="form-text text-muted">Choose the customer for this invoice</small>
                            <div class="invalid-feedback" id="customer-error" role="alert"></div>
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
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes...">{{ isset($orderData) ? $orderData['notes'] : '' }}</textarea>
                                </div>
                            </div>
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
                    <span class="float-right">
                        <small id="items-count">0 items</small>
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
                <div class="card form-step" id="step-3">
                    <div class="card-header bg-info">
                        <h4>
                            <i class="fas fa-calculator"></i> Invoice Summary
                        </h4>
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
                                <th>Packaging Fees:</th>
                                <td class="text-right">
                                    <input type="number" name="packaging_fees" id="packaging_fees" class="form-control form-control-sm text-right"
                                           placeholder="0.00" min="0" step="0.01" value="0" style="max-width: 100px; display: inline-block;">
                                </td>
                            </tr>
                            <tr>
                                <th>CGST (<span id="cgst-rate">9</span>%):</th>
                                <td class="text-right" id="cgst">₹0.00</td>
                            </tr>
                            <tr>
                                <th>SGST (<span id="sgst-rate">9</span>%):</th>
                                <td class="text-right" id="sgst">₹0.00</td>
                            </tr>
                            <tr class="border-top">
                                <th class="h5">Grand Total:</th>
                                <td class="text-right h5 font-weight-bold text-primary" id="grand_total">₹0.00</td>
                            </tr>
                        </table>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success btn-block btn-lg" id="submit-btn">
                                    <i class="fas fa-file-invoice"></i> Create Invoice
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
                                <label for="customer_gstin">GSTIN</label>
                                <input type="text" class="form-control" id="customer_gstin" name="gstin">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="customer_address">Address *</label>
                        <textarea class="form-control" id="customer_address" name="address" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="customer_state">State</label>
                        <input type="text" class="form-control" id="customer_state" name="state">
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
<!-- Modular JavaScript -->
<script src="{{ asset('js/invoice-validation.js') }}"></script>
<script src="{{ asset('js/invoice-performance.js') }}"></script>
<script src="{{ asset('js/invoice-accessibility.js') }}"></script>
<script src="{{ asset('js/invoice-main.js') }}"></script>
<script>
$(document).ready(function() {
    let itemIndex = 0;
    
    // Performance Optimization: Data Caching
    const dataCache = {
        products: new Map(),
        variants: new Map(),
        categories: new Map(),
        lastFetch: new Map()
    };
    
    // Cache TTL (Time To Live) - 5 minutes
    const CACHE_TTL = 5 * 60 * 1000;
    
    // Check if cached data is still valid
    function isCacheValid(key) {
        const lastFetch = dataCache.lastFetch.get(key);
        return lastFetch && (Date.now() - lastFetch) < CACHE_TTL;
    }
    
    // Show skeleton loader
    function showSkeletonLoader($container, type = 'default') {
        let skeletonHtml = '';
        
        switch(type) {
            case 'products':
                skeletonHtml = `
                    <div class="skeleton-loader skeleton-text"></div>
                    <div class="skeleton-loader skeleton-text" style="width: 80%;"></div>
                    <div class="skeleton-loader skeleton-text" style="width: 60%;"></div>
                `;
                break;
            case 'variants':
                skeletonHtml = `
                    <div class="skeleton-color-item">
                        <div class="skeleton-loader skeleton-color-badge"></div>
                        <div class="skeleton-loader skeleton-input-small"></div>
                        <div class="skeleton-loader skeleton-text" style="width: 50px;"></div>
                    </div>
                    <div class="skeleton-color-item">
                        <div class="skeleton-loader skeleton-color-badge"></div>
                        <div class="skeleton-loader skeleton-input-small"></div>
                        <div class="skeleton-loader skeleton-text" style="width: 50px;"></div>
                    </div>
                `;
                break;
            default:
                skeletonHtml = `
                    <div class="skeleton-loader skeleton-text"></div>
                    <div class="skeleton-loader skeleton-text" style="width: 70%;"></div>
                `;
        }
        
        $container.html(skeletonHtml);
    }
    
    // Optimized API call with caching
    function cachedApiCall(url, params, cacheKey) {
        return new Promise((resolve, reject) => {
            // Check cache first
            if (dataCache[cacheKey] && isCacheValid(cacheKey)) {
                console.log(`Using cached data for ${cacheKey}`);
                performanceMonitor.logCacheHit();
                resolve(dataCache[cacheKey]);
                return;
            }
            
            // Show skeleton loader
            const $target = $(`[data-cache-key="${cacheKey}"]`);
            if ($target.length) {
                showSkeletonLoader($target, cacheKey);
            }
            
            // Log API call
            performanceMonitor.logApiCall();
            
            // Make API call
            $.get(url, params)
                .done(function(data) {
                    // Cache the data
                    dataCache[cacheKey] = data;
                    dataCache.lastFetch.set(cacheKey, Date.now());
                    resolve(data);
                })
                .fail(function(xhr) {
                    console.error(`API call failed for ${cacheKey}:`, xhr);
                    reject(xhr);
                });
        });
    }
    
    // Lazy loading for non-critical features
    const lazyLoadFeatures = {
        customerModal: false,
        advancedValidation: false,
        keyboardShortcuts: false
    };
    
    // Initialize lazy features when needed
    function initializeLazyFeature(feature) {
        if (lazyLoadFeatures[feature]) return;
        
        switch(feature) {
            case 'customerModal':
                // Load customer modal functionality only when first accessed
                console.log('Loading customer modal functionality...');
                lazyLoadFeatures.customerModal = true;
                break;
                
            case 'advancedValidation':
                // Load advanced validation only when form is being submitted
                console.log('Loading advanced validation...');
                lazyLoadFeatures.advancedValidation = true;
                break;
                
            case 'keyboardShortcuts':
                // Load keyboard shortcuts only when user starts typing
                console.log('Loading keyboard shortcuts...');
                lazyLoadFeatures.keyboardShortcuts = true;
                break;
        }
    }
    
    // Performance monitoring
    const performanceMonitor = {
        startTime: Date.now(),
        apiCalls: 0,
        cacheHits: 0,
        
        logApiCall: function() {
            this.apiCalls++;
        },
        
        logCacheHit: function() {
            this.cacheHits++;
        },
        
        getStats: function() {
            const totalTime = Date.now() - this.startTime;
            return {
                totalTime: totalTime,
                apiCalls: this.apiCalls,
                cacheHits: this.cacheHits,
                cacheHitRate: this.cacheHits / (this.apiCalls + this.cacheHits) * 100
            };
        }
    };

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
            $('#cust-gstin').text(selected.data('gstin') || '');
            $('#customer-details').show();
        } else {
            $('#customer-details').hide();
        }
    });
    
    
    
    // Enhanced keyboard shortcuts with lazy loading
    $(document).on('keydown', function(e) {
        initializeLazyFeature('keyboardShortcuts');
        
        // Global shortcuts
        if (e.ctrlKey) {
            switch(e.key) {
                case 'i':
                case 'I':
                    e.preventDefault();
                    addNewItem();
                    announceToScreenReader('New item row added');
                    break;
                case 'u':
                case 'U':
                    e.preventDefault();
                    showCustomerModal();
                    announceToScreenReader('Customer modal opened');
                    break;
                case 's':
                case 'S':
                    e.preventDefault();
                    $('#invoice-form').submit();
                    announceToScreenReader('Form submitted');
                    break;
                case 'r':
                case 'R':
                    e.preventDefault();
                    resetForm();
                    announceToScreenReader('Form reset');
                    break;
            }
        }
        
        // Tab navigation enhancement
        if (e.key === 'Tab') {
            handleTabNavigation(e);
        }
        
        // Enter key handling for form elements
        if (e.key === 'Enter' && $(e.target).is('input, select, textarea')) {
            handleEnterKey(e);
        }
        
        // Escape key handling
        if (e.key === 'Escape') {
            handleEscapeKey(e);
        }
    });
    
    // Screen reader announcements
    function announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }
    
    // Enhanced tab navigation
    function handleTabNavigation(e) {
        const $currentElement = $(e.target);
        const $form = $('#invoice-form');
        
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
    function handleEnterKey(e) {
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
                    addNewItem();
                    setTimeout(() => {
                        $('.product-row').last().find('input, select, textarea').first().focus();
                    }, 100);
                }
            }
        }
    }
    
    // Handle Escape key
    function handleEscapeKey(e) {
        const $currentElement = $(e.target);
        
        // Close any open modals
        if ($('.modal').hasClass('show')) {
            $('.modal').modal('hide');
            announceToScreenReader('Modal closed');
        }
        
        // Clear current field if in input
        if ($currentElement.is('input, textarea')) {
            $currentElement.val('').trigger('change');
            announceToScreenReader('Field cleared');
        }
    }
    
    // Focus management for modals
    function setupModalFocusManagement() {
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
    
    // Form reset function
    function resetForm() {
        if (confirm('Are you sure you want to reset the form? All data will be lost.')) {
            $('#invoice-form')[0].reset();
            $('.product-row').not(':first').remove();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').hide();
            addedProducts.clear();
            updateProductTracking();
            announceToScreenReader('Form reset successfully');
        }
    }
    
    // Add item handlers
    $('#add-item-btn, #add-first-item').on('click', function() {
        addNewItem();
    });
    
    function addNewItem() {
        const rowHtml = `
            <tr class="product-row animate__animated animate__fadeIn" data-index="${itemIndex}">
                <td>
                    <select name="items[${itemIndex}][category_id]" class="form-control category-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </td>
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-control product-select" required disabled>
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
                               step="0.01" min="0.01" placeholder="Enter price" value="" data-original-price="">
                        <div class="invalid-feedback">Price must be greater than zero</div>
                        <small class="price-history text-muted" style="display: none;">
                            <i class="fas fa-history"></i> Original: ₹<span class="original-price">0.00</span>
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
        
        console.log('Category select changed, categoryId:', categoryId);
        
        if (!categoryId) {
            $productSelect.html('<option value="">Select Product</option>').prop('disabled', true);
            clearProductData($row);
            return;
        }
        
        $productSelect.prop('disabled', true);
        $productSelect.attr('data-cache-key', 'products');
        
        // Use cached API call
        cachedApiCall('/api/products/by-category', { category_id: categoryId }, 'products')
            .then(function(products) {
                console.log('Products loaded for category:', categoryId, 'products:', products);
                let options = '<option value="">Select Product</option>';
                products.forEach(function(product) {
                    const compositeBadge = product.is_composite ? '<span class="composite-badge">Composite</span>' : '';
                    options += `<option value="${product.id}" data-is-composite="${product.is_composite}">${product.name}${compositeBadge}</option>`;
                });
                $productSelect.html(options).prop('disabled', false);
                console.log('Product dropdown populated with', products.length, 'products');
                
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
            .catch(function(xhr) {
                console.error('Error loading products for category:', categoryId, xhr);
                let errorMessage = 'Error loading products';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $productSelect.html(`<option value="">${errorMessage}</option>`).prop('disabled', true);
                clearProductData($row);
                showError(errorMessage);
            });
    });
    
    $(document).on('change', '.product-select', function() {
        const $row = $(this).closest('tr');
        const productId = $(this).val();
        
        console.log('Product select changed, productId:', productId);
        
        if (!productId) {
            clearProductData($row);
            return;
        }
        
        $row.addClass('loading');
        $row.find('.colors-container').attr('data-cache-key', 'variants');
        
        // Use cached API call for variants
        cachedApiCall(`/api/products/variants/${productId}`, {}, 'variants')
            .then(function(data) {
                console.log('Product variants API response:', data);
                if (data.variants && data.variants.length > 0) {
                    console.log('Creating color inputs for variants:', data.variants);
                    createColorInputs($row, data.variants);
                    
                    const firstVariant = data.variants[0];
                    const $priceInput = $row.find('.price-input');
                    
                    $priceInput.val(firstVariant.price);
                    $priceInput.attr('data-original-price', firstVariant.price);
                    
                    $row.find('.original-price').text(parseFloat(firstVariant.price).toFixed(2));
                    $row.find('.price-history').show();
                    
                    makePriceEditable($priceInput);
                    
                } else {
                    console.log('No variants found for product:', productId);
                }
            })
            .catch(function() {
                console.log('Error loading product variants for product:', productId);
                showError('Error loading product variants. Please try again.');
            })
            .finally(function() {
                $row.removeClass('loading');
            });
    });
    
    // Enhanced price validation function
    function validatePriceInput($priceInput) {
        const price = parseFloat($priceInput.val()) || 0;
        const $row = $priceInput.closest('tr');
        const productId = $row.find('.product-select').val();
        
        // Only validate if a product is selected
        if (!productId) {
            $priceInput.removeClass('is-invalid');
            $priceInput.siblings('.invalid-feedback').hide();
            return true;
        }
        
        if (price <= 0) {
            $priceInput.addClass('is-invalid');
            $priceInput.siblings('.invalid-feedback').text('Price must be greater than zero').show();
            return false;
        } else if (price > 999999) {
            $priceInput.addClass('is-invalid');
            $priceInput.siblings('.invalid-feedback').text('Price seems too high. Please check the value.').show();
            return false;
        } else {
            $priceInput.removeClass('is-invalid');
            $priceInput.siblings('.invalid-feedback').hide();
            $priceInput.addClass('is-valid');
            return true;
        }
    }

    function makePriceEditable($priceInput) {
        $priceInput.removeClass('readonly').prop('readonly', false).addClass('editable');
        
        // Clear the field if it has default 0 value
        const currentValue = parseFloat($priceInput.val()) || 0;
        if (currentValue === 0) {
            $priceInput.val('');
        }
        
        $priceInput.on('click', function() {
            // Clear field if it's 0 or empty
            if (parseFloat($(this).val()) === 0 || $(this).val() === '') {
                $(this).val('');
            }
            $(this).select();
        });
        
        $priceInput.on('change keyup', function() {
            const originalPrice = parseFloat($(this).attr('data-original-price')) || 0;
            const currentPrice = parseFloat($(this).val()) || 0;
            
            // Validate price
            validatePriceInput($(this));
            
            if (currentPrice !== originalPrice && currentPrice > 0) {
                $(this).addClass('editable');
                $(this).closest('tr').find('.price-history').addClass('text-warning');
            } else if (currentPrice === 0) {
                // Hide history if price is cleared
                $(this).closest('tr').find('.price-history').hide();
            } else {
                $(this).removeClass('editable');
                $(this).closest('tr').find('.price-history').removeClass('text-warning');
            }
            
            updateTotals();
        });
    }
    
    function showError(message, type = 'error', duration = 5000) {
        const alertClass = type === 'error' ? 'alert-danger' : type === 'warning' ? 'alert-warning' : 'alert-info';
        const icon = type === 'error' ? 'fas fa-exclamation-triangle' : type === 'warning' ? 'fas fa-exclamation-circle' : 'fas fa-info-circle';
        
        const errorHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="${icon}"></i> <strong>${type === 'error' ? 'Error!' : type === 'warning' ? 'Warning!' : 'Info!'}</strong> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Remove existing alerts of same type
        $(`.alert-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'}`).remove();
        
        $('body').append(errorHtml);
        
        // Auto-hide after duration
        setTimeout(function() {
            $(`.alert-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'}`).alert('close');
        }, duration);
        
        // Scroll to top if error
        if (type === 'error') {
            $('html, body').animate({ scrollTop: 0 }, 300);
        }
    }
    
    // Update variant prices when user changes item price
    $(document).on('input change', '.price-input', function() {
        const $row = $(this).closest('tr');
        const newPrice = $(this).val() || 0;
        
        // Update all hidden price fields for this item's variants
        $row.find('input[name*="[price]"]').val(newPrice);
        
        console.log('Updated variant prices to:', newPrice);
    });
    
    function createColorInputs($row, variants) {
        const index = $row.data('index');
        const itemPrice = $row.find('.price-input').val() || 0;
        const gstRate = $('#gst_rate').val() || 0;
        let html = '';
        
        console.log('Creating color inputs for row index:', index, 'variants:', variants);
        console.log('Item price:', itemPrice, 'GST rate:', gstRate);
        
        variants.forEach(function(variant, variantIndex) {
            const colorName = variant.color || 'Default';
            const stockClass = getStockClass(variant.quantity);
            const colorStyle = getColorStyle(colorName);
            const isComposite = variant.is_composite;
            
            console.log(`Creating input for variant ${variant.id} (${colorName})`);
            
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
                    <input type="hidden" name="items[${index}][variants][${variantIndex}][price]" value="${itemPrice}">
                    <input type="hidden" name="items[${index}][variants][${variantIndex}][gst_rate]" value="${gstRate}">
                    <div class="stock-info ${stockClass}">Stock: ${variant.quantity}</div>
                </div>
            `;
            
            // ✅ REMOVED: Component information is not needed for invoice creation
            // Components are already consumed during assembly
            // Users only need to see composite product stock
        });
        
        console.log('Setting HTML for colors container:', html);
        $row.find('.colors-container').html(html);
        console.log('After setting HTML, hidden inputs count:', $row.find('input[type="hidden"]').length);
        console.log('After setting HTML, quantity inputs count:', $row.find('input[type="number"]').length);
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
        const $row = $(`.product-row[data-index="${index}"]`);
        $row.addClass('animate__animated animate__fadeOut');
        
        setTimeout(function() {
            $row.remove();
            if ($('#items-tbody tr').length === 0) {
                $('#no-items-message').show();
            }
            updateItemsCount();
            updateTotals();
        }, 500);
    };
    
    window.updateTotals = function() {
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

        const afterDiscount = grandSubtotal - discountAmount;
        const packagingFees = parseFloat($('#packaging_fees').val()) || 0;
        const afterPackaging = afterDiscount + packagingFees;
        
        const invoiceGstRate = parseFloat($('#gst_rate').val()) || 0;
        const totalGstAmount = (afterPackaging * invoiceGstRate) / 100;
        const cgstAmount = totalGstAmount / 2;
        const sgstAmount = totalGstAmount / 2;

        const grand_total = afterPackaging + cgstAmount + sgstAmount;

        $('#cgst-rate').text((invoiceGstRate / 2).toFixed(1));
        $('#sgst-rate').text((invoiceGstRate / 2).toFixed(1));

        $('#subtotal').text('₹' + grandSubtotal.toFixed(2));
        $('#discount_amount_display').text('₹' + discountAmount.toFixed(2));
        $('#after_discount').text('₹' + afterDiscount.toFixed(2));
        $('#cgst').text('₹' + cgstAmount.toFixed(2));
        $('#sgst').text('₹' + sgstAmount.toFixed(2));
        $('#grand_total').text('₹' + grand_total.toFixed(2));
        
        setTimeout(function() {
        }, 10);
        
        return grand_total;
    };
    
    $('#invoice-form').on('submit', function(e) {
        initializeLazyFeature('advancedValidation');
        
        console.log('Form submission started');
        console.log('Form data:', $(this).serialize());
        
        // Log performance stats before submission
        const perfStats = performanceMonitor.getStats();
        console.log('Performance Stats:', perfStats);
        
        // Enhanced validation before submission
        if (!validateForm()) {
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
        setTimeout(function() {
            if ($('#submit-btn').prop('disabled')) {
                $('#submit-btn').prop('disabled', false).html('<i class="fas fa-file-invoice"></i> Create Invoice');
                showError('Request timeout. Please try again.');
            }
        }, 30000); // 30 second timeout
    });
    
    $('#discount_type, #discount_value, #gst_rate, #packaging_fees').on('change keyup', function() {
        updateTotals();
    });
    
    window.showCustomerModal = function() {
        initializeLazyFeature('customerModal');
        $('#customerModal').modal('show');
        $('#customer_name').focus();
    };
    
    window.saveCustomer = function() {
        const formData = {
            name: $('#customer_name').val(),
            mobile: $('#customer_mobile').val(),
            email: $('#customer_email').val(),
            gstin: $('#customer_gstin').val(),
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
                    $(newOption).attr('data-gstin', response.customer.gstin);
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
    
    
    // Enhanced validation with real-time feedback
    function validateForm() {
        let isValid = true;
        let errorCount = 0;
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
        
        // Customer validation
        if (!$('#customer_id').val()) {
            $('#customer_id').addClass('is-invalid');
            $('#customer-error').text('Please select a customer').show();
            isValid = false;
            errorCount++;
        } else {
            $('#customer_id').removeClass('is-invalid');
            $('#customer-error').hide();
        }
        
        // Date validation
        const invoiceDate = $('input[name="invoice_date"]').val();
        const dueDate = $('input[name="due_date"]').val();
        
        if (!invoiceDate) {
            $('input[name="invoice_date"]').addClass('is-invalid');
            showError('Invoice date is required');
            isValid = false;
            errorCount++;
        }
        
        if (dueDate && new Date(dueDate) < new Date(invoiceDate)) {
            $('input[name="due_date"]').addClass('is-invalid');
            showError('Due date cannot be before invoice date');
            isValid = false;
            errorCount++;
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
            let totalQuantity = 0;
            
            // Count total items
            totalItems++;
            
            // Check quantities
            $row.find('.quantity-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                if (qty > 0) {
                    hasQuantity = true;
                    totalQuantity += qty;
                }
            });
            
            // Category validation
            if (!categoryId) {
                $row.find('.category-select').addClass('is-invalid');
                $row.find('.category-select').siblings('.invalid-feedback').text('Please select a category').show();
                isValid = false;
                errorCount++;
            } else {
                $row.find('.category-select').removeClass('is-invalid');
                $row.find('.category-select').siblings('.invalid-feedback').hide();
            }
            
            // Product validation
            if (!productId && categoryId) {
                $row.find('.product-select').addClass('is-invalid');
                $row.find('.product-select').siblings('.invalid-feedback').text('Please select a product').show();
                isValid = false;
                errorCount++;
            } else if (productId && categoryId) {
                // Check for duplicate products (same category + product combination)
                if (checkDuplicateProduct(productId, $row)) {
                    $row.find('.product-select').addClass('is-invalid');
                    $row.find('.product-select').siblings('.invalid-feedback').text('This product from this category is already added to the invoice').show();
                    isValid = false;
                    errorCount++;
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
                errorCount++;
            } else if (price > 0) {
                $row.find('.price-input').removeClass('is-invalid');
                $row.find('.price-input').siblings('.invalid-feedback').hide();
            }
            
            // Quantity validation
            if (productId && price > 0 && !hasQuantity) {
                $row.find('.quantity-input').first().addClass('is-invalid');
                $row.find('.quantity-input').first().siblings('.invalid-feedback').text('Please enter quantity').show();
                isValid = false;
                errorCount++;
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
                        errorCount++;
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
            showError('Please add at least one item to the invoice');
            isValid = false;
            errorCount++;
        } else if (!hasValidItems) {
            showError(`Please complete ${totalItems - validItems} incomplete item(s) with valid quantity`);
            isValid = false;
            errorCount++;
        }
        
        // Show validation summary
        if (!isValid && errorCount > 0) {
            showError(`Please fix ${errorCount} error(s) before submitting`);
        }
        
        return isValid;
    }
    
    // Track added products to prevent duplicates
    const addedProducts = new Set();
    
    // Real-time validation
    function setupRealTimeValidation() {
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
                showError('Due date cannot be before invoice date');
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
                // Check for duplicate products (same category + product combination)
                if (checkDuplicateProduct(productId, $row)) {
                    $row.find('.product-select').addClass('is-invalid');
                    $row.find('.product-select').siblings('.invalid-feedback').text('This product from this category is already added to the invoice').show();
                    showError('Product from this category already exists in the invoice. Please select a different product or category.', 'warning');
                } else {
                    $row.find('.product-select').removeClass('is-invalid');
                    $row.find('.product-select').siblings('.invalid-feedback').hide();
                    // Add to tracking set with category + product combination
                    const uniqueKey = `${categoryId}-${productId}`;
                    addedProducts.add(uniqueKey);
                }
            }
        });
        
        // Enhanced price validation and formatting
        $(document).on('input change blur', '.price-input', function() {
            const $input = $(this);
            const price = parseFloat($input.val()) || 0;
            const $row = $input.closest('tr');
            const productId = $row.find('.product-select').val();
            
            // Format price on blur (when user finishes typing)
            if ($input.is(':focus') === false && price > 0) {
                $input.val(price.toFixed(2));
            }
            
            // Validate price
            validatePriceInput($input);
            
            // Update totals if price is valid
            if (price > 0 && productId) {
                updateTotals();
            }
        });
        
        // Enhanced price input behavior
        $(document).on('focus', '.price-input', function() {
            const $input = $(this);
            const currentValue = parseFloat($input.val()) || 0;
            
            // If price is 0 or empty, clear the field for new input
            if (currentValue === 0 || $input.val() === '') {
                $input.val('');
            }
            
            // Select all text for easy editing
            setTimeout(() => {
                $input.select();
            }, 10);
        });
        
        // Clear price when user starts typing if it's 0
        $(document).on('input', '.price-input', function() {
            const $input = $(this);
            const value = $input.val();
            
            // If user types and current value is 0, clear it
            if (value === '0' || value === '0.0' || value === '0.00') {
                $input.val('');
            }
        });
        
        // Double-click to clear price field completely
        $(document).on('dblclick', '.price-input', function() {
            $(this).val('').focus();
        });
        
        // Add clear button functionality
        $(document).on('keydown', '.price-input', function(e) {
            // Clear field with Escape key
            if (e.key === 'Escape') {
                $(this).val('').blur();
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
    
    // Check for duplicate products
    function checkDuplicateProduct(productId, currentRow) {
        if (!productId) return false;
        
        const currentCategoryId = currentRow.find('.category-select').val();
        if (!currentCategoryId) return false; // Can't check duplicate without category
        
        let isDuplicate = false;
        $('.product-row').each(function() {
            const $row = $(this);
            const rowProductId = $row.find('.product-select').val();
            const rowCategoryId = $row.find('.category-select').val();
            
            // Skip current row and check others
            // Check for duplicate only if both category AND product are the same
            if ($row[0] !== currentRow[0] && 
                rowProductId === productId && 
                rowCategoryId === currentCategoryId) {
                isDuplicate = true;
                return false; // Break the loop
            }
        });
        
        return isDuplicate;
    }
    
    // Update product tracking when rows are removed
    function updateProductTracking() {
        addedProducts.clear();
        $('.product-row').each(function() {
            const productId = $(this).find('.product-select').val();
            const categoryId = $(this).find('.category-select').val();
            if (productId && categoryId) {
                // Use category + product combination as unique key
                const uniqueKey = `${categoryId}-${productId}`;
                addedProducts.add(uniqueKey);
            }
        });
    }
    
    // Enhanced addNewItem function with duplicate prevention
    function addNewItemWithValidation() {
        // Check if we can add more items (optional limit)
        const maxItems = 50; // Set a reasonable limit
        const currentItems = $('.product-row').length;
        
        if (currentItems >= maxItems) {
            showError(`Maximum ${maxItems} items allowed per invoice`, 'warning');
            return;
        }
        
        addNewItem();
    }
    
    // Show duplicate product suggestions
    function showDuplicateSuggestions(productId, productName) {
        const suggestions = `
            <div class="duplicate-suggestions" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 400px;">
                <h5><i class="fas fa-exclamation-triangle text-warning"></i> Duplicate Product Detected</h5>
                <p>The product "<strong>${productName}</strong>" is already added to this invoice.</p>
                <div class="mt-3">
                    <button class="btn btn-warning btn-sm mr-2" onclick="mergeDuplicateProduct('${productId}')">
                        <i class="fas fa-plus"></i> Merge Quantities
                    </button>
                    <button class="btn btn-secondary btn-sm mr-2" onclick="closeDuplicateSuggestions()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        `;
        
        $('body').append(suggestions);
        
        // Close on outside click
        setTimeout(() => {
            $(document).on('click', closeDuplicateSuggestions);
        }, 100);
    }
    
    function closeDuplicateSuggestions() {
        $('.duplicate-suggestions').remove();
        $(document).off('click', closeDuplicateSuggestions);
    }
    
    function mergeDuplicateProduct(productId) {
        // Find existing row with this product
        let existingRow = null;
        $('.product-row').each(function() {
            if ($(this).find('.product-select').val() === productId) {
                existingRow = $(this);
                return false;
            }
        });
        
        if (existingRow) {
            // Focus on the existing row
            existingRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            existingRow.addClass('highlight-existing');
            setTimeout(() => {
                existingRow.removeClass('highlight-existing');
            }, 2000);
            
            showError('Please update the quantity in the existing row instead of adding duplicates', 'info', 3000);
        }
        
        closeDuplicateSuggestions();
    }
    
    // Setup duplicate product prevention
    function setupDuplicatePrevention() {
        // Update tracking when rows are removed
        $(document).on('click', '.remove-item', function() {
            setTimeout(() => {
                updateProductTracking();
            }, 100);
        });
        
        // Update tracking when product selection changes
        $(document).on('change', '.product-select', function() {
            setTimeout(() => {
                updateProductTracking();
            }, 100);
        });
        
        // Add visual indicator for duplicate products
        $(document).on('change', '.product-select', function() {
            const $row = $(this).closest('tr');
            const productId = $(this).val();
            const categoryId = $row.find('.category-select').val();
            
            if (productId && categoryId && checkDuplicateProduct(productId, $row)) {
                $row.addClass('duplicate-product');
                $row.find('.product-select').addClass('is-invalid');
            } else {
                $row.removeClass('duplicate-product');
            }
        });
        
        // Also check for duplicates when category changes
        $(document).on('change', '.category-select', function() {
            const $row = $(this).closest('tr');
            const productId = $row.find('.product-select').val();
            const categoryId = $(this).val();
            
            if (productId && categoryId && checkDuplicateProduct(productId, $row)) {
                $row.addClass('duplicate-product');
                $row.find('.product-select').addClass('is-invalid');
                $row.find('.product-select').siblings('.invalid-feedback').text('This product from this category is already added to the invoice').show();
            } else {
                $row.removeClass('duplicate-product');
                $row.find('.product-select').removeClass('is-invalid');
                $row.find('.product-select').siblings('.invalid-feedback').hide();
            }
        });
    }
    
    // Mobile optimizations
    function setupMobileOptimizations() {
        // Detect mobile device
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 768;
        
        if (isMobile) {
            // Add mobile navigation
            addMobileNavigation();
            
            // Setup touch gestures
            setupTouchGestures();
            
            // Optimize form inputs for mobile
            optimizeMobileInputs();
            
            // Add mobile-specific event handlers
            setupMobileEventHandlers();
        }
    }
    
    function addMobileNavigation() {
        const mobileNav = `
            <div class="mobile-nav d-md-none">
                <button type="button" class="btn btn-primary" onclick="addNewItem()">
                    <i class="fas fa-plus"></i> Add Item
                </button>
                <button type="button" class="btn btn-success" onclick="showCustomerModal()">
                    <i class="fas fa-user-plus"></i> Customer
                </button>
                <button type="submit" class="btn btn-warning" form="invoice-form">
                    <i class="fas fa-file-invoice"></i> Create
                </button>
            </div>
        `;
        
        $('body').append(mobileNav);
        
        // Adjust body padding for mobile nav
        $('body').css('padding-bottom', '80px');
    }
    
    function setupTouchGestures() {
        let startX, startY, endX, endY;
        
        // Swipe to delete items
        $('.product-row').on('touchstart', function(e) {
            startX = e.originalEvent.touches[0].clientX;
            startY = e.originalEvent.touches[0].clientY;
        });
        
        $('.product-row').on('touchend', function(e) {
            endX = e.originalEvent.changedTouches[0].clientX;
            endY = e.originalEvent.changedTouches[0].clientY;
            
            const diffX = startX - endX;
            const diffY = startY - endY;
            
            // Swipe left to delete
            if (Math.abs(diffX) > Math.abs(diffY) && diffX > 50) {
                if (confirm('Delete this item?')) {
                    $(this).remove();
                    updateItemsCount();
                    updateTotals();
                }
            }
        });
        
        // Long press for context menu
        let longPressTimer;
        $('.product-row').on('touchstart', function() {
            longPressTimer = setTimeout(() => {
                showMobileContextMenu($(this));
            }, 500);
        });
        
        $('.product-row').on('touchend touchmove', function() {
            clearTimeout(longPressTimer);
        });
    }
    
    function optimizeMobileInputs() {
        // Prevent zoom on input focus (iOS)
        $('input, select, textarea').attr('autocomplete', 'off');
        
        // Add mobile-friendly input types
        $('input[type="number"]').attr('inputmode', 'numeric');
        $('input[type="tel"]').attr('inputmode', 'tel');
        $('input[type="email"]').attr('inputmode', 'email');
        
        // Optimize Select2 for mobile
        $('.select2-container').addClass('mobile-optimized');
    }
    
    function setupMobileEventHandlers() {
        // Double tap to add item
        let lastTap = 0;
        $('.quick-action-btn').on('touchend', function(e) {
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTap;
            
            if (tapLength < 500 && tapLength > 0) {
                // Double tap detected
                addNewItem();
            }
            lastTap = currentTime;
        });
        
        // Pull to refresh
        let startY = 0;
        let currentY = 0;
        let isPulling = false;
        
        $(document).on('touchstart', function(e) {
            if (window.scrollY === 0) {
                startY = e.originalEvent.touches[0].clientY;
                isPulling = true;
            }
        });
        
        $(document).on('touchmove', function(e) {
            if (isPulling) {
                currentY = e.originalEvent.touches[0].clientY;
                const pullDistance = currentY - startY;
                
                if (pullDistance > 100) {
                    // Pull to refresh
                    location.reload();
                }
            }
        });
        
        $(document).on('touchend', function() {
            isPulling = false;
        });
    }
    
    function showMobileContextMenu($row) {
        const contextMenu = `
            <div class="mobile-context-menu" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 1px solid #ccc; border-radius: 8px; padding: 10px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <button class="btn btn-sm btn-danger btn-block mb-2" onclick="deleteRow(${Date.now()})">
                    <i class="fas fa-trash"></i> Delete Item
                </button>
                <button class="btn btn-sm btn-secondary btn-block" onclick="closeContextMenu()">
                    Cancel
                </button>
            </div>
        `;
        
        $('body').append(contextMenu);
        
        // Close on outside click
        setTimeout(() => {
            $(document).on('click', closeContextMenu);
        }, 100);
    }
    
    function closeContextMenu() {
        $('.mobile-context-menu').remove();
        $(document).off('click', closeContextMenu);
    }
    
    // ✅ REMOVED: Duplicate form submission handler
    // The first handler (lines 1281-1300) already handles form submission properly
    
    loadOrderData();
    
    addNewItem();
    
    // Initialize real-time validation
    setupRealTimeValidation();
    
    // Setup duplicate product prevention
    setupDuplicatePrevention();
    
    // Mobile optimizations
    setupMobileOptimizations();
    
    // Accessibility features
    setupModalFocusManagement();
    
    $('input, select, textarea').on('change keyup', function() {
    });
    
    // Load order data if available
    function loadOrderData() {
        @if(isset($orderData) && $orderData)
            const orderData = @json($orderData);
            console.log('Loading order data:', orderData);
            
            // Clear any existing items
            $('#items-tbody').empty();
            itemIndex = 0;
            
            // Load order items
            if (orderData.items && orderData.items.length > 0) {
                orderData.items.forEach(function(item, index) {
                    console.log(`Loading item ${index}:`, item);
                    addNewItem();
                    const $row = $('#items-tbody tr:last');
                    
                    // Set category
                    console.log(`Setting category ${item.category_id} for item ${index}`);
                    $row.find('.category-select').val(item.category_id).trigger('change');
                    
                    // Wait for products to load, then set product
                    setTimeout(function() {
                        console.log(`Setting product ${item.product_id} for item ${index}`);
                        console.log('Available products in dropdown:', $row.find('.product-select option').length);
                        console.log('Product options:', $row.find('.product-select option').map(function() { return $(this).val() + ': ' + $(this).text(); }).get());
                        
                        // Check if the product exists in the dropdown
                        const productExists = $row.find('.product-select option[value="' + item.product_id + '"]').length > 0;
                        console.log('Product exists in dropdown:', productExists);
                        
                        if (productExists) {
                            $row.find('.product-select').val(item.product_id).trigger('change');
                        } else {
                            console.log('Product not found in dropdown, retrying...');
                            // Retry after a longer delay
                            setTimeout(function() {
                                console.log('Retry: Setting product', item.product_id);
                                $row.find('.product-select').val(item.product_id).trigger('change');
                            }, 1000);
                        }
                        
                        // Wait for variants to load, then set quantities
                        setTimeout(function() {
                            console.log(`Checking if variants loaded for item ${index}`);
                            console.log('Available hidden inputs:', $row.find('input[type="hidden"]').length);
                            console.log('Available quantity inputs:', $row.find('input[type="number"]').length);
                            if (item.variants && item.variants.length > 0) {
                                console.log(`Setting variants for item ${index}:`, item.variants);
                                
                                // Function to set quantities with retry mechanism
                                function setQuantities(retryCount = 0) {
                                    let allFound = true;
                                    
                                    item.variants.forEach(function(variant) {
                                        // Find the quantity input by looking for the hidden input with the variant ID
                                        const $hiddenInput = $row.find(`input[type="hidden"][value="${variant.product_id}"]`);
                                        console.log(`Looking for variant ${variant.product_id}, found:`, $hiddenInput.length);
                                        
                                        if ($hiddenInput.length) {
                                            const $quantityInput = $hiddenInput.siblings('input[type="number"]');
                                            console.log(`Setting quantity ${variant.quantity} for variant ${variant.product_id}`);
                                            if ($quantityInput.length) {
                                                $quantityInput.val(variant.quantity);
                                            }
                                        } else {
                                            allFound = false;
                                        }
                                    });
                                    
                                    // If not all variants found and we haven't exceeded retry limit, try again
                                    if (!allFound && retryCount < 5) {
                                        console.log(`Retrying to find variants (attempt ${retryCount + 1})`);
                                        setTimeout(function() {
                                            setQuantities(retryCount + 1);
                                        }, 500);
                                    } else if (!allFound) {
                                        console.log('Could not find all variants after retries');
                                    }
                                }
                                
                                setQuantities();
                            }
                            
                            // Set price
                            $row.find('.price-input').val(item.price);
                            
                            updateTotals();
                        }, 1500);
                    }, 1000);
                });
            }
            
            // Show customer details if customer is selected
            if (orderData.customer_id) {
                $('#customer_id').trigger('change');
            }
        @endif
    }
});
</script>
@endpush