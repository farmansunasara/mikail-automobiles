# Implementation Examples & Code Templates

This document provides ready-to-use code templates to implement the refactoring recommendations.

---

## 1. CONSOLIDATED INVOICE VIEW (Single File with Conditions)

### File: `resources/views/invoices/create.blade.php`

```php
@extends('layouts.admin')

@section('title', 'Create Invoice')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/invoice-forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/invoice-modals.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="page-title">
                {{ $invoiceType === 'gst' ? 'Create GST Invoice' : 'Create Simple Invoice' }}
            </h2>
        </div>
    </div>

    <form id="invoice-form" class="invoice-form" method="POST" 
          action="{{ route($invoiceType === 'gst' ? 'invoices.store-gst' : 'invoices.store') }}">
        @csrf
        <input type="hidden" name="invoice_type" value="{{ $invoiceType }}">

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Invoice Details</h5>
            </div>
            <div class="card-body">
                @include('invoices.components.form-header', ['invoiceType' => $invoiceType])
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Items</h5>
            </div>
            <div class="card-body">
                @include('invoices.components.items-section', ['invoiceType' => $invoiceType])
            </div>
        </div>

        {{-- GST-specific section --}}
        @if($invoiceType === 'gst')
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Tax Information</h5>
            </div>
            <div class="card-body">
                @include('invoices.components.gst-section')
            </div>
        </div>
        @endif

        <div class="card mt-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Totals</h5>
            </div>
            <div class="card-body">
                @include('invoices.components.totals-section', ['invoiceType' => $invoiceType])
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Payment Details</h5>
            </div>
            <div class="card-body">
                @include('invoices.components.payment-section')
            </div>
        </div>

        <div class="form-actions mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Invoice
            </button>
            <button type="reset" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Reset
            </button>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>

    {{-- Modals --}}
    @include('invoices.components.modals')
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/modules/FormValidator.js') }}"></script>
<script src="{{ asset('js/modules/InvoiceCalculator.js') }}"></script>
<script src="{{ asset('js/modules/ApiHandler.js') }}"></script>
<script src="{{ asset('js/modules/UIManager.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceType = '{{ $invoiceType }}';
    
    // Initialize validators
    const validator = new FormValidator('#invoice-form');
    
    // Initialize calculator
    const calculator = new InvoiceCalculator({
        type: invoiceType,
        gstRate: {{ $gstRate ?? 18 }},
        formSelector: '#invoice-form'
    });
    
    // Initialize UI manager
    const uiManager = new UIManager('#invoice-form');
    
    // Setup form submission
    document.getElementById('invoice-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validator.validate()) {
            uiManager.showError('Please fix the errors below');
            return;
        }
        
        uiManager.showLoading();
        
        try {
            // Form will be submitted naturally if validation passes
            this.submit();
        } catch (error) {
            uiManager.showError(error.message);
        } finally {
            uiManager.hideLoading();
        }
    });
});
</script>
@endpush
```

---

## 2. REUSABLE BLADE COMPONENTS

### File: `resources/views/invoices/components/form-header.blade.php`

```php
{{-- Invoice Date and Customer Selection --}}
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="invoice_date" class="form-label">Invoice Date</label>
            <input type="date" id="invoice_date" name="invoice_date" 
                   class="form-control @error('invoice_date') is-invalid @enderror"
                   value="{{ old('invoice_date', now()->format('Y-m-d')) }}" 
                   required>
            @error('invoice_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="due_date" class="form-label">Due Date</label>
            <input type="date" id="due_date" name="due_date" 
                   class="form-control @error('due_date') is-invalid @enderror"
                   value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}" 
                   required>
            @error('due_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group mt-3">
    <label for="customer_id" class="form-label">Customer</label>
    <select id="customer_id" name="customer_id" class="form-control select2 @error('customer_id') is-invalid @enderror" 
            required data-placeholder="Select a customer">
        <option></option>
        @foreach($customers as $customer)
            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                {{ $customer->name }} ({{ $customer->phone }})
            </option>
        @endforeach
    </select>
    @error('customer_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div id="customer-details" class="mt-3" style="display: none;">
    <div class="alert alert-info alert-sm" role="alert">
        <strong>Customer Details</strong>
        <div id="customer-info"></div>
    </div>
</div>
```

