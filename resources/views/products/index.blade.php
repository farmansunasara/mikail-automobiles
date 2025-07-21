@extends('layouts.admin')

@section('title', 'Products')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Products</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Products</h3>
        <div class="card-tools">
            <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('products.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="subcategory_id" id="subcategory_id" class="form-control">
                        <option value="">All Subcategories</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="color" class="form-control">
                        <option value="">All Colors</option>
                        @foreach($colors as $color)
                            <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>{{ ucfirst($color) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary btn-block">Clear</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Color</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->subcategory->name }}</td>
                        <td>
                            @if($product->color)
                                <span class="badge badge-secondary">{{ ucfirst($product->color) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $product->quantity }}</td>
                        <td>₹{{ number_format($product->price, 2) }}</td>
                        <td>
                            @if($product->is_composite)
                                <span class="badge badge-success">Composite</span>
                            @else
                                <span class="badge badge-info">Simple</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No products found.</td>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var categoryId = $('#category_id').val();
        if(categoryId) {
            fetchSubcategories(categoryId, "{{ request('subcategory_id') }}");
        }

        $('#category_id').change(function() {
            var categoryId = $(this).val();
            if(categoryId) {
                fetchSubcategories(categoryId);
            } else {
                $('#subcategory_id').html('<option value="">All Subcategories</option>');
            }
        });

        function fetchSubcategories(categoryId, selectedSubcategoryId = null) {
            $.ajax({
                url: `/api/subcategories/${categoryId}`,
                type: 'GET',
                success: function(data) {
                    var subcategorySelect = $('#subcategory_id');
                    subcategorySelect.html('<option value="">All Subcategories</option>');
                    $.each(data, function(key, value) {
                        var selected = selectedSubcategoryId == value.id ? 'selected' : '';
                        subcategorySelect.append(`<option value="${value.id}" ${selected}>${value.name}</option>`);
                    });
                }
            });
        }
    });
</script>
@endpush
