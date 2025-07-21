@extends('layouts.admin')

@section('title', 'Customer Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
<li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Customer Information</h3>
                <div class="card-tools">
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-warning">Edit</a>
                </div>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Name</dt>
                    <dd>{{ $customer->name }}</dd>
                    <dt>Mobile</dt>
                    <dd>{{ $customer->mobile }}</dd>
                    <dt>Email</dt>
                    <dd>{{ $customer->email ?? 'N/A' }}</dd>
                    <dt>GSTIN</dt>
                    <dd>{{ $customer->gstin ?? 'N/A' }}</dd>
                    <dt>Address</dt>
                    <dd>{{ $customer->address ?? 'N/A' }}</dd>
                    <dt>State</dt>
                    <dd>{{ $customer->state ?? 'N/A' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Invoices</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->invoices()->latest()->take(10)->get() as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->invoice_date->format('d M, Y') }}</td>
                                <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No invoices found for this customer.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
