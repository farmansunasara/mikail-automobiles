<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; }
        .invoice-details { margin-bottom: 20px; }
        .invoice-details table { width: 100%; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; }
        .items-table th { background-color: #f2f2f2; text-align: left; }
        .totals-table { width: 40%; float: right; margin-top: 20px; }
        .totals-table td { padding: 5px; }
        .text-right { text-align: right; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>INVOICE</h1>
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>Automobile Parts & Services</p>
        </div>

        <div class="invoice-details">
            <table>
                <tr>
                    <td style="width: 50%;">
                        <strong>Billed To:</strong><br>
                        {{ $invoice->customer->name }}<br>
                        {{ $invoice->customer->address }}<br>
                        {{ $invoice->customer->state }}<br>
                        GSTIN: {{ $invoice->customer->gstin ?? 'N/A' }}
                    </td>
                    <td style="width: 50%;" class="text-right">
                        <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}<br>
                    </td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>HSN</th>
                    <th>Price</th>
                    <th>GST%</th>
                    <th>Colors & Quantities</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $groupedItems = collect($invoice->items)->groupBy('product.name');
                    $rowNumber = 1;
                @endphp
                
                @foreach($groupedItems as $productName => $items)
                @php
                    $firstItem = $items->first();
                    $totalSubtotal = $items->sum('subtotal');
                @endphp
                <tr>
                    <td>{{ $rowNumber++ }}</td>
                    <td><strong>{{ $productName }}</strong></td>
                    <td>{{ $firstItem->product->hsn_code }}</td>
                    <td class="text-right">Rs.{{ number_format($firstItem->price, 2) }}</td>
                    <td class="text-right">{{ $firstItem->gst_rate }}%</td>
                    <td>
                        @foreach($items as $item)
                        <div style="margin-bottom: 3px;">
                            <strong>{{ $item->product->color ?? 'No Color' }}:</strong> {{ $item->quantity }} 
                            <span style="font-size: 10px; color: #666;">(Rs.{{ number_format($item->subtotal, 2) }})</span>
                        </div>
                        @endforeach
                    </td>
                    <td class="text-right"><strong>Rs.{{ number_format($totalSubtotal, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="clearfix">
            <table class="totals-table">
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rs.{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>CGST</td>
                    <td class="text-right">Rs.{{ number_format($invoice->cgst, 2) }}</td>
                </tr>
                <tr>
                    <td>SGST</td>
                    <td class="text-right">Rs.{{ number_format($invoice->sgst, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Grand Total</strong></td>
                    <td class="text-right"><strong>Rs.{{ number_format($invoice->grand_total, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <div class="footer" style="margin-top: 50px;">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
