@extends('layouts.admin')

@section('title', 'Product Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
<li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $product->name }}</h3>
                <div class="card-tools">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl>
                            <dt>Category</dt>
                            <dd>{{ $product->category->name }}</dd>
                            <dt>Subcategory</dt>
                            <dd>{{ $product->subcategory->name }}</dd>
                            <dt>Color Variants</dt>
                            <dd>
                                @if($product->colorVariants->count() > 0)
                                    <div class="color-variants-display">
                                        @foreach($product->colorVariants as $variant)
                                            @php
                                                $colorClass = match(strtolower($variant->color)) {
                                                    'black' => 'badge-dark',
                                                    'white' => 'badge-light text-dark',
                                                    'red' => 'badge-danger',
                                                    'blue' => 'badge-primary',
                                                    'green' => 'badge-success',
                                                    'yellow' => 'badge-warning text-dark',
                                                    'orange' => 'badge-warning',
                                                    'purple' => 'badge-info',
                                                    'pink' => 'badge-info',
                                                    'brown' => 'badge-secondary',
                                                    'gray', 'grey' => 'badge-secondary',
                                                    'silver' => 'badge-light text-dark',
                                                    'gold', 'golden' => 'badge-warning text-dark',
                                                    default => 'badge-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $colorClass }} mr-2 mb-1">
                                                {{ ucfirst($variant->color) }}: {{ $variant->quantity }} pcs
                                                <span style="font-size:0.9em; color:inherit;">(Min: {{ $variant->minimum_threshold }})</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">No color variants</span>
                                @endif
                            </dd>
                            <dt>Total Quantity in Stock</dt>
                            <dd><strong>{{ $product->colorVariants->sum('quantity') }} pcs</strong></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl>
                            <dt>Price</dt>
                            <dd>₹{{ number_format($product->price, 2) }}</dd>
                            <dt>Product Type</dt>
                            <dd>
                                @if($product->is_composite)
                                    <span class="badge badge-success">Composite</span>
                                @else
                                    <span class="badge badge-info">Simple</span>
                                @endif
                            </dd>
                            
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        @if($product->is_composite)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Components</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($product->components as $component)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('products.show', $component->componentProduct) }}">{{ $component->componentProduct->name }}</a>
                            <span class="badge badge-primary badge-pill">{{ $component->quantity_needed }} pcs</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Stock History</h3>
                <div class="card-tools">
                    <a href="{{ route('stock.product', $product) }}" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($product->stockLogs()->latest()->take(5)->get() as $log)
                        <li class="list-group-item">
                            <span class="text-{{ $log->change_type == 'inward' ? 'success' : 'danger' }}">
                                {{ ucfirst($log->change_type) }}: {{ $log->quantity }}
                            </span>
                            <small class="float-right">{{ $log->created_at->diffForHumans() }}</small>
                            @if($log->notes)
                                <br><small class="text-muted">{{ $log->notes }}</small>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item text-center">No stock history found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