### File: `resources/views/invoices/components/items-section.blade.php`

```php
{{-- Invoice Items Table --}}
<div class="table-responsive">
    <table class="table table-sm table-hover" id="items-table">
        <thead class="table-light">
            <tr>
                <th style="width: 30%">Product</th>
                <th style="width: 15%">Color</th>
                <th style="width: 10%">Qty</th>
                <th style="width: 15%">Unit Price</th>
                <th style="width: 15%">Total</th>
                <th style="width: 10%">Action</th>
            </tr>
        </thead>
        <tbody id="items-body">
            <tr class="item-row" data-row-id="1">
                <td>
                    <select name="items[1][product_id]" class="form-control form-control-sm product-select" required>
                        <option>Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                {{ $product->name }} (₹{{ number_format($product->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="items[1][color_id]" class="form-control form-control-sm color-select">
                        <option>Select Color</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="items[1][quantity]" min="1" value="1" 
                           class="form-control form-control-sm quantity-input">
                </td>
                <td>
                    <input type="number" name="items[1][unit_price]" step="0.01" 
                           class="form-control form-control-sm unit-price" readonly>
                </td>
                <td>
                    <input type="number" name="items[1][total]" step="0.01" 
                           class="form-control form-control-sm line-total" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<button type="button" id="add-item-btn" class="btn btn-sm btn-success mt-2">
    <i class="fas fa-plus"></i> Add Item
</button>
```

### File: `resources/views/invoices/components/gst-section.blade.php`

```php
{{-- GST-specific tax section --}}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="gst_rate" class="form-label">GST Rate (%)</label>
            <select id="gst_rate" name="gst_rate" class="form-control @error('gst_rate') is-invalid @enderror">
                <option value="0">No GST (0%)</option>
                <option value="5" {{ old('gst_rate') == 5 ? 'selected' : '' }}>5%</option>
                <option value="12" {{ old('gst_rate') == 12 ? 'selected' : '' }}>12%</option>
                <option value="18" {{ old('gst_rate') == 18 ? 'selected' : '' }} selected>18%</option>
                <option value="28" {{ old('gst_rate') == 28 ? 'selected' : '' }}>28%</option>
            </select>
            @error('gst_rate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="cgst" class="form-label">CGST</label>
            <input type="number" id="cgst" name="cgst" step="0.01" class="form-control" 
                   value="{{ old('cgst', 0) }}" readonly>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="sgst" class="form-label">SGST</label>
            <input type="number" id="sgst" name="sgst" step="0.01" class="form-control" 
                   value="{{ old('sgst', 0) }}" readonly>
        </div>
    </div>
</div>
```

### File: `resources/views/invoices/components/totals-section.blade.php`

```php
{{-- Invoice Totals --}}
<div class="row justify-content-end">
    <div class="col-md-4">
        <table class="table table-sm table-borderless">
            <tbody>
                <tr>
                    <td class="font-weight-bold">Subtotal:</td>
                    <td class="text-right">
                        <input type="number" name="subtotal" step="0.01" class="form-control-plaintext text-right" 
                               value="{{ old('subtotal', 0) }}" readonly style="border: none; padding: 0;">
                    </td>
                </tr>

                @if($invoiceType === 'gst')
                <tr>
                    <td class="font-weight-bold">CGST:</td>
                    <td class="text-right">
                        <input type="number" name="cgst_amount" step="0.01" class="form-control-plaintext text-right" 
                               value="{{ old('cgst_amount', 0) }}" readonly style="border: none; padding: 0;">
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold">SGST:</td>
                    <td class="text-right">
                        <input type="number" name="sgst_amount" step="0.01" class="form-control-plaintext text-right" 
                               value="{{ old('sgst_amount', 0) }}" readonly style="border: none; padding: 0;">
                    </td>
                </tr>
                @endif

                <tr>
                    <td class="font-weight-bold">Discount:</td>
                    <td class="text-right">
                        <input type="number" name="discount_amount" step="0.01" class="form-control form-control-sm" 
                               value="{{ old('discount_amount', 0) }}">
                    </td>
                </tr>

                <tr>
                    <td class="font-weight-bold">Packaging Fees:</td>
                    <td class="text-right">
                        <input type="number" name="packaging_fees" step="0.01" class="form-control form-control-sm" 
                               value="{{ old('packaging_fees', 0) }}">
                    </td>
                </tr>

                <tr class="border-top">
                    <td class="font-weight-bold">Grand Total:</td>
                    <td class="text-right font-weight-bold">
                        <input type="number" name="grand_total" step="0.01" class="form-control-plaintext text-right text-danger font-weight-bold" 
                               value="{{ old('grand_total', 0) }}" readonly style="border: none; padding: 0; font-size: 1.2rem;">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

---

## 3. JAVASCRIPT MODULES

### File: `public/js/modules/FormValidator.js`

```javascript
/**
 * Form Validator Module
 * Handles client-side form validation
 */

