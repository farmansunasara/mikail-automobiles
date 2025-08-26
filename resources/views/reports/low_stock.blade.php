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
                <a href="{{ route('reports.export.low-stock', request()->query()) }}" class="btn btn-success ml-2" title="Export to CSV" data-toggle="tooltip">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <x-sortable-header column="id" label="ID" />
                        <th>Product</th>
                        <x-sortable-header column="color" label="Color" />
                        <th>Category</th>
                        <x-sortable-header column="quantity" label="Current Quantity" />
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockVariants as $variant)
                    <tr>
                        <td>{{ $variant->id }}</td>
                        <td><a href="{{ route('products.show', $variant->product) }}">{{ $variant->product->name }}</a></td>
                        <td>
                            <span class="badge" style="background-color: {{ \App\Helpers\ColorHelper::getColorCode($variant->color) }}; color: {{ \App\Helpers\ColorHelper::getTextColor($variant->color) }};">
                                {{ $variant->color }}
                            </span>
                        </td>
                        <td>{{ $variant->product->category->name }}</td>
                        <td><span class="badge badge-danger">{{ $variant->quantity }}</span></td>
                        <td>
                            <a href="{{ route('stock.index') }}" class="btn btn-sm btn-success">Update Stock</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No color variants are below the stock threshold.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $lowStockVariants->links() }}
        </div>
    </div>
</div>
@endsection
