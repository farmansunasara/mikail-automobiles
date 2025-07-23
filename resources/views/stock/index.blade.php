@extends('layouts.admin')

@section('title', 'Stock Management')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Stock Management</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manage Product Stock</h3>
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
                        @foreach(\App\Models\Product::whereNotNull('color')->distinct()->pluck('color') as $color)
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

        <!-- Quick Stats -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Products</span>
                        <span class="info-box-number">{{ $products->total() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Critical Stock</span>
                        <span class="info-box-number">{{ $products->where('quantity', '<=', 5)->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Low Stock</span>
                        <span class="info-box-number">{{ $products->where('quantity', '<=', 10)->where('quantity', '>', 5)->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Good Stock</span>
                        <span class="info-box-number">{{ $products->where('quantity', '>', 20)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Color</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Stock Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="{{ $product->quantity <= 10 ? 'table-warning' : ($product->quantity <= 5 ? 'table-danger' : '') }}">
                        <td>{{ $product->id }}</td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="font-weight-bold">{{ $product->name }}</a>
                            <br>
                            <small class="text-muted">{{ $product->subcategory->name }}</small>
                        </td>
                        <td>
                            @if($product->color)
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
                                    $bgColor = $colors[strtolower($product->color)] ?? '#6c757d';
                                    $textColor = in_array(strtolower($product->color), $darkColors) ? '#ffffff' : '#000000';
                                @endphp
                                <span class="badge badge-pill" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                    {{ ucfirst($product->color) }}
                                </span>
                            @else
                                <span class="badge badge-secondary">No Color</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $product->category->name }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $product->quantity > 20 ? 'success' : ($product->quantity > 10 ? 'warning' : 'danger') }} badge-lg">
                                {{ $product->quantity }}
                            </span>
                        </td>
                        <td>
                            @if($product->quantity <= 5)
                                <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Critical</span>
                            @elseif($product->quantity <= 10)
                                <span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Low</span>
                            @elseif($product->quantity <= 20)
                                <span class="badge badge-info"><i class="fas fa-info-circle"></i> Medium</span>
                            @else
                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Good</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#stockModal" 
                                        data-product-id="{{ $product->id }}" 
                                        data-product-name="{{ $product->name }}" 
                                        data-product-color="{{ $product->color }}"
                                        data-change-type="inward"
                                        title="Add Stock">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#stockModal" 
                                        data-product-id="{{ $product->id }}" 
                                        data-product-name="{{ $product->name }}" 
                                        data-product-color="{{ $product->color }}"
                                        data-change-type="outward"
                                        title="Remove Stock">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <a href="{{ route('stock.product', $product) }}" class="btn btn-sm btn-info" title="View History">
                                    <i class="fas fa-history"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No products found.</td>
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
                <h5 class="modal-title" id="stockModalLabel">Update Stock for <span id="modalProductName"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('stock.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="modalProductId">
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
        var changeType = button.data('change-type');
        
        var modal = $(this);
        modal.find('.modal-title #modalProductName').text(productName);
        modal.find('.modal-body #modalProductId').val(productId);
        modal.find('.modal-body #modalChangeType').val(changeType);

        if(changeType === 'inward') {
            modal.find('.modal-header').css('background-color', '#28a745').css('color', 'white');
        } else {
            modal.find('.modal-header').css('background-color', '#dc3545').css('color', 'white');
        }
    });
</script>
@endpush
