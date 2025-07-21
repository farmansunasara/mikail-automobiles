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
                    <li class="list-group-item"><a href="{{ route('reports.low-stock') }}">Low Stock Report</a></li>
                    <li class="list-group-item"><a href="{{ route('reports.stock-report') }}">Current Stock Report</a></li>
                    <li class="list-group-item"><a href="{{ route('reports.product-movement') }}">Product Movement History</a></li>
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
                    <li class="list-group-item"><a href="{{ route('reports.sales') }}">Sales Report</a></li>
                    <li class="list-group-item"><a href="{{ route('reports.gst') }}">GST Report</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
