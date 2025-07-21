@extends('layouts.admin')

@section('title', 'Category Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categories</a></li>
<li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Category Information</h3>
                <div class="card-tools">
                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-warning">Edit</a>
                </div>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Name</dt>
                    <dd>{{ $category->name }}</dd>
                    <dt>Description</dt>
                    <dd>{{ $category->description ?? 'N/A' }}</dd>
                    <dt>Created At</dt>
                    <dd>{{ $category->created_at->format('d M, Y H:i A') }}</dd>
                    <dt>Updated At</dt>
                    <dd>{{ $category->updated_at->format('d M, Y H:i A') }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Subcategories</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($category->subcategories as $subcategory)
                    <li class="list-group-item">{{ $subcategory->name }}</li>
                    @empty
                    <li class="list-group-item text-center">No subcategories found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Products in this Category</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Subcategory</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($category->products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->subcategory->name ?? 'N/A' }}</td>
                                <td>{{ $product->quantity }}</td>
                                <td>₹{{ number_format($product->price, 2) }}</td>
                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No products found in this category.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
