@extends('layouts.admin')

@section('title', 'Non-GST Report')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Non-GST Report</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Non-GST Report for {{ $targetMonth->format('F Y') }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.non-gst') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="month">Select Month</label>
                    <input type="month" name="month" id="month" class="form-control" value="{{ $targetMonth->format('Y-m') }}">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">Generate</button>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <a href="{{ route('reports.export.non-gst', request()->query()) }}" class="btn btn-success btn-block" title="Export to CSV" data-toggle="tooltip">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Amount</span>
                        <span class="info-box-number">₹{{ number_format($nonGstReport->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Grand Total</span>
                        <span class="info-box-number">₹{{ number_format($nonGstReport->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-file-invoice"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Invoices</span>
                        <span class="info-box-number">{{ $nonGstReport->invoice_count }}</span>
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
                        <th>Total Amount</th>
                        <th>Grand Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_date->format('d M, Y') }}</td>
                        <td>
                            <a href="{{ route('invoices.non_gst.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                        </td>
                        <td>{{ $invoice->customer->name }}</td>
                        <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'sent' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No Non-GST invoices found for this month.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush
