@extends('layouts.admin')

@section('title', 'Create Invoice')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />
<style>
.invoice-form {
    max-width: 1200px;
    margin: 0 auto;
}

/* Enhanced Form Styling */
.form-step {
    transition: all 0.3s ease;
}

.form-step.completed {
    opacity: 0.8;
}

.progress-bar-custom {
    height: 4px;
    background: linear-gradient(90deg, #28a745 0%, #20c997 50%, #17a2b8 100%);
    border-radius: 2px;
    margin-bottom: 20px;
    transition: width 0.3s ease;
}

/* Enhanced Color Badges */
.color-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    color: white;
    font-size: 0.8em;
    margin-right: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.color-badge:hover {
    transform: scale(1.05);
}

/* Enhanced Input Styling */
.quantity-input {
    width: 80px;
    text-align: center;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: border-color 0.3s ease;
}

.quantity-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.price-input {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: border-color 0.3s ease;
    background-color: #fff;
}

.price-input:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40,167,69,.25);
}

.price-input.editable {
    background-color: #fff3cd;
    border-color: #ffc107;
}

/* Stock Information */
.stock-info {
    font-size: 0.75em;
    color: #6c757d;
    font-weight: 500;
}
.stock-warning { 
    color: #dc3545; 
    animation: pulse 2s infinite;
}
.stock-low { 
    color: #ffc107; 
    font-weight: bold;
}
.stock-good { 
    color: #28a745; 
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

/* Enhanced Product Row */
.product-row {
    border-bottom: 1px solid #dee2e6;
    transition: background-color 0.3s ease;
    position: relative;
}

.product-row:hover {
    background-color: #f8f9fa;
}

.product-row.animate__animated {
    animation-duration: 0.5s;
}

/* Enhanced Remove Button */
.remove-btn {
    border: none;
    background: none;
    color: #dc3545;
    font-size: 1.2em;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.remove-btn:hover {
    background-color: #dc3545;
    color: white;
    transform: scale(1.1);
}

/* Enhanced Color Items */
.color-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    padding: 8px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
}

.color-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    transform: translateY(-1px);
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
    position: relative;
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

/* Composite Product Styling */
.composite-badge {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.7em;
    margin-left: 8px;
    box-shadow: 0 2px 4px rgba(40,167,69,0.3);
}

.component-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-top: 8px;
    font-size: 0.85em;
}

.component-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px solid #e9ecef;
}

.component-item:last-child {
    border-bottom: none;
}

.component-stock-warning {
    color: #dc3545;
    font-weight: bold;
    animation: pulse 2s infinite;
}

/* Enhanced Cards */
.card {
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    transition: all 0.3s ease;
    border: none;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: none;
    padding: 1.25rem 1.5rem;
}

/* Enhanced Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    border: none;
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
    border: none;
}

/* Quick Actions */
.quick-actions {
    position: fixed;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quick-action-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: none;
    color: white;
    font-size: 1.2em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.quick-action-btn:hover {
    transform: scale(1.1);
}

/* Customer Quick Add Modal */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
    border-radius: 12px 12px 0 0;
}

/* Price History Tooltip */
.price-history {
    position: relative;
    cursor: help;
}

.price-history-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.8em;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
}

.price-history:hover .price-history-tooltip {
    opacity: 1;
    visibility: visible;
}

/* Auto-save Indicator */
.auto-save-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.8em;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1000;
}

.auto-save-indicator.show {
    opacity: 1;
}

/* Responsive Design */
@media (max-width: 768px) {
    .invoice-form {
        margin: 0 10px;
    }
    
    .color-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .quantity-input {
        width: 100%;
        max-width: 120px;
    }
    
    .quick-actions {
        display: none;
    }
}

/* Form Validation */
.is-invalid {
    border-color: #dc3545 !important;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.8em;
    margin-top: 4px;
}
</style>
@endpush

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<!-- Auto-save Indicator -->
<div class="auto-save-indicator" id="auto-save-indicator">
    <i class="fas fa-save"></i> Draft saved
