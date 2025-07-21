@extends('layouts.admin')

@section('title', 'Create Invoice')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<form action="{{ route('invoices.store') }}" method="POST" id="invoice-form">
    @csrf
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Invoice Items</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>GST (%)</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Item rows will be added here -->
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="add-item-btn" class="btn btn-primary">Add Item</button>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Invoice Details</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control" value="{{ $invoice_number }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="invoice_date">Invoice Date</label>
                        <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_id">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-details="{{ json_encode($customer) }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="customer-details" class="mb-3" style="display: none;">
                        <p class="mb-0"><strong>Address:</strong> <span id="cust-address"></span></p>
                        <p class="mb-0"><strong>GSTIN:</strong> <span id="cust-gstin"></span></p>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea name="notes" class="form-control"></textarea>
                    </div>

                    <div class="mt-4">
                        <table class="table">
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
                            <tr>
                                <th>Grand Total:</th>
                                <td class="text-right font-weight-bold" id="grand_total">₹0.00</td>
                            </tr>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-success btn-block">Create Invoice</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Template for invoice item row -->
<template id="item-row-template">
    <tr class="item-row">
        <td>
            <select name="items[__INDEX__][product_id]" class="form-control product-select" required>
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-gst="{{ $product->gst_rate }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[__INDEX__][quantity]" class="form-control quantity" value="1" min="1" required></td>
        <td><input type="number" name="items[__INDEX__][price]" class="form-control price" step="0.01" required></td>
        <td><input type="number" name="items[__INDEX__][gst_rate]" class="form-control gst_rate" step="0.01" required></td>
        <td class="total-price text-right">₹0.00</td>
        <td><button type="button" class="btn btn-danger btn-sm remove-item-btn">&times;</button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#customer_id').select2();
        
        var itemIndex = 0;

        function initializeSelect2(element) {
            $(element).select2({
                placeholder: 'Select a product',
                allowClear: true
            });
        }

        $('#add-item-btn').click(function() {
            var template = $('#item-row-template').html().replace(/__INDEX__/g, itemIndex);
            $('#items-table tbody').append(template);
            initializeSelect2('.product-select:last');
            itemIndex++;
        });

        $('#items-table').on('click', '.remove-item-btn', function() {
            $(this).closest('tr').remove();
            updateTotals();
        });

        $('#items-table').on('change', '.product-select', function() {
            var selectedOption = $(this).find('option:selected');
            var row = $(this).closest('tr');
            row.find('.price').val(selectedOption.data('price'));
            row.find('.gst_rate').val(selectedOption.data('gst'));
            updateTotals();
        });

        $('#items-table').on('input', '.quantity, .price, .gst_rate', function() {
            updateTotals();
        });

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

        function updateTotals() {
            var subtotal = 0;
            var cgst = 0;
            var sgst = 0;

            $('.item-row').each(function() {
                var row = $(this);
                var qty = parseFloat(row.find('.quantity').val()) || 0;
                var price = parseFloat(row.find('.price').val()) || 0;
                var gst_rate = parseFloat(row.find('.gst_rate').val()) || 0;
                
                var lineTotal = qty * price;
                var gstAmount = (lineTotal * gst_rate) / 100;
                
                row.find('.total-price').text('₹' + lineTotal.toFixed(2));
                
                subtotal += lineTotal;
                cgst += gstAmount / 2;
                sgst += gstAmount / 2;
            });

            var grand_total = subtotal + cgst + sgst;

            $('#subtotal').text('₹' + subtotal.toFixed(2));
            $('#cgst').text('₹' + cgst.toFixed(2));
            $('#sgst').text('₹' + sgst.toFixed(2));
            $('#grand_total').text('₹' + grand_total.toFixed(2));
        }

        // Trigger on load if there are old items
        if ($('.item-row').length > 0) {
            initializeSelect2('.product-select');
            updateTotals();
        }
    });
</script>
@endpush
