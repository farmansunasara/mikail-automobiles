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
        <form action="{{ route('stock.index') }}" method="GET" class="form-inline mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Current Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                            <br>
                            <small class="text-muted">{{ $product->category->name }} > {{ $product->subcategory->name }}</small>
                        </td>
                        <td>{{ $product->quantity }}</td>
                        <td>
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#stockModal" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-change-type="inward">
                                <i class="fas fa-plus"></i> Inward
                            </button>
                            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#stockModal" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-change-type="outward">
                                <i class="fas fa-minus"></i> Outward
                            </button>
                            <a href="{{ route('stock.product', $product) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-history"></i> Logs
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
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
