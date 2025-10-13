@extends('layouts.admin')

@section('title', 'Colors Management')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Colors</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Colors Management</h3>
            <a href="{{ route('colors.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Color
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Search and Filter Form -->
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search colors..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
                <div class="col-md-3 text-right">
                    <a href="{{ route('colors.low-stock') }}" class="btn btn-warning">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock Colors
                    </a>
                </div>
            </div>
        </form>

        <!-- Colors Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Color</th>
                        <th>Stock (grams)</th>
                        <th>Minimum Stock</th>
                        <th>Status</th>
                        <th>Used in Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colors as $color)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($color->hex_code)
                                        <div class="color-preview mr-2" style="width: 20px; height: 20px; background-color: {{ $color->hex_code }}; border: 1px solid #ddd; border-radius: 3px;"></div>
                                    @endif
                                    <strong>{{ $color->name }}</strong>
                                </div>
                                @if($color->description)
                                    <small class="text-muted">{{ $color->description }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $color->stock_status }}">
                                    {{ number_format($color->stock_grams, 2) }}g
                                </span>
                            </td>
                            <td>{{ number_format($color->minimum_stock, 2) }}g</td>
                            <td>
                                @if($color->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $color->productColorVariants->count() }}</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('colors.show', $color) }}" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('colors.edit', $color) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#stockModal" 
                                            data-color-id="{{ $color->id }}" data-color-name="{{ $color->name }}" 
                                            data-current-stock="{{ $color->stock_grams }}" data-action="inward" title="Add Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#stockModal" 
                                            data-color-id="{{ $color->id }}" data-color-name="{{ $color->name }}" 
                                            data-current-stock="{{ $color->stock_grams }}" data-action="outward" title="Reduce Stock">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No colors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{ $colors->links() }}
    </div>
</div>

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

// Add error handling for stock updates
$('#stockForm').on('submit', function(e) {
    e.preventDefault();
    
    var form = $(this);
    var formData = form.serialize();
    var action = form.attr('action');
    
    $.ajax({
        url: action,
        type: 'POST',
        data: formData,
        success: function(response) {
            $('#stockModal').modal('hide');
            location.reload(); // Refresh to show updated stock
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors || {};
            var errorMessage = 'Failed to update stock.';
            
            if (xhr.responseJSON?.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            alert(errorMessage);
        }
    });
});
</script>
@endpush
