@extends('layouts.admin')

@section('title', 'Invoice Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoices.gst.index') }}">GST Invoices</a></li>
<li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="invoice p-3 mb-3">
    <!-- title row -->
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-globe"></i> {{ config('app.name') }}
                <small class="float-right">Date: {{ $invoice->invoice_date->format('d/m/Y') }}</small>
            </h4>
        </div>
    </div>
    <!-- info row -->
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            From
            <address>
                <strong>{{ config('app.name') }}</strong><br>
                Industrial Area<br>
                Automobile Parts & Services<br>
                Phone: +91-XXXXXXXXXX<br>
                Email: info@almikailautomobile.com
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            To
            <address>
                <strong>{{ $invoice->customer->name }}</strong><br>
                {{ $invoice->customer->address }}<br>
                {{ $invoice->customer->state }}<br>
                Phone: {{ $invoice->customer->mobile }}<br>
                Email: {{ $invoice->customer->email ?? 'N/A' }}
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Invoice #{{ $invoice->invoice_number }}</b><br>
            <br>
            <b>GSTIN:</b> {{ $invoice->customer->gstin ?? 'N/A' }}<br>
        </div>
    </div>
    <!-- /.row -->

    <!-- Table row -->
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Product</th>
                        <!-- <th>GST%</th> -->
                        <th>Colors & Quantities</th>
                        <th>Price</th>

                        <th>Total Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedItems = $invoice->items->groupBy('product.name');
                        $rowNumber = 1;
                    @endphp
                    
                    @foreach($groupedItems as $productName => $items)
                    @php
                        $firstItem = $items->first();
                        $totalSubtotal = $items->sum('subtotal');
                        $totalQuantity = $items->sum('quantity');
                    @endphp
                    <tr>
                        <td>{{ $rowNumber++ }}</td>
                        <td>
                            <span class="badge badge-info">{{ $firstItem->product->category->name ?? 'N/A' }}</span>
                            @if($firstItem->product->subcategory)
                                <br><small class="text-muted">{{ $firstItem->product->subcategory->name }}</small>
                            @endif
                        </td>
                        <td><strong>{{ $productName }}</strong></td>
                        <!-- <td>{{ $firstItem->gst_rate }}%</td> -->
                        <td>
                            <div class="colors-display">
                                @foreach($items as $item)
                                <div class="color-item mb-1">
                                    <div class="row align-items-center">
                                        <div class="col-4">
                                            @if($item->colorVariant)
                                                <span class="badge" style="background-color: {{ \App\Helpers\ColorHelper::getColorCode($item->colorVariant->color) }}; color: {{ \App\Helpers\ColorHelper::getTextColor($item->colorVariant->color) }};">
                                                    {{ $item->colorVariant->color }}
                                                </span>
                                            @elseif($item->product->color)
                                                @php
                                                    $colorClass = match(strtolower($item->product->color)) {
                                                        'black' => 'badge-dark',
                                                        'red' => 'badge-danger',
                                                        'blue' => 'badge-primary',
                                                        'white' => 'badge-light',
                                                        'green' => 'badge-success',
                                                        'yellow' => 'badge-warning',
                                                        'silver' => 'badge-secondary',
                                                        'golden' => 'badge-warning',
                                                        'clear' => 'badge-info',
                                                        default => 'badge-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $colorClass }}">{{ $item->product->color }}</span>
                                            @else
                                                <span class="badge badge-secondary">No Color</span>
                                            @endif
                                        </div>
                                        <div class="col-4">
                                            <strong>{{ $item->quantity }}</strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">₹{{ number_format($item->subtotal, 2) }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td>₹{{ number_format($firstItem->price, 2) }}</td>

                        <td><strong>{{ $totalQuantity }}</strong></td>
                        <td><strong>₹{{ number_format($totalSubtotal, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.row -->

    <div class="row">
        <!-- accepted payments column -->
        <div class="col-6">
            <p class="lead">Payment Methods:</p>
            <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                Bank Transfer, UPI, Cash
            </p>
            @if($invoice->notes)
            <p class="lead">Notes:</p>
            <p class="text-muted">{{ $invoice->notes }}</p>
            @endif
        </div>
        <!-- /.col -->
        <div class="col-6">
            <p class="lead">Amount Due {{ $invoice->invoice_date->format('d/m/Y') }}</p>

            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>Total Quantity:</th>
                        <td><strong>{{ $invoice->items->sum('quantity') }} pcs</strong></td>
                    </tr>
                    <tr>
                        <th>Subtotal:</th>
                        <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <th>Discount ({{ $invoice->discount_display }}):</th>
                        <td class="text-danger">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>After Discount:</th>
                        <td>₹{{ number_format($invoice->subtotal_after_discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>CGST ({{ $invoice->gst_rate / 2 }}%)</th>
                        <td>₹{{ number_format($invoice->cgst, 2) }}</td>
                    </tr>
                    <tr>
                        <th>SGST ({{ $invoice->gst_rate / 2 }}%)</th>
                        <td>₹{{ number_format($invoice->sgst, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total:</th>
                        <td><strong>₹{{ number_format($invoice->grand_total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

    <!-- this row will not appear when printing -->
    <div class="row no-print">
        <div class="col-12">
            <a href="{{ route('invoices.gst.preview', $invoice) }}" rel="noopener" target="_blank" class="btn btn-default"><i class="fas fa-print"></i> Print</a>
            <a href="{{ route('invoices.gst.download', $invoice) }}" class="btn btn-primary float-right" style="margin-right: 5px;">
                <i class="fas fa-download"></i> Generate PDF
            </a>
        </div>
    </div>
</div>
@endsection