class FormValidator {
    constructor(formSelector, options = {}) {
        this.form = document.querySelector(formSelector);
        this.options = {
            showErrors: true,
            validateOnChange: true,
            validateOnBlur: true,
            ...options
        };
        this.errors = {};
        this.init();
    }

    init() {
        if (!this.form) return;
        
        if (this.options.validateOnChange) {
            this.form.addEventListener('change', (e) => this.validateField(e.target));
        }
        
        if (this.options.validateOnBlur) {
            this.form.addEventListener('blur', (e) => this.validateField(e.target), true);
        }
    }

    validate() {
        this.errors = {};
        const fields = this.form.querySelectorAll('[required], [data-validate]');
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                this.errors[field.name] = this.getErrorMessage(field);
            }
        });
        
        return Object.keys(this.errors).length === 0;
    }

    validateField(field) {
        const value = field.value.trim();
        
        // Required validation
        if (field.hasAttribute('required') && !value) {
            this.showFieldError(field, 'This field is required');
            return false;
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showFieldError(field, 'Please enter a valid email');
                return false;
            }
        }
        
        // Number validation
        if (field.type === 'number' && value) {
            if (isNaN(value)) {
                this.showFieldError(field, 'Please enter a valid number');
                return false;
            }
            
            if (field.min && parseInt(value) < parseInt(field.min)) {
                this.showFieldError(field, `Minimum value is ${field.min}`);
                return false;
            }
        }
        
        // Date validation
        if (field.type === 'date' && value) {
            const date = new Date(value);
            if (isNaN(date.getTime())) {
                this.showFieldError(field, 'Please enter a valid date');
                return false;
            }
        }
        
        // Custom validation rules
        const customRule = field.getAttribute('data-validate');
        if (customRule && !this.validateCustomRule(value, customRule)) {
            this.showFieldError(field, field.getAttribute('data-error-message') || 'Invalid input');
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }

    validateCustomRule(value, rule) {
        // Add custom validation rules here
        switch (rule) {
            case 'phone':
                return /^\d{10}$/.test(value.replace(/\D/g, ''));
            case 'pincode':
                return /^\d{6}$/.test(value);
            case 'gst':
                return /^\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z\d]{1}Z[A-Z\d]{1}$/.test(value);
            default:
                return true;
        }
    }

    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let errorElement = field.parentElement.querySelector('.invalid-feedback');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'invalid-feedback d-block';
            field.parentElement.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        
        const errorElement = field.parentElement.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.remove();
        }
    }

    getErrorMessage(field) {
        return field.parentElement.querySelector('.invalid-feedback')?.textContent || 'Invalid input';
    }

    getErrors() {
        return this.errors;
    }

    reset() {
        this.errors = {};
        this.form.querySelectorAll('.is-invalid').forEach(field => {
            this.clearFieldError(field);
        });
    }
}

// Export for use
window.FormValidator = FormValidator;
```

### File: `public/js/modules/InvoiceCalculator.js`

```javascript
/**
 * Invoice Calculator Module
 * Handles all invoice calculations (taxes, totals, etc)
 */

class InvoiceCalculator {
    constructor(options = {}) {
        this.invoiceType = options.type || 'gst';
        this.gstRate = options.gstRate || 18;
        this.formSelector = options.formSelector || '#invoice-form';
        this.form = document.querySelector(this.formSelector);
        this.init();
    }

