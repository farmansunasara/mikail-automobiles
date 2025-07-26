@extends('layouts.admin')

@section('title', 'Stock Management - Color Variants')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Stock Management</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manage Product Stock (Color Variants)</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('stock.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="color" class="form-control">
                        <option value="">All Colors</option>
                        @foreach($colors as $color)
                            <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>
                                {{ ucfirst($color) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="stock_status" class="form-control">
                        <option value="">All Stock Levels</option>
                        <option value="critical" {{ request('stock_status') == 'critical' ? 'selected' : '' }}>Critical (≤5)</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low (≤10)</option>
                        <option value="medium" {{ request('stock_status') == 'medium' ? 'selected' : '' }}>Medium (≤20)</option>
                        <option value="good" {{ request('stock_status') == 'good' ? 'selected' : '' }}>Good (>20)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('stock.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Color Variants & Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="font-weight-bold">{{ $product->name }}</a>
                            <br>
                            <small class="text-muted">{{ $product->subcategory->name }}</small>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $product->category->name }}</span>
                        </td>
                        <td>
                            <div class="color-variants-container">
                                @forelse($product->colorVariants as $variant)
                                    <div class="color-variant-item mb-2 p-2 border rounded">
                                        <div class="row align-items-center">
                                            <div class="col-4">
                                                @php
                                                    $colors = [
                                                        'red' => '#dc3545',
                                                        'blue' => '#007bff',
                                                        'green' => '#28a745',
                                                        'yellow' => '#ffc107',
                                                        'orange' => '#fd7e14',
                                                        'purple' => '#6f42c1',
                                                        'pink' => '#e83e8c',
                                                        'black' => '#343a40',
                                                        'white' => '#f8f9fa',
                                                        'gray' => '#6c757d',
                                                        'grey' => '#6c757d',
                                                        'brown' => '#795548',
                                                        'silver' => '#c0c0c0',
                                                        'gold' => '#ffd700',
                                                        'golden' => '#ffd700',
                                                        'clear' => '#e9ecef',
                                                        'mixed' => '#6c757d'
                                                    ];
                                                    $darkColors = ['black', 'blue', 'purple', 'brown', 'gray', 'grey', 'mixed'];
                                                    $bgColor = $colors[strtolower($variant->color)] ?? '#6c757d';
                                                    $textColor = in_array(strtolower($variant->color), $darkColors) ? '#ffffff' : '#000000';
                                                @endphp
                                                <span class="badge badge-pill" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                                    {{ ucfirst($variant->color) }}
                                                </span>
                                            </div>
                                            <div class="col-4">
                                                <span class="badge badge-{{ $variant->quantity > 20 ? 'success' : ($variant->quantity > 10 ? 'warning' : 'danger') }} badge-lg">
                                                    {{ $variant->quantity }}
                                                </span>
                                                @if($variant->quantity <= 5)
                                                    <small class="text-danger d-block">Critical</small>
                                                @elseif($variant->quantity <= 10)
                                                    <small class="text-warning d-block">Low</small>
                                                @endif
                                            </div>
                                            <div class="col-4">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-success" data-toggle="modal" data-target="#stockModal" 
                                                            data-product-id="{{ $product->id }}" 
                                                            data-product-name="{{ $product->name }}" 
                                                            data-color-variant-id="{{ $variant->id }}"
                                                            data-color="{{ $variant->color }}"
                                                            data-change-type="inward"
                                                            title="Add Stock">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <button class="btn btn-danger" data-toggle="modal" data-target="#stockModal" 
                                                            data-product-id="{{ $product->id }}" 
                                                            data-product-name="{{ $product->name }}" 
                                                            data-color-variant-id="{{ $variant->id }}"
                                                            data-color="{{ $variant->color }}"
                                                            data-change-type="outward"
                                                            title="Remove Stock">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">No color variants available</div>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('stock.product', $product) }}" class="btn btn-sm btn-info" title="View History">
                                <i class="fas fa-history"></i> History
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- Stock Modal -->
<div class="modal fade" id="stockModal" tabindex="-1" role="dialog" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockModalLabel">Update Stock for <span id="modalProductName"></span> (<span id="modalColorName"></span>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('stock.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="modalProductId">
                    <input type="hidden" name="color_variant_id" id="modalColorVariantId">
                    <input type="hidden" name="change_type" id="modalChangeType">
                    
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
        var productId = button.data('product-id');
        var productName = button.data('product-name');
        var colorVariantId = button.data('color-variant-id');
        var color = button.data('color');
        var changeType = button.data('change-type');
        
        var modal = $(this);
        modal.find('.modal-title #modalProductName').text(productName);
        modal.find('.modal-title #modalColorName').text(color);
        modal.find('.modal-body #modalProductId').val(productId);
        modal.find('.modal-body #modalColorVariantId').val(colorVariantId);
        modal.find('.modal-body #modalChangeType').val(changeType);

        if(changeType === 'inward') {
            modal.find('.modal-header').css('background-color', '#28a745').css('color', 'white');
        } else {
            modal.find('.modal-header').css('background-color', '#dc3545').css('color', 'white');
        }
    });
</script>
@endpush

<style>
.color-variant-item {
    background-color: #f8f9fa;
}
.color-variants-container {
    max-height: 300px;
    overflow-y: auto;
}
</style>
