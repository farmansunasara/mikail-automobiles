@extends('layouts.admin')

@section('title', 'Invoice Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
<li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="invoice p-3 mb-3">
    <!-- title row -->
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-globe"></i> {{ config('app.name', 'Laravel') }}
                <small class="float-right">Date: {{ $invoice->invoice_date->format('d/m/Y') }}</small>
            </h4>
        </div>
    </div>
    <!-- info row -->
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            From
            <address>
                <strong>{{ config('app.name', 'Laravel') }}</strong><br>
                123 Industrial Area<br>
                Pune, Maharashtra 411001<br>
                Phone: (123) 456-7890<br>
                Email: info@example.com
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            To
            <address>
                <strong>{{ $invoice->customer->name }}</strong><br>
                {{ $invoice->customer->address }}<br>
                {{ $invoice->customer->state }}<br>
                Phone: {{ $invoice->customer->mobile }}<br>
                Email: {{ $invoice->customer->email ?? 'N/A' }}
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Invoice #{{ $invoice->invoice_number }}</b><br>
            <br>
            <b>GSTIN:</b> {{ $invoice->customer->gstin ?? 'N/A' }}<br>
        </div>
    </div>
    <!-- /.row -->

    <!-- Table row -->
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>HSN Code</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->product->hsn_code }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format($item->price, 2) }}</td>
                        <td>₹{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.row -->

    <div class="row">
        <!-- accepted payments column -->
        <div class="col-6">
            <p class="lead">Payment Methods:</p>
            <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                Bank Transfer, UPI, Cash
            </p>
            @if($invoice->notes)
            <p class="lead">Notes:</p>
            <p class="text-muted">{{ $invoice->notes }}</p>
            @endif
        </div>
        <!-- /.col -->
        <div class="col-6">
            <p class="lead">Amount Due {{ $invoice->invoice_date->format('d/m/Y') }}</p>

            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Subtotal:</th>
                        <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>CGST ({{ $invoice->items->first()->gst_rate / 2 }}%)</th>
                        <td>₹{{ number_format($invoice->cgst, 2) }}</td>
                    </tr>
                    <tr>
                        <th>SGST ({{ $invoice->items->first()->gst_rate / 2 }}%)</th>
                        <td>₹{{ number_format($invoice->sgst, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total:</th>
                        <td><strong>₹{{ number_format($invoice->grand_total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

    <!-- this row will not appear when printing -->
    <div class="row no-print">
        <div class="col-12">
            <a href="{{ route('invoices.preview', $invoice) }}" rel="noopener" target="_blank" class="btn btn-default"><i class="fas fa-print"></i> Print</a>
            <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-primary float-right" style="margin-right: 5px;">
                <i class="fas fa-download"></i> Generate PDF
            </a>
        </div>
    </div>
</div>
@endsection
