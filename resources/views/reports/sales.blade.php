@extends('layouts.admin')

@section('title', 'Sales Report')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Sales Report</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sales Report</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.sales') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-5">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-5">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <a href="{{ route('reports.export.sales', request()->query()) }}" class="btn btn-success btn-block" title="Export to CSV" data-toggle="tooltip">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>₹{{ number_format($totals->total_amount, 2) }}</h3>
                        <p>Total Sales (Taxable)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>₹{{ number_format($totals->total_cgst, 2) }}</h3>
                        <p>Total CGST</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>₹{{ number_format($totals->total_sgst, 2) }}</h3>
                        <p>Total SGST</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>₹{{ number_format($totals->grand_total, 2) }}</h3>
                        <p>Grand Total</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <x-sortable-header column="invoice_number" label="Invoice #" />
                        <th>Customer</th>
                        <x-sortable-header column="invoice_date" label="Date" />
                        <x-sortable-header column="total_amount" label="Taxable Amount" />
                        <th>CGST</th>
                        <th>SGST</th>
                        <x-sortable-header column="grand_total" label="Grand Total" />
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesReport as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->customer->name }}</td>
                        <td>{{ $invoice->invoice_date->format('d M, Y') }}</td>
                        <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>₹{{ number_format($invoice->cgst, 2) }}</td>
                        <td>₹{{ number_format($invoice->sgst, 2) }}</td>
                        <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                        <td>
                            @if($invoice->invoice_type === 'gst')
                                <a href="{{ route('invoices.gst.show', $invoice) }}" class="btn btn-sm btn-info">View</a>
                            @else
                                <a href="{{ route('invoices.non_gst.show', $invoice) }}" class="btn btn-sm btn-info">View</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No sales found for the selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $salesReport->links() }}
        </div>
    </div>
</div>
@endsection
