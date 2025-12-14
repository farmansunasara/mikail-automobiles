@extends('layouts.admin')

@section('title', 'Order Details - ' . $order->order_number)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
<li class="breadcrumb-item active">{{ $order->order_number }}</li>
@endsection

@section('content')
            <!-- Order Header -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Order Information</h3>
                    <div class="card-tools">
                        @if($order->status === 'pending')
                            <button type="button" class="btn btn-success btn-sm" onclick="generateInvoice()">
                                <i class="fas fa-file-invoice"></i> Generate Invoice
                            </button>
                            <a href="{{ route('orders.edit', $order) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit Order
                            </a>
                        @endif
                        <!-- New: Download Order PDF (non-intrusive) -->
                        @if($order->status === 'pending' && (auth()->user()->role === 'admin' || $order->status !== 'cancelled'))
                        <a href="{{ route('orders.download', $order) }}" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-download"></i> Download Order
                        </a>
                        @endif
                        @if($order->status === 'pending')
                            <button type="button" class="btn btn-danger btn-sm" onclick="cancelOrder()">
                                <i class="fas fa-times"></i> Cancel Order
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Order Number:</strong><br>
                            <span class="h5">{{ $order->order_number }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Customer:</strong><br>
                            <a href="{{ route('customers.show', $order->customer) }}" class="text-primary">
                                {{ $order->customer->name }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <strong>Order Date:</strong><br>
                            {{ $order->order_date->format('d/m/Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Delivery Date:</strong><br>
                            @if($order->delivery_date)
                                {{ $order->delivery_date->format('d/m/Y') }}
                                @if($order->isOverdue())
                                    <span class="badge badge-danger ml-1">Overdue</span>
                                @endif
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <strong>Status:</strong><br>
                            <span class="badge {{ $order->status_badge_class }} badge-lg">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Amount:</strong><br>
                            <span class="h5 text-primary">₹{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Items:</strong><br>
                            {{ $order->items->count() }} items ({{ $order->total_quantity }} units)
                        </div>
                        <div class="col-md-3">
                            <strong>Invoice:</strong><br>
                            @if($order->invoice)
                                <a href="{{ $order->invoice->invoice_type === 'gst' ? route('invoices.gst.show', $order->invoice) : route('invoices.non_gst.show', $order->invoice) }}" 
                                   class="text-success">
                                    {{ $order->invoice->invoice_number }}
                                </a>
                            @else
                                <span class="text-muted">Not generated</span>
                            @endif
                        </div>
                    </div>
                    @if($order->notes)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <strong>Notes:</strong><br>
                                <p class="text-muted">{{ $order->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions for Pending Orders -->
            @if($order->status === 'pending')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-tools"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Check Manufacturing Requirements</span>
                                        <span class="info-box-number">Real-time shortage calculation</span>
                                        <a href="{{ route('manufacturing.requirements.index') }}" class="btn btn-info btn-sm mt-2">
                                            <i class="fas fa-external-link-alt"></i> View Requirements
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-file-invoice"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Generate Invoice</span>
                                        <span class="info-box-number">Convert to GST/Non-GST invoice</span>
                                        <button type="button" class="btn btn-success btn-sm mt-2" onclick="generateInvoice()">
                                            <i class="fas fa-plus"></i> Create Invoice
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            <strong>Simplified Workflow:</strong> 
                            Stock will be checked and deducted only when you generate an invoice. 
                            Check manufacturing requirements to see what needs to be produced.
                        </div>
                    </div>
                </div>
            @endif

            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Order Items</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Color</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                    <th>Available Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <a href="{{ route('products.show', $item->product) }}" class="text-primary">
                                                {{ $item->product->name }}
                                            </a>
                                        </td>
                                        <td>{{ $item->colorVariant?->color ?? 'Default' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>₹{{ number_format($item->price, 2) }}</td>
                                        <td>₹{{ number_format($item->subtotal, 2) }}</td>
                                        <td>{{ $item->available_stock }}</td>
                                        <td>
                                            <span class="badge {{ $item->stock_status_class }}">
                                                {{ ucfirst($item->stock_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="4">Total</th>
                                    <th>₹{{ number_format($order->total_amount, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Invoice Type Selection Modal -->
<div class="modal fade" id="invoiceTypeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Invoice Type</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Choose the type of invoice you want to generate from this order:</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card text-center" style="cursor: pointer;" onclick="selectInvoiceType('gst')">
                            <div class="card-body">
                                <i class="fas fa-file-invoice fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">GST Invoice</h5>
                                <p class="card-text">Generate a GST invoice with tax calculations</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-center" style="cursor: pointer;" onclick="selectInvoiceType('non_gst')">
                            <div class="card-body">
                                <i class="fas fa-file-invoice fa-3x text-success mb-3"></i>
                                <h5 class="card-title">Non-GST Invoice</h5>
                                <p class="card-text">Generate a simple invoice without tax</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Type Selection Modal -->
<div class="modal fade" id="invoiceTypeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Invoice Type</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Please select the type of invoice you want to generate:</p>
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-primary btn-block" onclick="selectInvoiceType('gst')">
                            <i class="fas fa-receipt"></i><br>
                            GST Invoice
                            <small class="d-block text-muted">With tax calculations</small>
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-info btn-block" onclick="selectInvoiceType('non_gst')">
                            <i class="fas fa-file-invoice"></i><br>
                            Non-GST Invoice
                            <small class="d-block text-muted">Without tax calculations</small>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function cancelOrder() {
    confirmOrderCancellation('{{ $order->order_number }}', function() {
        // Submit the cancel form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("orders.cancel", $order) }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add method override for DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    });
}

function generateInvoice() {
    confirmInvoiceGeneration('{{ $order->order_number }}', function() {
        showInvoiceTypeModal();
    });
}

function showInvoiceTypeModal() {
    $('#invoiceTypeModal').modal('show');
}

function selectInvoiceType(type) {
    $('#invoiceTypeModal').modal('hide');
    
    if (type === 'gst') {
        window.location.href = "{{ route('invoices.gst.create') }}?order_id={{ $order->id }}";
    } else if (type === 'non_gst') {
        window.location.href = "{{ route('invoices.non_gst.create') }}?order_id={{ $order->id }}";
    }
}
</script>
@endpush