</div>

<!-- Progress Bar -->
<div class="progress-bar-custom" id="progress-bar" style="width: 25%;"></div>

<!-- Quick Actions -->
<div class="quick-actions">
    <button type="button" class="quick-action-btn bg-primary" onclick="addNewItem()" title="Add Item (Ctrl+I)">
        <i class="fas fa-plus"></i>
    </button>
    <button type="button" class="quick-action-btn bg-success" onclick="showCustomerModal()" title="Add Customer (Ctrl+U)">
        <i class="fas fa-user-plus"></i>
    </button>
    <button type="button" class="quick-action-btn bg-info" onclick="saveDraft()" title="Save Draft (Ctrl+S)">
        <i class="fas fa-save"></i>
    </button>
</div>

<div class="invoice-form">
    <form action="{{ route('invoices.gst.store') }}" method="POST" id="invoice-form">
        @csrf
        
        <!-- Invoice Header -->
        <div class="card mb-4 form-step" id="step-1">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-file-invoice"></i> Invoice Details
                    <span class="badge badge-light ml-2" id="step-indicator-1">Step 1 of 3</span>
                </h4>
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
                            <div class="input-group">
                                <select name="customer_id" id="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                                data-address="{{ $customer->address }}" 
                                                data-gstin="{{ $customer->gstin }}"
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
        <div class="card mb-4 form-step" id="step-2">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">
                    <i class="fas fa-shopping-cart"></i> Invoice Items
                    <span class="badge badge-light ml-2" id="step-indicator-2">Step 2 of 3</span>
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
                                <th width="120px">
                                    Price 
                                    <i class="fas fa-info-circle text-info" title="Click to edit price"></i>
                                </th>
                                <th width="100px">Total</th>
                                <th width="50px">
                                    <i class="fas fa-cog"></i>
                                </th>
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
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-calculator"></i> Invoice Summary
                            <span class="badge badge-light ml-2" id="step-indicator-3">Step 3 of 3</span>
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
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-secondary btn-block" onclick="saveDraft()">
                                    <i class="fas fa-save"></i> Save Draft
                                </button>
                            </div>
                            <div class="col-md-6">
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
            <div class="modal-header bg-success text-white">
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
                                <input type="text" class="form-control" id="customer_mobile" name="mobile" required>
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
                        <textarea class="form-control" id="customer_address" name="address" rows="3" required></textarea>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let itemIndex = 0;
    let draftTimer;
    let formData = {};
    
    // Initialize Select2
    $('#customer_id').select2({
        placeholder: 'Select Customer',
        allowClear: true,
        templateResult: formatCustomer,
        templateSelection: formatCustomerSelection
    });
    
    // Enhanced customer display in dropdown
    function formatCustomer(customer) {
        if (!customer.id) return customer.text;
        
        const $customer = $(
            '<div class="customer-option">' +
                '<div class="customer-name">' + customer.text + '</div>' +
                '<div class="customer-details"><small class="text-muted">' + 
                ($(customer.element).data('mobile') || 'No mobile') + '</small></div>' +
            '</div>'
        );
        return $customer;
    }
    
    function formatCustomerSelection(customer) {
        return customer.text;
    }
    
    // Customer selection handler
    $('#customer_id').on('change', function() {
        const selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#cust-address').text(selected.data('address'));
            $('#cust-gstin').text(selected.data('gstin'));
            $('#customer-details').show();
            updateProgress();
            saveDraftData();
        } else {
            $('#customer-details').hide();
        }
    });
    
    // Progress tracking
    function updateProgress() {
        let progress = 25; // Base progress for form load
        
        if ($('#customer_id').val()) progress += 25;
        if ($('#items-tbody tr').length > 0) progress += 25;
        
        // Calculate grand total without calling updateTotals to avoid circular reference
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
        
        // Update step indicators
        if (progress >= 50) $('#step-1').addClass('completed');
        if (progress >= 75) $('#step-2').addClass('completed');
        if (progress >= 100) $('#step-3').addClass('completed');
    }
    
    // Auto-save functionality
    function saveDraftData() {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(function() {
            const formData = {
                customer_id: $('#customer_id').val(),
                invoice_date: $('input[name="invoice_date"]').val(),
                due_date: $('input[name="due_date"]').val(),
                gst_rate: $('#gst_rate').val(),
                discount_type: $('#discount_type').val(),
                discount_value: $('#discount_value').val(),
                notes: $('textarea[name="notes"]').val(),
                items: []
            };
            
            $('.product-row').each(function() {
                const $row = $(this);
                const itemData = {
                    category_id: $row.find('.category-select').val(),
                    product_id: $row.find('.product-select').val(),
                    price: $row.find('.price-input').val(),
                    variants: []
                };
                
                $row.find('.quantity-input').each(function() {
                    if ($(this).val() > 0) {
                        itemData.variants.push({
                            product_id: $(this).siblings('input[type="hidden"]').val(),
                            quantity: $(this).val()
                        });
                    }
                });
                
                if (itemData.variants.length > 0) {
                    formData.items.push(itemData);
                }
            });
            
            localStorage.setItem('invoice_draft', JSON.stringify(formData));
            showAutoSaveIndicator();
        }, 2000);
    }
    
    function showAutoSaveIndicator() {
        $('#auto-save-indicator').addClass('show');
        setTimeout(function() {
            $('#auto-save-indicator').removeClass('show');
        }, 2000);
    }
    
    // Load draft data
    function loadDraftData() {
        const draft = localStorage.getItem('invoice_draft');
        if (draft) {
            try {
                const data = JSON.parse(draft);
                if (confirm('Found a saved draft. Would you like to restore it?')) {
                    // Restore basic form data
                    if (data.customer_id) $('#customer_id').val(data.customer_id).trigger('change');
                    if (data.invoice_date) $('input[name="invoice_date"]').val(data.invoice_date);
                    if (data.due_date) $('input[name="due_date"]').val(data.due_date);
                    if (data.gst_rate) $('#gst_rate').val(data.gst_rate);
                    if (data.discount_type) $('#discount_type').val(data.discount_type);
                    if (data.discount_value) $('#discount_value').val(data.discount_value);
                    if (data.notes) $('textarea[name="notes"]').val(data.notes);
                    
                    // Note: Item restoration would require more complex logic
                    // For now, we'll just restore the basic form fields
                }
            } catch (e) {
                console.error('Error loading draft:', e);
            }
        }
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
                case 's':
                case 'S':
                    e.preventDefault();
                    saveDraft();
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
                               step="0.01" readonly data-original-price="0">
                        <small class="price-history text-muted" style="display: none;">
                            Original: ₹<span class="original-price">0.00</span>
                            <div class="price-history-tooltip">Click to edit price</div>
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
                        <button type="button" class="remove-btn" onclick="removeItem(${itemIndex})" title="Remove Item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        
        $('#items-tbody').append(rowHtml);
        $('#no-items-message').hide();
        updateItemsCount();
        updateProgress();
        saveDraftData();
        itemIndex++;
    }
    
    // Update items count
    function updateItemsCount() {
        const count = $('#items-tbody tr').length;
        $('#items-count').text(count + (count === 1 ? ' item' : ' items'));
    }
    
    // Duplicate item functionality
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
        
        // Set the values for the new item
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
                    const $priceInput = $row.find('.price-input');
                    
                    $priceInput.val(firstVariant.price);
                    $priceInput.attr('data-original-price', firstVariant.price);
                    
                    // Show price history
                    $row.find('.original-price').text(parseFloat(firstVariant.price).toFixed(2));
                    $row.find('.price-history').show();
                    
                    // Make price editable
                    makePriceEditable($priceInput);
                    
                    updateProgress();
                    saveDraftData();
                }
            })
            .fail(function() {
                showError('Error loading product variants. Please try again.');
            })
            .always(function() {
                $row.removeClass('loading');
            });
    });
    
    // Make price input editable
    function makePriceEditable($priceInput) {
        $priceInput.removeClass('readonly').prop('readonly', false).addClass('editable');
        
        $priceInput.on('click', function() {
            $(this).select();
        });
        
        $priceInput.on('change keyup', function() {
            const originalPrice = parseFloat($(this).attr('data-original-price')) || 0;
            const currentPrice = parseFloat($(this).val()) || 0;
            
            if (currentPrice !== originalPrice) {
                $(this).addClass('editable');
                $(this).closest('tr').find('.price-history').addClass('text-warning');
            } else {
                $(this).removeClass('editable');
                $(this).closest('tr').find('.price-history').removeClass('text-warning');
            }
            
            updateTotals();
            saveDraftData();
        });
        
        // Add tooltip
        $priceInput.attr('title', 'Click to edit price. Original price: ₹' + $priceInput.attr('data-original-price'));
    }
    
    // Enhanced error display
    function showError(message) {
        // Create a toast-like notification
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
        
        // Auto-dismiss after 5 seconds
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
        const $row = $(`.product-row[data-index="${index}"]`);
        $row.addClass('animate__animated animate__fadeOut');
        
        setTimeout(function() {
            $row.remove();
            if ($('#items-tbody tr').length === 0) {
                $('#no-items-message').show();
            }
            updateItemsCount();
            updateTotals();
            updateProgress();
            saveDraftData();
        }, 500);
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

        // Update GST rate display
        $('#cgst-rate').text((invoiceGstRate / 2).toFixed(1));
        $('#sgst-rate').text((invoiceGstRate / 2).toFixed(1));

        // Update display with animations
        $('#subtotal').text('₹' + grandSubtotal.toFixed(2));
        $('#discount_amount_display').text('₹' + discountAmount.toFixed(2));
        $('#after_discount').text('₹' + afterDiscount.toFixed(2));
        $('#cgst').text('₹' + cgstAmount.toFixed(2));
        $('#sgst').text('₹' + sgstAmount.toFixed(2));
        $('#grand_total').text('₹' + grand_total.toFixed(2));
        
        // Update progress without circular reference
        setTimeout(function() {
            updateProgress();
        }, 10);
        
        return grand_total;
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
        saveDraftData();
    });
    
    // Customer Modal Functions
    window.showCustomerModal = function() {
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
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Basic validation
        if (!formData.name || !formData.mobile || !formData.address) {
            showError('Please fill in all required fields (Name, Mobile, Address)');
            return;
        }
        
        $.post('/customers', formData)
            .done(function(response) {
                if (response.success) {
                    // Add new customer to dropdown
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
            })
            .fail(function(xhr) {
                let errorMessage = 'Error adding customer';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                }
                showError(errorMessage);
            });
    };
    
    // Success notification
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
    
    // Save Draft Function
    window.saveDraft = function() {
        saveDraftData();
        showAutoSaveIndicator();
        showSuccess('Draft saved successfully!');
    };
    
    // Enhanced form validation
    function validateForm() {
        let isValid = true;
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Validate customer
        if (!$('#customer_id').val()) {
            $('#customer_id').addClass('is-invalid');
            $('#customer-error').text('Please select a customer');
            isValid = false;
        }
        
        // Validate items
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
                isValid = false;
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
    
    // Enhanced form submission
    $('#invoice-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return false;
        }
        
        // Show loading state
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Invoice...');
        
        // Clear draft after successful submission
        localStorage.removeItem('invoice_draft');
        
        // Submit the form
        this.submit();
    });
    
    // Initialize
    loadDraftData();
    updateProgress();
    
    // Add first item automatically
    addNewItem();
    
    // Add input change listeners for auto-save
    $('input, select, textarea').on('change keyup', function() {
        saveDraftData();
    });
});
</script>
@endpush
