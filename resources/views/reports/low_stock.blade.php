@extends('layouts.admin')

@section('title', 'Low Stock Report')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Low Stock</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Low Stock Products (Threshold: {{ $threshold }})</h3>
        <div class="card-tools">
            <form action="{{ route('reports.low-stock') }}" method="GET" class="form-inline">
                <label for="threshold" class="mr-2">Threshold:</label>
                <input type="number" name="threshold" id="threshold" class="form-control mr-2" value="{{ $threshold }}">
                <button type="submit" class="btn btn-primary">Set</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Current Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockProducts as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                        <td>{{ $product->category->name }}</td>
                        <td><span class="badge badge-danger">{{ $product->quantity }}</span></td>
                        <td>
                            <a href="{{ route('stock.index') }}" class="btn btn-sm btn-success">Update Stock</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No products are below the stock threshold.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $lowStockProducts->links() }}
        </div>
    </div>
</div>
@endsection
