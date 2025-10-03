@extends('layouts.admin')

@section('title', 'Create Non-GST Invoice')

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

/* Compact table rows */
.product-row td {
    padding: 0.375rem 0.5rem !important;
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
    pointer-events: auto;
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
    pointer-events: auto;
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
<li class="breadcrumb-item"><a href="{{ route('invoices.non_gst.index') }}">Non-GST Invoices</a></li>
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
    <form action="{{ route('invoices.non_gst.store') }}" method="POST" id="invoice-form">
        @csrf
        
        <!-- Hidden GST rate field for Non-GST invoices (always 0) -->
        <input type="hidden" name="gst_rate" value="0">
        
        <!-- Invoice Header -->
        <div class="card mb-4 form-step" id="step-1">
            <div class="card-header">
                <h4>
                    <i class="fas fa-file-invoice"></i> Non-GST Invoice Details
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
                                <select name="customer_id" id="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                                data-address="{{ $customer->address }}" 
                                                data-mobile="{{ $customer->mobile }}"
                                                data-email="{{ $customer->email }}"
                                                {{ isset($orderData) && $orderData['customer_id'] == $customer->id ? 'selected' : '' }}>
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
                        <div id="customer-details" style="display: none;">
                            <div class="alert alert-info">
                                <strong>Address:</strong> <span id="cust-address"></span><br>
                                <strong>Mobile:</strong> <span id="cust-mobile"></span>
                            </div>
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
                                <th>Packaging Fees:</th>
                                <td class="text-right">
                                    <input type="number" name="packaging_fees" id="packaging_fees" class="form-control form-control-sm text-right"
                                           placeholder="0.00" min="0" step="0.01" value="0" style="max-width: 100px; display: inline-block;">
                                </td>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                resolve(dataCache[cacheKey]);
                return;
            }
            
            // Show skeleton loader
            const $target = $(`[data-cache-key="${cacheKey}"]`);
            if ($target.length) {
                showSkeletonLoader($target, cacheKey);
            }
            
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
            $('#cust-mobile').text(selected.data('mobile') || '');
            $('#customer-details').show();
        } else {
            $('#customer-details').hide();
        }
    });
    
    
    
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
        
        $productSelect.prop('disabled', true);
        $productSelect.attr('data-cache-key', 'products');
        
        // Use cached API call
        cachedApiCall('/api/products/by-category', { category_id: categoryId }, 'products')
            .then(function(products) {
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
            .catch(function() {
                $productSelect.html('<option value="">Error loading products</option>');
                showError('Error loading products. Please try again.');
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
        
        $row.find('.colors-container').attr('data-cache-key', 'variants');
        
        // Use cached API call for variants
        cachedApiCall(`/api/products/variants/${productId}`, {}, 'variants')
            .then(function(data) {
                if (data.variants && data.variants.length > 0) {
                    createColorInputs($row, data.variants);
                    
                    const firstVariant = data.variants[0];
                    const $priceInput = $row.find('.price-input');
                    
                    $priceInput.val(firstVariant.price);
                    $priceInput.attr('data-original-price', firstVariant.price);
                    
                    $row.find('.original-price').text(parseFloat(firstVariant.price).toFixed(2));
                    $row.find('.price-history').show();
                    
                    makePriceEditable($priceInput);
                    
                }
            })
            .catch(function() {
                showError('Error loading product variants. Please try again.');
            })
            .finally(function() {
                $row.removeClass('loading');
            });
    });
    
    // Update variant prices when user changes item price
    $(document).on('input change', '.price-input', function() {
        const $row = $(this).closest('tr');
        const newPrice = $(this).val() || 0;
        
        // Update all hidden price fields for this item's variants
        $row.find('input[name*="[price]"]').val(newPrice);
        
        console.log('Updated variant prices to:', newPrice);
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
    
    // Clear field with Escape key
    $(document).on('keydown', '.price-input', function(e) {
        if (e.key === 'Escape') {
            $(this).val('').blur();
        }
    });
    
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
        const itemPrice = $row.find('.price-input').val() || 0;
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
                    <input type="hidden" name="items[${index}][variants][${variantIndex}][price]" value="${itemPrice}">
                    <input type="hidden" name="items[${index}][variants][${variantIndex}][gst_rate]" value="0">
                    <div class="stock-info ${stockClass}">Stock: ${variant.quantity}</div>
                </div>
            `;
            
            // ✅ REMOVED: Component information is not needed for invoice creation
            // Components are already consumed during assembly
            // Users only need to see composite product stock
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

        const packagingFees = parseFloat($('#packaging_fees').val()) || 0;
        const grandTotal = grandSubtotal - discountAmount + packagingFees;

        $('#subtotal').text('₹' + grandSubtotal.toFixed(2));
        $('#discount_amount_display').text('₹' + discountAmount.toFixed(2));
        $('#grand_total').text('₹' + grandTotal.toFixed(2));
        
        setTimeout(function() {
        }, 10);
        
        return grandTotal;
    };
    
    $('#discount_type, #discount_value, #packaging_fees').on('change keyup', function() {
        updateTotals();
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
        console.log('Non-GST Form submission started');
        console.log('Form data:', $(this).serialize());
        
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
    
    loadOrderData();
    
    addNewItem();
    
    // Track added products to prevent duplicates
    const addedProducts = new Set();
    
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
            
            if (productId && checkDuplicateProduct(productId, $row)) {
                $row.addClass('duplicate-product');
                $row.find('.product-select').addClass('is-invalid');
                $row.find('.product-select').siblings('.invalid-feedback').text('This product from this category is already added to the invoice').show();
                showError('Product from this category already exists in the invoice. Please select a different product or category.', 'warning');
            } else {
                $row.removeClass('duplicate-product');
                $row.find('.product-select').removeClass('is-invalid');
                $row.find('.product-select').siblings('.invalid-feedback').hide();
            }
        });
    }
    
    setupDuplicatePrevention();
    
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