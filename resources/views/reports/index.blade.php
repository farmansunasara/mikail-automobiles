@extends('layouts.admin')

@section('title', 'Reports')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h5 class="card-title">Stock Reports</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('reports.low-stock') }}">Low Stock Report</a>
                        <a href="{{ route('reports.export.low-stock') }}" class="btn btn-sm btn-success" title="Export CSV" data-toggle="tooltip">
                            <i class="fas fa-download"></i>
                        </a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('reports.stock-report') }}">Current Stock Report</a>
                        <a href="{{ route('reports.export.stock-report') }}" class="btn btn-sm btn-success" title="Export CSV" data-toggle="tooltip">
                            <i class="fas fa-download"></i>
                        </a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('reports.product-movement') }}">Product Movement History</a>
                        <a href="{{ route('reports.export.product-movement') }}" class="btn btn-sm btn-success" title="Export CSV" data-toggle="tooltip">
                            <i class="fas fa-download"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h5 class="card-title">Sales & GST Reports</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('reports.sales') }}">Sales Report</a>
                        <a href="{{ route('reports.export.sales') }}" class="btn btn-sm btn-success" title="Export CSV" data-toggle="tooltip">
                            <i class="fas fa-download"></i>
                        </a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('reports.gst') }}">GST Report</a>
                        <a href="{{ route('reports.export.gst') }}" class="btn btn-sm btn-success" title="Export CSV" data-toggle="tooltip">
                            <i class="fas fa-download"></i>
                        </a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('reports.non-gst') }}">Non-GST Report</a>
                        <a href="{{ route('reports.export.non-gst') }}" class="btn btn-sm btn-success" title="Export CSV" data-toggle="tooltip">
                            <i class="fas fa-download"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush
