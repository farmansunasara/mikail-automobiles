@extends('layouts.admin')

@section('title', 'Manufacturing Requirements')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Manufacturing Requirements</li>
@endsection

@push('styles')
<style>
.component-breakdown {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
}

.component-breakdown th {
    background-color: #e9ecef;
    font-size: 0.85rem;
}

.assembly-readiness {
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
}

.badge-composite {
    background-color: #17a2b8;
    color: white;
}

.badge-simple {
    background-color: #28a745;
    color: white;
}

.progress-sm {
    height: 0.5rem;
}

.table-sm th,
.table-sm td {
    padding: 0.3rem;
    font-size: 0.875rem;
}

.collapse-toggle {
    transition: all 0.3s ease;
}

.collapse-toggle:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.component-status-sufficient {
    background-color: rgba(40, 167, 69, 0.1);
}

.component-status-insufficient {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['total_shortages'] }}</h3>
                    <p>Total Shortages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $summary['total_shortage_quantity'] }}</h3>
                    <p>Units Needed</p>
                </div>
                <div class="icon">
                    <i class="fas fa-industry"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ count($summary['urgent_requirements']) }}</h3>
                    <p>Urgent (≤3 days)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $summary['pending_orders_count'] }}</h3>
                    <p>Pending Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    @php
                        $compositeCount = collect($requirements)->where('is_composite', true)->count();
                    @endphp
                    <h3>{{ $compositeCount }}</h3>
                    <p>Composite Products</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    @php
                        $simpleCount = collect($requirements)->where('is_composite', false)->count();
                    @endphp
                    <h3>{{ $simpleCount }}</h3>
                    <p>Simple Products</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cube"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Manufacturing Requirements -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tools"></i> Manufacturing Requirements (Dynamic)
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm" onclick="refreshRequirements()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(empty($requirements))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Great!</strong> All orders can be fulfilled with current stock. No manufacturing required.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Priority</th>
                                <th>Product</th>
                                <th>Color</th>
                                <th>Current Stock</th>
                                <th>Total Demand</th>
                                <th class="text-danger">Shortage</th>
                                <th>Type</th>
                                <th>Component Requirements</th>
                                <th>Orders Affected</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requirements as $requirement)
                            <tr class="{{ $requirement['priority'] <= 3 ? 'table-warning' : '' }}">
                                <td>
                                    @if($requirement['priority'] <= 3)
                                        <span class="badge badge-danger">Urgent ({{ $requirement['priority'] }} days)</span>
                                    @else
                                        <span class="badge badge-info">{{ $requirement['priority'] }} days</span>
                                    @endif
                                </td>
                                <td><strong>{{ $requirement['product_name'] }}</strong></td>
                                <td>
                                    <span class="badge badge-secondary">{{ $requirement['color'] }}</span>
                                </td>
                                <td>{{ $requirement['current_stock'] }}</td>
                                <td>{{ $requirement['total_demand'] }}</td>
                                <td class="text-danger">
                                    <strong>{{ $requirement['shortage'] }}</strong>
                                </td>
                                <td>
                                    @if($requirement['is_composite'])
                                        <span class="badge badge-info">
                                            <i class="fas fa-cubes"></i> Composite
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="fas fa-cube"></i> Simple
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($requirement['is_composite'] && !empty($requirement['component_requirements']))
                                        <button type="button" class="btn btn-outline-primary btn-sm collapse-toggle" 
                                                data-toggle="collapse" 
                                                data-target="#components{{ $requirement['variant_id'] }}"
                                                aria-expanded="false">
                                            <i class="fas fa-list"></i> View Components ({{ count($requirement['component_requirements']) }})
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                            data-toggle="modal" 
                                            data-target="#ordersModal{{ $requirement['variant_id'] }}">
                                        {{ count($requirement['orders']) }} order(s)
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm" 
                                            data-toggle="modal" 
                                            data-target="#addStockModal{{ $requirement['variant_id'] }}">
                                        <i class="fas fa-plus"></i> Add Stock
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Component Requirements Expandable Row -->
                            @if($requirement['is_composite'] && !empty($requirement['component_requirements']))
                            <tr>
                                <td colspan="10" class="p-0">
                                    <div class="collapse" id="components{{ $requirement['variant_id'] }}">
                                        <div class="card card-body m-2 component-breakdown">
                                            <h6 class="mb-3">
                                                <i class="fas fa-cubes text-info"></i> 
                                                Component Requirements for {{ $requirement['shortage'] }} units of {{ $requirement['product_name'] }} ({{ $requirement['color'] }})
                                            </h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Component</th>
                                                            <th>Per Unit</th>
                                                            <th>Total Needed</th>
                                                            <th>Available Stock</th>
                                                            <th>Shortage</th>
                                                            <th>Status</th>
                                                            <th>Color Breakdown</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($requirement['component_requirements'] as $component)
                                                        <tr class="{{ $component['shortage'] > 0 ? 'component-status-insufficient' : 'component-status-sufficient' }}">
                                                            <td><strong>{{ $component['component_name'] }}</strong></td>
                                                            <td>{{ $component['quantity_per_unit'] }}</td>
                                                            <td><strong>{{ $component['total_needed'] }}</strong></td>
                                                            <td>{{ $component['available_stock'] }}</td>
                                                            <td>
                                                                @if($component['shortage'] > 0)
                                                                    <span class="text-danger"><strong>{{ $component['shortage'] }}</strong></span>
                                                                @else
                                                                    <span class="text-success">0</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($component['shortage'] > 0)
                                                                    <span class="badge badge-danger">Insufficient</span>
                                                                    <button type="button" class="btn btn-sm btn-warning ml-2" 
                                                                            data-toggle="modal" 
                                                                            data-target="#addComponentStockModal{{ $component['component_product_id'] }}">
                                                                        <i class="fas fa-plus"></i> Add Stock
                                                                    </button>
                                                                @else
                                                                    <span class="badge badge-success">Sufficient</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(!empty($component['color_breakdown']))
                                                                    <div class="d-flex flex-wrap">
                                                                        @foreach($component['color_breakdown'] as $colorInfo)
                                                                            <small class="badge badge-secondary mr-1 mb-1">
                                                                                {{ $colorInfo['color'] }}: {{ $colorInfo['available'] }}
                                                                                @if($colorInfo['can_use'] < $colorInfo['available'])
                                                                                    (use {{ $colorInfo['can_use'] }})
                                                                                @endif
                                                                            </small>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <span class="text-muted">No stock</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <!-- Component Summary -->
                                            @php
                                                $totalComponents = count($requirement['component_requirements']);
                                                $sufficientComponents = count(array_filter($requirement['component_requirements'], function($comp) {
                                                    return $comp['shortage'] == 0;
                                                }));
                                                $componentReadiness = $totalComponents > 0 ? ($sufficientComponents / $totalComponents) * 100 : 0;
                                            @endphp
                                            
                                            <div class="mt-3 p-3 assembly-readiness">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>Assembly Readiness: {{ number_format($componentReadiness, 1) }}%</strong>
                                                        <br>
                                                        <small class="text-muted">({{ $sufficientComponents }}/{{ $totalComponents }} components available)</small>
                                                    </div>
                                                    <div class="progress" style="width: 200px; height: 25px;">
                                                        <div class="progress-bar {{ $componentReadiness == 100 ? 'bg-success' : ($componentReadiness > 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ $componentReadiness }}%">
                                                             {{ number_format($componentReadiness, 0) }}%
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($componentReadiness == 100)
                                                    <div class="mt-2">
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check"></i> Ready for Assembly
                                                        </span>
                                                    </div>
                                                @elseif($componentReadiness > 0)
                                                    <div class="mt-2">
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-exclamation-triangle"></i> Partial Assembly Possible
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="mt-2">
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times"></i> Cannot Assemble - Component Shortage
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Ready Orders Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-check-circle"></i> Orders Ready for Invoice
            </h3>
        </div>
        <div class="card-body">
            @php
                $readyOrdersList = array_filter($readyOrders, function($order) {
                    return $order['can_fulfill'];
                });
            @endphp
            
            @if(empty($readyOrdersList))
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No orders are ready for invoice generation. Complete manufacturing requirements first.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Order Date</th>
                                <th>Delivery Date</th>
                                <th>Total Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readyOrdersList as $orderData)
                            @php $order = $orderData['order']; @endphp
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
                                    <button type="button" class="btn btn-primary btn-sm" 
                                            onclick="showInvoiceTypeModal({{ $order->id }})">
                                        <i class="fas fa-file-invoice"></i> Generate Invoice
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals for Add Stock -->
@foreach($requirements as $requirement)
<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal{{ $requirement['variant_id'] }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('manufacturing.requirements.add-stock') }}">
                @csrf
                <input type="hidden" name="variant_id" value="{{ $requirement['variant_id'] }}">
                <div class="modal-header">
                    <h4 class="modal-title">Add Manufactured Stock</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Product:</strong><br>
                            {{ $requirement['product_name'] }}
                        </div>
                        <div class="col-md-6">
                            <strong>Color:</strong><br>
                            <span class="badge badge-secondary">{{ $requirement['color'] }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Current Stock:</strong><br>
                            {{ $requirement['current_stock'] }}
                        </div>
                        <div class="col-md-4">
                            <strong>Total Demand:</strong><br>
                            {{ $requirement['total_demand'] }}
                        </div>
                        <div class="col-md-4">
                            <strong>Shortage:</strong><br>
                            <span class="text-danger">{{ $requirement['shortage'] }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="quantity{{ $requirement['variant_id'] }}">Quantity Manufactured <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control" 
                               id="quantity{{ $requirement['variant_id'] }}" 
                               name="quantity" 
                               min="1" 
                               max="9999"
                               value="{{ $requirement['shortage'] }}" 
                               required>
                        <small class="form-text text-muted">
                            Recommended: {{ $requirement['shortage'] }} units to fulfill shortage
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="notes{{ $requirement['variant_id'] }}">Notes (Optional)</label>
                        <textarea class="form-control" 
                                  id="notes{{ $requirement['variant_id'] }}" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Manufacturing notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Orders Modal -->
<div class="modal fade" id="ordersModal{{ $requirement['variant_id'] }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Orders Requiring {{ $requirement['product_name'] }} ({{ $requirement['color'] }})</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Quantity</th>
                                <th>Delivery Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requirement['orders'] as $orderInfo)
                            <tr>
                                <td>
                                    <a href="{{ route('orders.show', $orderInfo['order_id']) }}" class="text-primary">
                                        {{ $orderInfo['order_number'] }}
                                    </a>
                                </td>
                                <td>{{ $orderInfo['customer_name'] }}</td>
                                <td>{{ $orderInfo['quantity'] }}</td>
                                <td>
                                    @if($orderInfo['delivery_date'])
                                        {{ \Carbon\Carbon::parse($orderInfo['delivery_date'])->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

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

<!-- Component Stock Modals -->
@foreach($requirements as $requirement)
    @if($requirement['is_composite'] && !empty($requirement['component_requirements']))
        @foreach($requirement['component_requirements'] as $component)
            @if($component['shortage'] > 0)
<!-- Add Component Stock Modal for {{ $component['component_name'] }} -->
<div class="modal fade" id="addComponentStockModal{{ $component['component_product_id'] }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('manufacturing.requirements.add-component-stock') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-cube text-warning"></i> 
                        Add Component Stock: {{ $component['component_name'] }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Current Shortage:</strong> {{ $component['shortage'] }} units needed for assembly
                    </div>

                    <input type="hidden" name="component_product_id" value="{{ $component['component_product_id'] }}">
                    
                    <div class="form-group">
                        <label for="componentColor{{ $component['component_product_id'] }}">Color</label>
                        <select class="form-control" 
                                id="componentColor{{ $component['component_product_id'] }}" 
                                name="color" 
                                required>
                            <option value="">Select Color</option>
                            @if(!empty($component['color_breakdown']))
                                @foreach($component['color_breakdown'] as $colorInfo)
                                    <option value="{{ $colorInfo['color'] }}">
                                        {{ $colorInfo['color'] }} (Current: {{ $colorInfo['available'] }})
                                    </option>
                                @endforeach
                            @endif
                            <option value="Other">Other Color (specify in notes)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="componentQuantity{{ $component['component_product_id'] }}">Quantity to Add</label>
                        <input type="number" 
                               class="form-control" 
                               id="componentQuantity{{ $component['component_product_id'] }}" 
                               name="quantity" 
                               min="1" 
                               max="9999" 
                               value="{{ $component['shortage'] }}"
                               required>
                        <small class="form-text text-muted">
                            Suggested: {{ $component['shortage'] }} units (current shortage)
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="componentNotes{{ $component['component_product_id'] }}">Notes (Optional)</label>
                        <textarea class="form-control" 
                                  id="componentNotes{{ $component['component_product_id'] }}" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Add any notes about this stock addition..."></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> This will add raw component stock that can be used for assembly.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-plus"></i> Add Component Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
            @endif
        @endforeach
    @endif
@endforeach

@endsection

@push('scripts')
<script>
function refreshRequirements() {
    location.reload();
}

// Global variable to store the current order ID for invoice generation
let currentOrderId = null;

function showInvoiceTypeModal(orderId) {
    console.log('showInvoiceTypeModal called with orderId:', orderId);
    
    // Get order number for confirmation
    const orderElement = document.querySelector(`[data-order-id="${orderId}"]`);
    const orderNumber = orderElement ? orderElement.dataset.orderNumber : orderId;
    
    confirmInvoiceGeneration(orderNumber, function() {
        currentOrderId = orderId;
        $('#invoiceTypeModal').modal('show');
    });
}

function selectInvoiceType(type) {
    console.log('selectInvoiceType called with type:', type, 'orderId:', currentOrderId);
    $('#invoiceTypeModal').modal('hide');
    
    if (currentOrderId && type) {
        if (type === 'gst') {
            window.location.href = "{{ route('invoices.gst.create') }}?order_id=" + currentOrderId;
        } else if (type === 'non_gst') {
            window.location.href = "{{ route('invoices.non_gst.create') }}?order_id=" + currentOrderId;
        }
    }
}

// Handle component stock modal opening
$(document).ready(function() {
    // Auto-focus quantity input when component modal opens
    $('[id^="addComponentStockModal"]').on('shown.bs.modal', function () {
        $(this).find('input[name="quantity"]').focus().select();
    });
    
    // Update quantity field helper text based on shortage
    $('input[name="quantity"]').on('input', function() {
        const shortage = parseInt($(this).attr('max')) || 0;
        const entered = parseInt($(this).val()) || 0;
        const helpText = $(this).siblings('.form-text');
        
        if (entered > shortage) {
            helpText.html(`<span class="text-info">Adding ${entered - shortage} extra units beyond current shortage</span>`);
        } else if (entered === shortage) {
            helpText.html(`<span class="text-success">Perfect! This will cover the current shortage</span>`);
        } else {
            helpText.html(`<span class="text-warning">Still ${shortage - entered} units short after this addition</span>`);
        }
    });
});
</script>
@endpush