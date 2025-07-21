@extends('layouts.admin')

@section('title', 'GST Report')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">GST Report</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">GST Report for {{ $targetMonth->format('F Y') }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.gst') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="month">Select Month</label>
                    <input type="month" name="month" id="month" class="form-control" value="{{ $targetMonth->format('Y-m') }}">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">Generate</button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Taxable Value</span>
                        <span class="info-box-number">₹{{ number_format($gstReport->taxable_value, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total CGST</span>
                        <span class="info-box-number">₹{{ number_format($gstReport->total_cgst, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total SGST</span>
                        <span class="info-box-number">₹{{ number_format($gstReport->total_sgst, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Amount</span>
                        <span class="info-box-number">₹{{ number_format($gstReport->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mt-4">Invoice Details</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>GSTIN</th>
                        <th>Taxable Value</th>
                        <th>CGST</th>
                        <th>SGST</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_date->format('d M, Y') }}</td>
                        <td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                        <td>{{ $invoice->customer->name }}</td>
                        <td>{{ $invoice->customer->gstin ?? 'N/A' }}</td>
                        <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>₹{{ number_format($invoice->cgst, 2) }}</td>
                        <td>₹{{ number_format($invoice->sgst, 2) }}</td>
                        <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No invoices found for this month.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
