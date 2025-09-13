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
    <h3 class="card-title">Low Stock Color Variants (Below Minimum Threshold)</h3>
        <div class="card-tools">
            <a href="{{ route('reports.export.low-stock') }}" class="btn btn-success ml-2" title="Export to CSV" data-toggle="tooltip">
                <i class="fas fa-download"></i> Export CSV
            </a>
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
                        <th>Min Threshold</th>
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
                        <td>{{ $variant->minimum_threshold }}</td>
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
