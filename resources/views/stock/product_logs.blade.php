@extends('layouts.admin')

@section('title', "Stock Logs for {$product->name}")

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('stock.index') }}">Stock Management</a></li>
<li class="breadcrumb-item active">Logs for {{ $product->name }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Stock Logs for: <strong>{{ $product->name }}</strong></h3>
        <div class="card-tools">
            <a href="{{ route('stock.logs') }}" class="btn btn-sm btn-primary">View All Logs</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
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
                            <span class="badge badge-{{ $log->change_type == 'inward' ? 'success' : 'danger' }}">
                                {{ ucfirst($log->change_type) }}
                            </span>
                        </td>
                        <td>{{ $log->quantity }}</td>
                        <td>{{ $log->notes ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No stock logs found for this product.</td>
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
