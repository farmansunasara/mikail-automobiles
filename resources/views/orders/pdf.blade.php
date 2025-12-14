<!DOCTYPE html>
<html>
<head>
    <title>Order #{{ $order->order_number }}</title>
    <style>
        @page { size: A4; margin: 10mm 8mm; }
        body { font-family: 'Arial', sans-serif; font-size: 10pt; line-height: 1.1; margin: 0; padding: 0; }
        .container { width: 100%; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 8px; }
        .header h1 { margin: 0 0 2px 0; font-size: 17pt; font-weight: bold; }
        .header p { margin: 1px 0; font-size: 10pt; }
        .details { margin-bottom: 8px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 2px 4px; vertical-align: top; font-size: 9pt; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 9pt; }
        .items-table th, .items-table td { border: 0.5pt solid #333; padding: 2px 3px; vertical-align: top; line-height: 1.1; }
        .items-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; padding: 3px 2px; }
        .totals-table { width: 35%; float: right; margin-top: 8px; border-collapse: collapse; font-size: 9pt; }
        .totals-table td { padding: 1px 4px; border: 0.5pt solid #333; line-height: 1.2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .color-qty { font-size: 8pt; line-height: 1.1; }
        .footer { margin-top: 15px; text-align: center; font-size: 9pt; }
        .col-sr { width: 3%; }
        .col-product { width: 25%; }
        .col-color { width: 30%; }
        .col-price { width: 10%; }
        .col-qty { width: 10%; }
        .col-total { width: 12%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order</h1>
            <p><strong>{{ config('app.name') }}</strong></p>
        </div>

        <div class="details">
            <table>
                <tr>
                    <td style="width:60%;">
                        <strong>Ship To:</strong> {{ $order->customer->name }} {{ $order->customer->address ? ' | '.$order->customer->address : '' }}
                    </td>
                    <td style="width:40%;" class="text-right">
                        <strong>Order #:</strong> {{ $order->order_number }} | <strong>Date:</strong> {{ $order->order_date->format('d/m/Y') }}
                        @if($order->delivery_date)
                            | <strong>Delivery:</strong> {{ $order->delivery_date->format('d/m/Y') }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-sr">#</th>
                    <th class="col-product">Product</th>
                    <th class="col-color">Colors & Quantities</th>
                    <th class="col-price">Price</th>
                    <th class="col-qty">Total Qty</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Sort by id first to maintain insertion order, then group by product_id
                    $groupedItems = collect($order->items)->sortBy('id')->groupBy('product_id');
                    $rowNumber = 1;
                @endphp

                @foreach($groupedItems as $productId => $items)
                    @php
                        $firstItem = $items->first();
                        $totalSubtotal = $items->sum('subtotal');
                        $totalQuantity = $items->sum('quantity');
                        $productName = $firstItem->product->name;

                        $colorQuantities = [];
                        foreach ($items as $it) {
                            $color = $it->colorVariant ? $it->colorVariant->color : ($it->product->color ?? 'Default');
                            $colorQuantities[] = $color . ': ' . $it->quantity;
                        }
                        $colorQtyString = implode(', ', $colorQuantities);
                    @endphp

                    <tr>
                        <td class="text-center">{{ $rowNumber++ }}</td>
                        <td><strong>{{ $productName }}</strong></td>
                        <td class="color-qty">{{ $colorQtyString }}</td>
                        <td class="text-right">Rs.{{ number_format($firstItem->price, 2) }}</td>
                        <td class="text-center"><strong>{{ $totalQuantity }}</strong></td>
                        <td class="text-right"><strong>Rs.{{ number_format($totalSubtotal, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="clearfix">
            <table class="totals-table">
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td class="text-right"><strong>Rs.{{ number_format($order->total_amount, 2) }}</strong></td>
                </tr>
                <tr style="background-color: #f0f0f0;">
                    <td><strong>Grand Total</strong></td>
                    <td class="text-right"><strong>Rs.{{ number_format($order->total_amount, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        @if($order->notes)
        <div style="margin-top:15px; clear: both;">
            <strong>Notes:</strong>
            <div>{{ $order->notes }}</div>
        </div>
        @endif

    </div>
</body>
</html>