    init() {
        if (!this.form) return;
        
        // Listen for quantity and price changes
        this.form.addEventListener('change', (e) => {
            if (e.target.classList.contains('quantity-input') || 
                e.target.classList.contains('unit-price')) {
                this.updateLineTotal(e.target.closest('tr'));
            }
        });
        
        this.form.addEventListener('change', (e) => {
            if (e.target.classList.contains('unit-price') ||
                e.target.id === 'discount_amount' ||
                e.target.id === 'packaging_fees' ||
                e.target.id === 'gst_rate') {
                this.updateTotals();
            }
        });
    }

    updateLineTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input')?.value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price')?.value) || 0;
        const lineTotal = quantity * unitPrice;
        
        const totalInput = row.querySelector('.line-total');
        if (totalInput) {
            totalInput.value = lineTotal.toFixed(2);
        }
        
        this.updateTotals();
    }

    updateTotals() {
        // Calculate subtotal
        let subtotal = 0;
        const items = this.form.querySelectorAll('.item-row');
        
        items.forEach(row => {
            const total = parseFloat(row.querySelector('.line-total')?.value) || 0;
            subtotal += total;
        });

        // Apply discount
        const discountAmount = parseFloat(this.form.querySelector('[name="discount_amount"]')?.value) || 0;
        const subtotalAfterDiscount = subtotal - discountAmount;

        // Calculate GST
        let cgstAmount = 0, sgstAmount = 0;
        
        if (this.invoiceType === 'gst') {
            const gstRate = parseFloat(this.form.querySelector('[name="gst_rate"]')?.value) || 0;
            cgstAmount = (subtotalAfterDiscount * gstRate) / 200; // CGST = half of GST
            sgstAmount = (subtotalAfterDiscount * gstRate) / 200; // SGST = half of GST
        }

        // Add packaging fees
        const packagingFees = parseFloat(this.form.querySelector('[name="packaging_fees"]')?.value) || 0;

        // Calculate grand total
        const grandTotal = subtotalAfterDiscount + cgstAmount + sgstAmount + packagingFees;

        // Update form inputs
        this.updateFormValues({
            subtotal: subtotal.toFixed(2),
            cgst_amount: cgstAmount.toFixed(2),
            sgst_amount: sgstAmount.toFixed(2),
            cgst: (cgstAmount / 2).toFixed(2), // Display half
            sgst: (sgstAmount / 2).toFixed(2), // Display half
            grand_total: grandTotal.toFixed(2)
        });

        // Trigger custom event for other modules
        this.form.dispatchEvent(new CustomEvent('totals-updated', {
            detail: {
                subtotal,
                cgstAmount,
                sgstAmount,
                grandTotal
            }
        }));
    }

    updateFormValues(values) {
        Object.keys(values).forEach(key => {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = values[key];
            }
        });
    }

    getTotal() {
        return parseFloat(this.form.querySelector('[name="grand_total"]')?.value) || 0;
    }

    getTaxAmount() {
        const cgst = parseFloat(this.form.querySelector('[name="cgst_amount"]')?.value) || 0;
        const sgst = parseFloat(this.form.querySelector('[name="sgst_amount"]')?.value) || 0;
        return cgst + sgst;
    }

    reset() {
        this.updateFormValues({
            subtotal: '0.00',
            cgst_amount: '0.00',
            sgst_amount: '0.00',
            grand_total: '0.00'
        });
    }
}

// Export for use
window.InvoiceCalculator = InvoiceCalculator;
```

### File: `public/js/modules/UIManager.js`

```javascript
/**
 * UI Manager Module
 * Handles all UI interactions and DOM manipulations
 */

class UIManager {
    constructor(formSelector = '#invoice-form') {
        this.formSelector = formSelector;
        this.form = document.querySelector(formSelector);
        this.loadingElement = null;
        this.initializeUI();
    }

    initializeUI() {
        // Create loading overlay
        this.createLoadingOverlay();
        
        // Setup item add/remove buttons
        this.setupItemManagement();
    }

