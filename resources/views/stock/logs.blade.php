@extends('layouts.admin')

@section('title', 'Stock Logs')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('stock.index') }}">Stock Management</a></li>
<li class="breadcrumb-item active">Logs</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Stock Logs</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('stock.logs') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <select name="product_id" class="form-control">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="change_type" class="form-control">
                        <option value="">Any Type</option>
                        <option value="inward" {{ request('change_type') == 'inward' ? 'selected' : '' }}>Inward</option>
                        <option value="outward" {{ request('change_type') == 'outward' ? 'selected' : '' }}>Outward</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-1">
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
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M, Y H:i A') }}</td>
                        <td>
                            <a href="{{ route('products.show', $log->product) }}">{{ $log->product->name }}</a>
                        </td>
                        <td>
                            <span class="badge badge-{{ $log->change_type == 'inward' ? 'success' : 'danger' }}">
                                {{ ucfirst($log->change_type) }}
                            </span>
                        </td>
                        <td>{{ $log->quantity }}</td>
                        <td>{{ $log->notes ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No stock logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
