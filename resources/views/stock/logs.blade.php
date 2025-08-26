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
        <h3 class="card-title mb-0">All Stock Logs</h3>
        <div class="card-tools d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex d-md-none mr-2" id="toggle-filters">
                <i class="fas fa-filter mr-1"></i> Filters
            </button>
        </div>
    </div>
    <div class="card-body pt-3">
        <div class="d-flex flex-wrap mb-2 small text-muted">
            <div class="mr-3"><strong>Total:</strong> {{ $logs->total() }}</div>
            <div class="mr-3"><strong>Showing:</strong> {{ $logs->firstItem() }}-{{ $logs->lastItem() }}</div>
            @if(request()->anyFilled(['product_id','color_variant_id','change_type','start_date','end_date','remarks']))
                <div class="text-success"><i class="fas fa-check-circle"></i> Filters active</div>
            @endif
        </div>
        <form action="{{ route('stock.logs') }}" method="GET" class="mb-3" id="stock-log-filter-form" novalidate>
            <div class="row g-2" id="filters-wrapper">
                <div class="col-md-2 mb-2">
                    <select name="product_id" id="filter_product_id" class="form-control">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="color_variant_id" id="filter_color_variant_id" class="form-control" {{ request('product_id') ? '' : 'disabled' }}>
                        <option value="">All Variants</option>
                        {{-- If returning from request with product selected, we let JS repopulate; optional server pre-population could be added --}}
                    </select>
                </div>
                <div class="col-md- mb-2">
                    <select name="change_type" class="form-control">
                        <option value="">Any Type</option>
                        <option value="inward" {{ request('change_type') == 'inward' ? 'selected' : '' }}>Inward</option>
                        <option value="outward" {{ request('change_type') == 'outward' ? 'selected' : '' }}>Outward</option>
                    </select>
                </div>
                <div class="col-md-1 mb-2">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="From">
                </div>
                <div class="col-md-1 mb-2">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="To">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" name="remarks" class="form-control" placeholder="Search remarks..." value="{{ request('remarks') }}">
                </div>
                <div class="col-md-1 mb-2">
                    <button type="submit" class="btn btn-primary mb-1">Filter</button>
                </div>
                <div class="col-md-1 mb-2">
                    <button type="button" class="btn btn-secondary mb-1" onclick="window.location='{{ route('stock.logs') }}'">Reset</button>
                </div>
        </form>

        <div class="table-responsive table-responsive-sticky">
            <table class="table table-bordered table-sm table-hover mb-0 stock-log-table">
                <thead>
                    <tr>
                        <th style="min-width:140px">Date</th>
                        <th style="min-width:140px">Product</th>
                        <th style="min-width:120px">Variant</th>
                        <th style="min-width:90px">Type</th>
                        <th style="min-width:120px">Qty</th>
                        <th style="min-width:200px">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="log-row">
                        <td data-label="Date">{{ $log->created_at->format('d M, Y H:i') }}</td>
                        <td data-label="Product">
                            <a href="{{ route('products.show', $log->product) }}" class="text-break">{{ $log->product->name }}</a>
                        </td>
                        <td data-label="Variant">
                            @if($log->colorVariant)
                                <span class="badge badge-{{ $log->colorVariant->stock_status ?? 'secondary' }}">{{ $log->variant_color }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td data-label="Type">
                            <span class="badge badge-{{ $log->change_type == 'inward' ? 'success' : 'danger' }}">
                                {{ ucfirst($log->change_type) }}
                            </span>
                        </td>
                        <td data-label="Qty" class="text-nowrap">
                            <strong>{{ $log->quantity }}</strong>
                            <small class="text-muted d-block">{{ $log->previous_quantity }} → {{ $log->new_quantity }}</small>
                        </td>
                        <td data-label="Remarks" class="remarks-cell">
                            @php $r = $log->remarks; @endphp
                            @if($r)
                                <span class="remarks-text" title="{{ $r }}">{{ Str::limit($r, 80) }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No stock logs found.</td>
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

@push('scripts')
<script>
(function() {
    // Collapsible filters on small screens
    const filtersWrapper = document.getElementById('filters-wrapper');
    const toggleBtn = document.getElementById('toggle-filters');
    if(toggleBtn && filtersWrapper) {
        const MOBILE_BREAKPOINT = 768;
        function setInitialState() {
            if(window.innerWidth < MOBILE_BREAKPOINT) {
                filtersWrapper.style.display = 'none';
            } else {
                filtersWrapper.style.display = '';
            }
        }
        setInitialState();
        window.addEventListener('resize', setInitialState);
        toggleBtn.addEventListener('click', () => {
            if(filtersWrapper.style.display === 'none') {
                filtersWrapper.style.display = '';
            } else {
                filtersWrapper.style.display = 'none';
            }
        });
    }

    const productSelect = document.getElementById('filter_product_id');
    const variantSelect = document.getElementById('filter_color_variant_id');
    const selectedVariant = "{{ request('color_variant_id') }}";

    function loadVariants(productId, preselect = null) {
        if(!productId) {
            variantSelect.innerHTML = '<option value="">All Variants</option>';
            variantSelect.disabled = true;
            return;
        }
        variantSelect.disabled = true;
        variantSelect.innerHTML = '<option value="">Loading...</option>';
        fetch(`/api/products/${productId}/color-variants`)
            .then(r => r.json())
            .then(data => {
                let opts = '<option value="">All Variants</option>';
                data.forEach(v => {
                    opts += `<option value="${v.id}">${v.color || 'Default'} (Stock: ${v.quantity})</option>`;
                });
                variantSelect.innerHTML = opts;
                variantSelect.disabled = false;
                if(preselect) {
                    variantSelect.value = preselect;
                }
            })
            .catch(() => {
                variantSelect.innerHTML = '<option value="">Error</option>';
            });
    }

    productSelect && productSelect.addEventListener('change', e => {
        loadVariants(e.target.value);
    });

    // On page load if product selected, load variants
    if(productSelect && productSelect.value) {
        loadVariants(productSelect.value, selectedVariant);
    }
})();
</script>
<style>
/* Sticky header */
.table-responsive-sticky thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 2; }

/* Mobile stacked view */
@media (max-width: 767.98px) {
    .stock-log-table thead { display: none; }
    .stock-log-table tbody tr { display: block; margin-bottom: .85rem; border: 1px solid #dee2e6; border-radius: .25rem; padding: .5rem .75rem; }
    .stock-log-table tbody tr td { display: flex; justify-content: space-between; padding: .35rem .25rem; border: none !important; }
    .stock-log-table tbody tr td:not(:last-child) { border-bottom: 1px dashed #e2e3e5 !important; }
    .stock-log-table tbody tr td:before { content: attr(data-label); font-weight: 600; margin-right: .75rem; color: #555; }
    .remarks-cell { white-space: normal !important; }
}

/* Truncate remarks on desktop */
.remarks-text { display: inline-block; max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
@media (max-width: 1200px) { .remarks-text { max-width: 260px; } }
@media (max-width: 992px)  { .remarks-text { max-width: 200px; } }

/* Narrow spacing tweaks */
.stock-log-table td, .stock-log-table th { vertical-align: middle; }
</style>
@endpush