    createLoadingOverlay() {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'loading-overlay d-none';
        overlay.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p>Processing your request...</p>
        `;
        document.body.appendChild(overlay);
        this.loadingElement = overlay;
    }

    setupItemManagement() {
        const addBtn = document.getElementById('add-item-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.addItem());
        }

        // Delegate remove button clicks
        const itemsBody = document.getElementById('items-body');
        if (itemsBody) {
            itemsBody.addEventListener('click', (e) => {
                if (e.target.closest('.remove-item')) {
                    this.removeItem(e.target.closest('tr'));
                }
            });
        }
    }

    addItem() {
        const itemsBody = document.getElementById('items-body');
        const rowCount = itemsBody.querySelectorAll('tr').length + 1;
        
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.dataset.rowId = rowCount;
        newRow.innerHTML = `
            <td>
                <select name="items[${rowCount}][product_id]" class="form-control form-control-sm product-select" required>
                    <option>Select Product</option>
                </select>
            </td>
            <td>
                <select name="items[${rowCount}][color_id]" class="form-control form-control-sm color-select">
                    <option>Select Color</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][quantity]" min="1" value="1" 
                       class="form-control form-control-sm quantity-input">
            </td>
            <td>
                <input type="number" name="items[${rowCount}][unit_price]" step="0.01" 
                       class="form-control form-control-sm unit-price" readonly>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][total]" step="0.01" 
                       class="form-control form-control-sm line-total" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        itemsBody.appendChild(newRow);
        this.showSuccess('Item added successfully');
    }

    removeItem(row) {
        if (!confirm('Are you sure you want to remove this item?')) {
            return;
        }
        
        row.remove();
        this.showSuccess('Item removed');
        
        // Trigger total recalculation
        this.form.dispatchEvent(new Event('change'));
    }

    showLoading() {
        if (this.loadingElement) {
            this.loadingElement.classList.remove('d-none');
        }
    }

    hideLoading() {
        if (this.loadingElement) {
            this.loadingElement.classList.add('d-none');
        }
    }

    showSuccess(message) {
        this.showAlert(message, 'success');
    }

    showError(message) {
        this.showAlert(message, 'danger');
    }

    showWarning(message) {
        this.showAlert(message, 'warning');
    }

    showInfo(message) {
        this.showAlert(message, 'info');
    }

    showAlert(message, type = 'info') {
        const alertId = 'alert-' + Date.now();
        const alert = document.createElement('div');
        alert.id = alertId;
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.role = 'alert';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at top of form or page
        const insertPoint = this.form || document.body;
        insertPoint.parentElement.insertBefore(alert, insertPoint);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const element = document.getElementById(alertId);
            if (element) {
                const bsAlert = new bootstrap.Alert(element);
                bsAlert.close();
            }
        }, 5000);
    }
}

// Export for use
window.UIManager = UIManager;
```

---

## 4. REFACTORED PHP SERVICE CLASS

### File: `app/Services/InvoiceCalculationService.php`

```php
<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;

/**
 * Invoice Calculation Service
 * Handles all invoice-related calculations
 */
class InvoiceCalculationService
{
    private const DEFAULT_GST_RATE = 18;

