@extends('layouts.admin')

@section('title', 'Low Stock Colors')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('colors.index') }}">Colors</a></li>
<li class="breadcrumb-item active">Low Stock</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                Low Stock Colors
            </h3>
            <div>
                <a href="{{ route('colors.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to All Colors
                </a>
                <a href="{{ route('colors.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Color
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($colors->count() > 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Alert!</strong> The following {{ $colors->count() }} color(s) are at or below their minimum stock threshold.
                Please consider restocking these colors to avoid production delays.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Color</th>
                            <th>Current Stock</th>
                            <th>Minimum Stock</th>
                            <th>Shortage</th>
                            <th>Used in Products</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($colors as $color)
                            <tr class="table-warning">
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($color->hex_code)
                                            <div class="color-preview mr-2" style="width: 20px; height: 20px; background-color: {{ $color->hex_code }}; border: 1px solid #ddd; border-radius: 3px;"></div>
                                        @endif
                                        <div>
                                            <strong>{{ $color->name }}</strong>
                                            @if($color->description)
                                                <br><small class="text-muted">{{ Str::limit($color->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-danger badge-lg">
                                        {{ number_format($color->stock_grams, 2) }}g
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ number_format($color->minimum_stock, 2) }}g</span>
                                </td>
                                <td>
                                    @php
                                        $shortage = $color->minimum_stock - $color->stock_grams;
                                    @endphp
                                    @if($shortage > 0)
                                        <span class="text-danger font-weight-bold">
                                            -{{ number_format($shortage, 2) }}g
                                        </span>
                                    @else
                                        <span class="text-warning">
                                            At threshold
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $color->productColorVariants->count() }}</span>
                                    @if($color->productColorVariants->count() > 0)
                                        <small class="text-muted d-block">products</small>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $color->updated_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('colors.show', $color) }}" class="btn btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#stockModal" 
                                                data-color-id="{{ $color->id }}" data-color-name="{{ $color->name }}" 
                                                data-current-stock="{{ $color->stock_grams }}" data-action="inward" title="Add Stock">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <a href="{{ route('colors.edit', $color) }}" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Low Stock Colors</span>
                            <span class="info-box-number">{{ $colors->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Out of Stock</span>
                            <span class="info-box-number">{{ $colors->where('stock_grams', 0)->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Affected Products</span>
                            <span class="info-box-number">{{ $colors->sum(function($color) { return $color->productColorVariants->count(); }) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-weight"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Stock</span>
                            <span class="info-box-number">{{ number_format($colors->sum('stock_grams'), 1) }}g</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-lightbulb text-warning"></i>
                        Restocking Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($colors->take(6) as $color)
                            @php
                                $recommendedOrder = max($color->minimum_stock * 2, $color->minimum_stock + 500); // Recommend 2x minimum or +500g
                                $shortage = $color->minimum_stock - $color->stock_grams;
                            @endphp
                            <div class="col-md-4 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            @if($color->hex_code)
                                                <div class="color-preview mr-2" style="width: 15px; height: 15px; background-color: {{ $color->hex_code }}; border: 1px solid #ddd; border-radius: 2px;"></div>
                                            @endif
                                            <strong class="text-truncate">{{ $color->name }}</strong>
                                        </div>
                                        <small class="text-muted">
                                            Current: {{ number_format($color->stock_grams, 1) }}g<br>
                                            Minimum: {{ number_format($color->minimum_stock, 1) }}g<br>
                                            <span class="text-success">Recommended order: {{ number_format($recommendedOrder, 1) }}g</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        @else
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <strong>Great!</strong> All colors are currently above their minimum stock thresholds.
                No immediate restocking is required.
            </div>
            
            <div class="text-center py-5">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem; opacity: 0.3;"></i>
                <h4 class="mt-3 text-muted">All Colors Well Stocked</h4>
                <p class="text-muted">Your color inventory levels are healthy.</p>
                <a href="{{ route('colors.index') }}" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Colors
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="stockForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus mr-2"></i>
                        Add Stock for <span id="modalColorName"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="change_type" value="inward">
                    
                    <div class="form-group">
                        <label>Current Stock</label>
                        <input type="text" id="modalCurrentStock" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity to Add (grams) *</label>
                        <input type="number" name="quantity_grams" class="form-control" step="0.01" min="0.01" required placeholder="Enter quantity in grams">
                        <small class="form-text text-muted">This will be added to the current stock</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Optional remarks (e.g., Supplier, Purchase Order #, etc.)"></textarea>
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
@endsection

@push('scripts')
<script>
$('#stockModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var colorId = button.data('color-id');
    var colorName = button.data('color-name');
    var currentStock = button.data('current-stock');
    
    var modal = $(this);
    modal.find('#modalColorName').text(colorName);
    modal.find('#modalCurrentStock').val(currentStock + 'g');
    
    // Update form action
    modal.find('#stockForm').attr('action', `/colors/${colorId}/update-stock`);
});

$('#stockModal').on('hidden.bs.modal', function () {
    $(this).find('form')[0].reset();
});

// Auto-refresh page every 5 minutes to show updated stock levels
setTimeout(function() {
    location.reload();
}, 300000); // 5 minutes
</script>
@endpush

@push('styles')
<style>
.color-preview {
    flex-shrink: 0;
}

.info-box {
    border-radius: 0.375rem;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}

.card.border-warning {
    border-width: 2px;
}

@keyframes pulse-warning {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

.table-warning:hover {
    animation: pulse-warning 2s infinite;
}
</style>
@endpush
