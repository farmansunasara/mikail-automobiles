@extends('layouts.admin')

@section('title', 'Current Stock Report')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Current Stock</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Current Stock Report</h3>
        <div class="card-tools">
            <strong>Total Stock Value: ₹{{ number_format($totalValue, 2) }}</strong>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.stock-report') }}" method="GET" class="form-inline mb-3">
            <div class="input-group">
                <select name="category_id" class="form-control">
                    <option value="">All Categories</option>
                    @foreach(App\Models\Category::all() as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockReport as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>₹{{ number_format($product->price, 2) }}</td>
                        <td>₹{{ number_format($product->quantity * $product->price, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $stockReport->links() }}
        </div>
    </div>
</div>
@endsection
