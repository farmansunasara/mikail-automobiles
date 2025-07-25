@extends('layouts.admin')

@section('title', 'Product Movement Report')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Product Movement</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Product Movement History</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.product-movement') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <select name="product_id" class="form-control">
                        <option value="">All Products</option>
                        @foreach(App\Models\Product::all() as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Color</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movementHistory as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M, Y H:i A') }}</td>
                        <td><a href="{{ route('products.show', $log->product) }}">{{ $log->product->name }}</a></td>
                        <td>
                            @if($log->colorVariant)
                                <span class="badge" style="background-color: {{ \App\Helpers\ColorHelper::getColorCode($log->colorVariant->color) }}; color: {{ \App\Helpers\ColorHelper::getTextColor($log->colorVariant->color) }};">
                                    {{ $log->colorVariant->color }}
                                </span>
                            @elseif($log->product->color)
                                <span class="badge" style="background-color: {{ \App\Helpers\ColorHelper::getColorCode($log->product->color) }}; color: {{ \App\Helpers\ColorHelper::getTextColor($log->product->color) }};">
                                    {{ $log->product->color }}
                                </span>
                            @else
                                <span class="badge badge-secondary">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $log->change_type == 'inward' ? 'success' : 'danger' }}">
                                {{ ucfirst($log->change_type) }}
                            </span>
                        </td>
                        <td>{{ $log->quantity }}</td>
                        <td>{{ $log->remarks ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No product movement found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $movementHistory->links() }}
        </div>
    </div>
</div>
@endsection
