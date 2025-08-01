@extends('layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumbs')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalProducts }}</h3>
                <p>Total Products</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('products.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($totalStockValue, 2) }}</h3>
                <p>Total Stock Value</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{ route('reports.stock-report') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $invoicesThisMonth }}</h3>
                <p>Invoices This Month</p>
            </div>
            <div class="icon">
                <i class="ion ion-person-add"></i>
            </div>
            <a href="{{ route('invoices.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $lowStockItems }}</h3>
                <p>Low Stock Items</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('reports.low-stock') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Monthly Sales Chart</h3>
            </div>
            <div class="card-body" id="chart-container" style="position: relative; min-height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Low Stock Products</h3>
            </div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    @forelse($lowStockProducts as $variant)
                    <li class="item">
                        <div class="product-info">
                            <a href="{{ route('products.show', $variant->product_id) }}" class="product-title">
                                {{ $variant->product->name ?? 'Unknown Product' }}
                                @if($variant->color)
                                    <small>({{ $variant->color }})</small>
                                @endif
                                <span class="badge badge-warning float-right">{{ $variant->quantity }}</span>
                            </a>
                            <span class="product-description">
                                {{ $variant->product->category->name ?? 'No Category' }}
                                @if($variant->product->subcategory)
                                    / {{ $variant->product->subcategory->name }}
                                @endif
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="item"><div class="text-center">No low stock products.</div></li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('reports.low-stock') }}" class="uppercase">View All Low Stock Products</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Invoices</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentInvoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->customer->name }}</td>
                            <td>{{ $invoice->invoice_date->format('d M, Y') }}</td>
                            <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                            <td>
                                @if($invoice->invoice_type === 'gst')
                                    <a href="{{ route('invoices.gst.show', $invoice) }}" class="btn btn-sm btn-info">View</a>
                                @else
                                    <a href="{{ route('invoices.non_gst.show', $invoice) }}" class="btn btn-sm btn-info">View</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No recent invoices found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Show chart loader
    showChartLoader($('#chart-container'));
    
    // Simulate loading delay for better UX
    setTimeout(function() {
        const salesChartCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesChartCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($monthlySales, 'month')) !!},
                datasets: [{
                    label: 'Monthly Sales (₹)',
                    data: {!! json_encode(array_column($monthlySales, 'sales')) !!},
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    pointRadius: false,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return '₹' + value;
                            }
                        }
                    }
                },
                animation: {
                    onComplete: function() {
                        // Hide chart loader when animation completes
                        hideChartLoader($('#chart-container'));
                    }
                }
            }
        });
    }, 800); // Small delay to show the loader
});
</script>
@endpush