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
                    <a href="{{ route('reports.export.stock-report', request()->query()) }}" class="btn btn-success" title="Export to CSV" data-toggle="tooltip">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Color</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockReport as $variant)
                    <tr>
                        <td>{{ $variant->id }}</td>
                        <td><a href="{{ route('products.show', $variant->product) }}">{{ $variant->product->name }}</a></td>
                        <td>
                            <span class="badge" style="background-color: {{ \App\Helpers\ColorHelper::getColorCode($variant->color) }}; color: {{ \App\Helpers\ColorHelper::getTextColor($variant->color) }};">
                                {{ $variant->color }}
                            </span>
                        </td>
                        <td>{{ $variant->product->category->name }}</td>
                        <td>{{ $variant->quantity }}</td>
                        <td>₹{{ number_format($variant->product->price, 2) }}</td>
                        <td>₹{{ number_format($variant->quantity * $variant->product->price, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No color variants found.</td>
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
