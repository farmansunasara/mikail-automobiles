@extends('layouts.admin')

@section('title', 'Invoices')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Invoices</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Invoices</h3>
        <div class="card-tools">
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">Create New Invoice</a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('invoices.index') }}" method="GET" class="mb-3" id="invoice-filter-form">
            <div class="row">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="Search invoice # or customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="customer_id" class="form-control">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="From">
                </div>
                <div class="col-md-1">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="To">
                </div>
                <div class="col-md-2">
                    <div class="form-check">
                        <input type="checkbox" name="overdue" value="1" class="form-check-input" {{ request('overdue') ? 'checked' : '' }}>
                        <label class="form-check-label">Show Overdue Only</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block" id="invoice-filter-btn">
                        <span class="btn-text">Filter</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive" id="invoices-table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Amount Due</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr class="{{ $invoice->isOverdue() ? 'table-warning' : '' }}">
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->customer->name }}</td>
                        <td>{{ $invoice->invoice_date->format('d M, Y') }}</td>
                        <td>
                            {{ $invoice->due_date ? $invoice->due_date->format('d M, Y') : 'N/A' }}
                            @if($invoice->isOverdue())
                                <span class="badge badge-danger ml-1">Overdue</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $invoice->status_badge_class }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                        <td>
                            ₹{{ number_format($invoice->amount_due, 2) }}
                            @if($invoice->paid_amount > 0 && !$invoice->isPaid())
                                <small class="text-muted d-block">Paid: ₹{{ number_format($invoice->paid_amount, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-sm btn-success">PDF</a>
                                @if($invoice->status !== 'paid')
                                    <button type="button" class="btn btn-sm btn-warning" onclick="markAsPaid({{ $invoice->id }})">Mark Paid</button>
                                @endif
                                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this invoice? Stock will be restored.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 d-flex justify-content-center">
            {{ $invoices->links() }}
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="markPaidForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Mark Invoice as Paid</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="payment_amount">Payment Amount</label>
                        <input type="number" name="amount" id="payment_amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_date">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="UPI">UPI</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Card">Card</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAsPaid(invoiceId) {
    // Get invoice data from the table row
    var row = $('button[onclick="markAsPaid(' + invoiceId + ')"]').closest('tr');
    var amountDue = row.find('td:nth-child(7)').text().replace('₹', '').replace(',', '').trim();
    
    // Set form action and amount
    $('#markPaidForm').attr('action', '/invoices/' + invoiceId + '/mark-paid');
    $('#payment_amount').val(amountDue);
    
    // Show modal
    $('#markPaidModal').modal('show');
}
</script>
@endpush
@endsection
