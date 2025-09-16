@extends('layouts.admin')

@section('title', 'Orders Management')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Orders</li>
@endsection

@section('content')
            <!-- Order Statistics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $orders->where('status', 'pending')->count() }}</h3>
                            <p>Pending Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $orders->where('status', 'completed')->count() }}</h3>
                            <p>Completed Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            @php
                                $overdueCount = $orders->filter(function($order) {
                                    return $order->status === 'pending' && $order->isOverdue();
                                })->count();
                            @endphp
                            <h3>{{ $overdueCount }}</h3>
                            <p>Overdue Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>₹{{ number_format($orders->where('status', 'pending')->sum('total_amount'), 0) }}</h3>
                            <p>Pending Value</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filters</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('orders.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Order number or customer name">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="customer_id">Customer</label>
                                    <select class="form-control" id="customer_id" name="customer_id">
                                        <option value="">All Customers</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="overdue" name="overdue" value="1" 
                                           {{ request('overdue') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="overdue">
                                        Show Overdue Orders Only
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Orders List</h3>
                    <div class="card-tools">
                        <a href="{{ route('manufacturing.requirements.index') }}" class="btn btn-warning btn-sm mr-2">
                            <i class="fas fa-tools"></i> Manufacturing Requirements
                        </a>
                        <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create New Order
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Customer</th>
                                        <th>Order Date</th>
                                        <th>Delivery Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Manufacturing</th>
                                        <th>Items</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr data-order-id="{{ $order->id }}" data-order-number="{{ $order->order_number }}">
                                            <td>
                                                <a href="{{ route('orders.show', $order) }}" class="text-primary">
                                                    {{ $order->order_number }}
                                                </a>
                                            </td>
                                            <td>{{ $order->customer->name }}</td>
                                            <td>{{ $order->order_date->format('d/m/Y') }}</td>
                                            <td>
                                                @if($order->delivery_date)
                                                    {{ $order->delivery_date->format('d/m/Y') }}
                                                    @if($order->isOverdue())
                                                        <span class="badge badge-danger ml-1">Overdue</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td>₹{{ number_format($order->total_amount, 2) }}</td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'badge-warning',
                                                        'completed' => 'badge-success', 
                                                        'cancelled' => 'badge-danger'
                                                    ];
                                                    $statusIcons = [
                                                        'pending' => 'fas fa-clock',
                                                        'completed' => 'fas fa-check-circle',
                                                        'cancelled' => 'fas fa-times-circle'
                                                    ];
                                                @endphp
                                                <span class="badge {{ $statusClasses[$order->status] ?? 'badge-secondary' }}">
                                                    <i class="{{ $statusIcons[$order->status] ?? 'fas fa-question' }}"></i>
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                                @if($order->status === 'pending' && $order->isOverdue())
                                                    <br><span class="badge badge-danger mt-1">
                                                        <i class="fas fa-exclamation-triangle"></i> Overdue
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($order->status === 'pending')
                                                    @php
                                                        // Enhanced stock availability check with proper composite handling
                                                        $canManufacture = true;
                                                        $shortageCount = 0;
                                                        $debugInfo = [];
                                                        
                                                        foreach($order->items as $item) {
                                                            if($item->colorVariant) {
                                                                $needed = $item->quantity;
                                                                $available = $item->colorVariant->quantity ?? 0;
                                                                $product = $item->colorVariant->product;
                                                                
                                                                // If quantity is null, treat as unlimited stock for now
                                                                if($item->colorVariant->quantity === null) {
                                                                    $debugInfo[] = "Stock not tracked for {$item->colorVariant->color}";
                                                                    // Consider null as sufficient stock (untracked inventory)
                                                                } else {
                                                                    if($product->is_composite) {
                                                                        // For composite products: check assembly capability
                                                                        $hasFinishedStock = $available >= $needed;
                                                                        if($hasFinishedStock) {
                                                                            $debugInfo[] = "Composite {$item->colorVariant->color}: {$available} finished available (need {$needed})";
                                                                        } else {
                                                                            $shortfall = $needed - $available;
                                                                            $canAssemble = $product->canAssemble($shortfall);
                                                                            if($canAssemble) {
                                                                                $debugInfo[] = "Composite {$item->colorVariant->color}: {$available} finished + {$shortfall} can assemble";
                                                                            } else {
                                                                                $canManufacture = false;
                                                                                $shortageCount++;
                                                                                $debugInfo[] = "Composite {$item->colorVariant->color}: Cannot assemble {$shortfall} units - component shortage";
                                                                            }
                                                                        }
                                                                    } else {
                                                                        // For simple products: direct stock check
                                                                        $debugInfo[] = "Simple {$item->colorVariant->color}: Need: {$needed}, Available: {$available}";
                                                                        if($needed > $available) {
                                                                            $canManufacture = false;
                                                                            $shortageCount++;
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $canManufacture = false;
                                                                $shortageCount++;
                                                                $debugInfo[] = "No color variant found";
                                                            }
                                                        }
                                                    @endphp
                                                    @if($canManufacture)
                                                        <span class="badge badge-success" title="Stock Status: {{ implode(', ', $debugInfo) }}">
                                                            <i class="fas fa-check"></i> Ready
                                                        </span>
                                                    @else
                                                        <span class="badge badge-warning" title="Stock Issues: {{ implode(', ', $debugInfo) }}">
                                                            <i class="fas fa-tools"></i> Need Mfg ({{ $shortageCount }})
                                                        </span>
                                                    @endif
                                                @elseif($order->status === 'completed')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-industry"></i> Complete
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-minus"></i> N/A
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $order->items->count() }} items</span>
                                                <span class="badge badge-secondary">{{ $order->total_quantity }} units</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('orders.show', $order) }}" 
                                                       class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($order->status === 'pending')
                                                        <a href="{{ route('orders.edit', $order) }}" 
                                                           class="btn btn-warning btn-sm" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                        @php
                                            // Check if order is ready for invoice generation
                                            $isReadyForInvoice = true;
                                            foreach($order->items as $item) {
                                                if($item->colorVariant) {
                                                    $needed = $item->quantity;
                                                    $available = $item->colorVariant->quantity ?? 0;
                                                    $product = $item->colorVariant->product;
                                                    
                                                    // Enhanced logic for composite vs simple products
                                                    if($item->colorVariant->quantity === null) {
                                                        // Untracked inventory - cannot proceed
                                                        $isReadyForInvoice = false;
                                                        break;
                                                    } elseif($product->is_composite) {
                                                        // For composite products: check if can be assembled OR already have finished stock
                                                        $hasFinishedStock = $available >= $needed;
                                                        $canAssemble = $hasFinishedStock ? true : $product->canAssemble($needed - $available);
                                                        
                                                        if (!$hasFinishedStock && !$canAssemble) {
                                                            $isReadyForInvoice = false;
                                                            break;
                                                        }
                                                    } else {
                                                        // For simple products: check direct stock availability
                                                        if($needed > $available) {
                                                            $isReadyForInvoice = false;
                                                            break;
                                                        }
                                                    }
                                                } else {
                                                    $isReadyForInvoice = false;
                                                    break;
                                                }
                                            }
                                        @endphp                                                        @if($isReadyForInvoice)
                                                            <button type="button" class="btn btn-success btn-sm" 
                                                                    title="Generate Invoice - All items ready (finished stock or can be assembled)"
                                                                    onclick="showInvoiceTypeModal({{ $order->id }})">
                                                                <i class="fas fa-file-invoice"></i>
                                                            </button>
                                                        @else
                                                            <span class="btn btn-success btn-sm disabled" title="Cannot generate invoice - insufficient stock and cannot manufacture required quantities">
                                                                <i class="fas fa-file-invoice text-muted"></i>
                                                            </span>
                                                        @endif
                                                        
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                title="Cancel Order" data-toggle="modal" 
                                                                data-target="#cancelModal{{ $order->id }}">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @elseif($order->status === 'completed')
                                                        <span class="btn btn-success btn-sm disabled" title="Invoice Generated">
                                                            <i class="fas fa-check"></i> Invoiced
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                @if($order->status === 'pending')
                                                <!-- Cancel Order Modal -->
                                                <div class="modal fade" id="cancelModal{{ $order->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Cancel Order</h5>
                                                                <button type="button" class="close" data-dismiss="modal">
                                                                    <span>&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to cancel order <strong>{{ $order->order_number }}</strong>?
                                                                <br><small class="text-muted">This action cannot be undone.</small>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <form action="{{ route('orders.cancel', $order) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No orders found</h5>
                            <p class="text-muted">Create your first order to get started.</p>
                            <a href="{{ route('orders.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create New Order
                            </a>
                        </div>
                    @endif
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
                <p>Choose the type of invoice you want to generate for this order:</p>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('#customer_id, #status').change(function() {
        $(this).closest('form').submit();
    });
    
    // Auto-refresh every 5 minutes for real-time updates (only if no filters are applied)
    @if(!request()->hasAny(['search', 'customer_id', 'status', 'start_date', 'end_date', 'overdue']))
    setInterval(function() {
        console.log('Auto-refreshing orders list...');
        window.location.reload();
    }, 300000); // 5 minutes
    @endif
    
    // Add smooth animations for status badges
    $('.badge').hover(
        function() { $(this).addClass('shadow-sm'); },
        function() { $(this).removeClass('shadow-sm'); }
    );
});

// Global variable to store the current order ID for invoice generation
let currentOrderId = null;

function showInvoiceTypeModal(orderId) {
    // First, confirm the action
    const orderElement = document.querySelector(`[data-order-id="${orderId}"]`);
    const orderNumber = orderElement ? orderElement.dataset.orderNumber : orderId;
    
    confirmInvoiceGeneration(orderNumber, function() {
        currentOrderId = orderId;
        $('#invoiceTypeModal').modal('show');
    });
}

function selectInvoiceType(type) {
    $('#invoiceTypeModal').modal('hide');
    
    if (currentOrderId && type) {
        if (type === 'gst') {
            window.location.href = "{{ route('invoices.gst.create') }}?order_id=" + currentOrderId;
        } else if (type === 'non_gst') {
            window.location.href = "{{ route('invoices.non_gst.create') }}?order_id=" + currentOrderId;
        }
    }
}
</script>
@endpush
