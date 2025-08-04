@extends('layouts.admin')

@section('title', 'Color Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('colors.index') }}">Colors</a></li>
<li class="breadcrumb-item active">{{ $color->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Color Information</h3>
                    <div>
                        <a href="{{ route('colors.edit', $color) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Color
                        </a>
                        <a href="{{ route('colors.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Color Name:</dt>
                            <dd class="col-sm-8">
                                <div class="d-flex align-items-center">
                                    @if($color->hex_code)
                                        <div class="color-preview mr-2" style="width: 25px; height: 25px; background-color: {{ $color->hex_code }}; border: 1px solid #ddd; border-radius: 3px;"></div>
                                    @endif
                                    <strong>{{ $color->name }}</strong>
                                </div>
                            </dd>

                            <dt class="col-sm-4">Hex Code:</dt>
                            <dd class="col-sm-8">
                                @if($color->hex_code)
                                    <code>{{ $color->hex_code }}</code>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </dd>

                            <dt class="col-sm-4">Current Stock:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-{{ $color->stock_status }} badge-lg">
                                    {{ number_format($color->stock_grams, 2) }}g
                                </span>
                            </dd>

                            <dt class="col-sm-4">Minimum Stock:</dt>
                            <dd class="col-sm-8">{{ number_format($color->minimum_stock, 2) }}g</dd>

                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                @if($color->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Description:</dt>
                            <dd class="col-sm-8">
                                @if($color->description)
                                    {{ $color->description }}
                                @else
                                    <span class="text-muted">No description provided</span>
                                @endif
                            </dd>

                            <dt class="col-sm-4">Used in Products:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-info">{{ $color->productColorVariants->count() }}</span>
                                @if($color->productColorVariants->count() > 0)
                                    <small class="text-muted d-block">products using this color</small>
                                @endif
                            </dd>

                            <dt class="col-sm-4">Created:</dt>
                            <dd class="col-sm-8">{{ $color->created_at->format('M d, Y H:i') }}</dd>

                            <dt class="col-sm-4">Last Updated:</dt>
                            <dd class="col-sm-8">{{ $color->updated_at->format('M d, Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Stock Management -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Stock Management</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h4 class="text-{{ $color->stock_status }}">
                        {{ number_format($color->stock_grams, 2) }}g
                    </h4>
                    <small class="text-muted">Current Stock</small>
                </div>

                <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#stockModal" 
                            data-color-id="{{ $color->id }}" data-color-name="{{ $color->name }}" 
                            data-current-stock="{{ $color->stock_grams }}" data-action="inward">
                        <i class="fas fa-plus"></i> Add Stock
                    </button>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#stockModal" 
                            data-color-id="{{ $color->id }}" data-color-name="{{ $color->name }}" 
                            data-current-stock="{{ $color->stock_grams }}" data-action="outward">
                        <i class="fas fa-minus"></i> Reduce Stock
                    </button>
                </div>

                @if($color->stock_grams <= $color->minimum_stock)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Low Stock Alert!</strong><br>
                        Current stock is at or below minimum threshold.
                    </div>
                @endif
            </div>
        </div>

        <!-- Products Using This Color -->
        @if($color->productColorVariants->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Products Using This Color</h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($color->productColorVariants->take(10) as $variant)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $variant->product->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Usage: {{ number_format($variant->color_usage_grams, 2) }}g per unit
                                    </small>
                                </div>
                                <span class="badge badge-primary">{{ $variant->quantity }} pcs</span>
                            </div>
                        </div>
                    @endforeach
                    @if($color->productColorVariants->count() > 10)
                        <div class="list-group-item px-0 text-center">
                            <small class="text-muted">
                                ... and {{ $color->productColorVariants->count() - 10 }} more products
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Recent Stock Movements -->
@if($color->stockLogs->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Recent Stock Movements</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($color->stockLogs->take(10) as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @if($log->change_type === 'inward')
                                    <span class="badge badge-success">Inward</span>
                                @else
                                    <span class="badge badge-danger">Outward</span>
                                @endif
                            </td>
                            <td>
                                @if($log->change_type === 'inward')
                                    <span class="text-success">+{{ number_format($log->quantity_grams, 2) }}g</span>
                                @else
                                    <span class="text-danger">-{{ number_format($log->quantity_grams, 2) }}g</span>
                                @endif
                            </td>
                            <td>{{ $log->remarks ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="stockForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Stock for <span id="modalColorName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="change_type" id="modalChangeType">
                    
                    <div class="form-group">
                        <label>Current Stock</label>
                        <input type="text" id="modalCurrentStock" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity (grams) *</label>
                        <input type="number" name="quantity_grams" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Optional remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
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
    var action = button.data('action');
    
    var modal = $(this);
    modal.find('#modalColorName').text(colorName);
    modal.find('#modalCurrentStock').val(currentStock + 'g');
    modal.find('#modalChangeType').val(action);
    
    // Update form action
    modal.find('#stockForm').attr('action', `/colors/${colorId}/update-stock`);
    
    // Update modal header color
    if(action === 'inward') {
        modal.find('.modal-header').removeClass('bg-danger').addClass('bg-success text-white');
        modal.find('.modal-title').prepend('<i class="fas fa-plus mr-2"></i>');
    } else {
        modal.find('.modal-header').removeClass('bg-success').addClass('bg-danger text-white');
        modal.find('.modal-title').prepend('<i class="fas fa-minus mr-2"></i>');
    }
});

$('#stockModal').on('hidden.bs.modal', function () {
    $(this).find('.modal-header').removeClass('bg-success bg-danger text-white');
    $(this).find('.modal-title i').remove();
    $(this).find('form')[0].reset();
});
</script>
@endpush