    /**
     * Calculate invoice totals
     */
    public function calculateTotals(Invoice $invoice): array
    {
        $subtotal = $this->calculateSubtotal($invoice);
        $discount = $invoice->discount_amount ?? 0;
        $subtotalAfterDiscount = $subtotal - $discount;
        
        $taxes = $this->calculateTaxes($invoice, $subtotalAfterDiscount);
        $packagingFees = $invoice->packaging_fees ?? 0;
        
        $grandTotal = $subtotalAfterDiscount + $taxes['total'] + $packagingFees;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'subtotal_after_discount' => $subtotalAfterDiscount,
            'cgst' => $taxes['cgst'] ?? 0,
            'sgst' => $taxes['sgst'] ?? 0,
            'total_tax' => $taxes['total'] ?? 0,
            'packaging_fees' => $packagingFees,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Calculate subtotal from invoice items
     */
    public function calculateSubtotal(Invoice $invoice): float
    {
        return $invoice->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    /**
     * Calculate tax amounts based on invoice type
     */
    private function calculateTaxes(Invoice $invoice, float $amount): array
    {
        if ($invoice->invoice_type !== 'gst') {
            return ['cgst' => 0, 'sgst' => 0, 'total' => 0];
        }

        $gstRate = $invoice->gst_rate ?? self::DEFAULT_GST_RATE;
        $totalTax = ($amount * $gstRate) / 100;
        $halfTax = $totalTax / 2;

        return [
            'cgst' => $halfTax,
            'sgst' => $halfTax,
            'total' => $totalTax,
        ];
    }

    /**
     * Apply discount to amount
     */
    public function applyDiscount(float $amount, string $discountType, float $discountValue): float
    {
        if ($discountType === 'percentage') {
            return $amount - ($amount * $discountValue / 100);
        }

        return $amount - $discountValue;
    }
}
```

### File: `app/Http/Controllers/InvoiceController.php` (Refactored)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Services\InvoiceService;
use App\Services\InvoiceCalculationService;
use App\Http\Requests\InvoiceStoreRequest;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private InvoiceCalculationService $calculationService
    ) {}

    /**
     * Show create invoice form
     */
    public function create(Request $request)
    {
        $invoiceType = $request->query('type', 'gst');
        
        return view('invoices.create', [
            'invoiceType' => $invoiceType,
            'customers' => Customer::active()->get(),
            'products' => Product::available()->get(),
            'gstRate' => config('invoice.gst_rate', 18),
        ]);
    }

    /**
     * Store invoice in database
     */
    public function store(InvoiceStoreRequest $request)
    {
        try {
            $invoice = $this->invoiceService->create(
                $request->validated(),
                $request->input('invoice_type')
            );

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice)
    {
        $totals = $this->calculationService->calculateTotals($invoice);
        
        return view('invoices.show', [
            'invoice' => $invoice,
            'totals' => $totals,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(Invoice $invoice)
    {
        return view('invoices.edit', [
            'invoice' => $invoice,
            'invoiceType' => $invoice->invoice_type,
            'customers' => Customer::active()->get(),
            'products' => Product::available()->get(),
        ]);
    }

    /**
     * Update invoice
     */
    public function update(InvoiceStoreRequest $request, Invoice $invoice)
    {
        try {
            $this->invoiceService->update($invoice, $request->validated());
            
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice)
    {
        try {
            $this->invoiceService->delete($invoice);
            
            return redirect()
                ->route('invoices.index')
                ->with('success', 'Invoice deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }
}
```

---

## 5. EXTRACTED CSS FILE

### File: `resources/css/invoice-forms.css`

```css
/* Invoice Forms Common Styles */

/* Form Container */
.invoice-form {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px;
}

/* Card Spacing */
.invoice-form .card {
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
}

.invoice-form .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
}

.invoice-form .card-body {
    padding: 1rem;
}

/* Form Groups */
.invoice-form .form-group {
    margin-bottom: 0.75rem;
}

.invoice-form label {
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #495057;
}

/* Form Controls */
.invoice-form .form-control,
.invoice-form .form-select {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.invoice-form .form-control:focus,
.invoice-form .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Validation Styles */
.invoice-form .is-invalid {
    border-color: #dc3545;
}

.invoice-form .invalid-feedback {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}

/* Table Styles */
.invoice-form .table {
    margin-bottom: 0;
    font-size: 0.875rem;
}

.invoice-form .table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    padding: 0.5rem;
}

.invoice-form .table td {
    padding: 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #dee2e6;
}

/* Button Styles */
.form-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.invoice-form .btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
    border: 1px solid transparent;
}

.invoice-form .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.invoice-form .btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

/* Alert Styles */
.invoice-form .alert {
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border-radius: 0.25rem;
}

/* Select2 Customization */
.invoice-form .select2-container--default .select2-selection--single {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    height: 38px;
}

.invoice-form .select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Responsive */
@media (max-width: 768px) {
    .invoice-form {
        padding: 10px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
    
    .invoice-form .table {
        font-size: 0.75rem;
    }
    
    .invoice-form .table th,
    .invoice-form .table td {
        padding: 0.25rem;
    }
}
```

---

## Implementation Checklist

- [ ] Create consolidated invoice views
- [ ] Extract invoice components
- [ ] Move CSS to separate files
- [ ] Reorganize JavaScript modules
- [ ] Create service classes
- [ ] Refactor controllers
- [ ] Add validation request classes
- [ ] Create unit tests
- [ ] Create feature tests
- [ ] Update routes
- [ ] Update documentation
- [ ] Test in development environment
- [ ] Deploy to staging
- [ ] Final QA testing
- [ ] Deploy to production

This implementation guide provides concrete examples you can start using immediately.
