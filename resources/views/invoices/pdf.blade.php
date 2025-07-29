<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm 8mm;
        }
        
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 10pt; /* Increased from 9pt */
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }
        
        .container { 
            width: 100%; 
            margin: 0;
            padding: 0;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 8px;
        }
        
        .header h1 { 
            margin: 0 0 2px 0; 
            font-size: 17pt; /* Increased from 16pt */
            font-weight: bold;
        }
        
        .header p { 
            margin: 1px 0; 
            font-size: 10pt; /* Increased from 9pt */
        }
        
        .invoice-details { 
            margin-bottom: 8px; 
        }
        
        .invoice-details table { 
            width: 100%; 
            border-collapse: collapse;
        }
        
        .invoice-details td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 9pt; /* Increased from 8pt */
            line-height: 1.2;
        }
        
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 5px;
            font-size: 9pt; /* Increased from 8pt */
        }
        
        .items-table th, .items-table td { 
            border: 0.5pt solid #333; 
            padding: 2px 3px;
            vertical-align: top;
            line-height: 1.1;
        }
        
        .items-table th { 
            background-color: #f0f0f0; 
            text-align: center;
            font-weight: bold;
            font-size: 9pt; /* Increased from 8pt */
            padding: 3px 2px;
        }
        
        .items-table td {
            font-size: 9pt; /* Increased from 8pt */
        }
        
        .totals-table { 
            width: 35%; 
            float: right; 
            margin-top: 8px;
            border-collapse: collapse;
            font-size: 9pt; /* Increased from 8pt */
        }
        
        .totals-table td { 
            padding: 1px 4px;
            border: 0.5pt solid #333;
            line-height: 1.2;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .clearfix::after { content: ""; clear: both; display: table; }
        
        .color-qty {
            font-size: 8pt; /* Increased from 7pt */
            line-height: 1.1;
        }
        
        .footer { 
            margin-top: 15px;
            text-align: center;
            font-size: 9pt; /* Increased from 8pt */
        }
        
        /* Column widths for optimal space usage */
        .col-sr { width: 3%; }
        .col-category { width: 12%; }
        .col-product { width: 14%; }
        .col-hsn { width: 8%; }
        .col-price { width: 8%; }
        .col-gst { width: 6%; }
        .col-colors { width: 31%; }
        .col-qty { width: 8%; }
        .col-total { width: 10%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cash/Credit</h1>
            <p><strong>{{ config('app.name') }}</strong></p>
        </div>

        <div class="invoice-details">
            <table>
                <tr>
                    <td style="width: 60%;">
                        <strong>Bill To:</strong> {{ $invoice->customer->name }} | {{ $invoice->customer->address }} | {{ $invoice->customer->state }} |<br> GSTIN: {{ $invoice->customer->gstin ?? 'N/A' }}
                    </td>
                    <td style="width: 40%;" class="text-right">
                        <strong>Bill no #:</strong> {{ $invoice->invoice_number }} | <strong>Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}
                        @if($invoice->due_date)
                            | <strong>Due:</strong> {{ $invoice->due_date->format('d/m/Y') }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-sr">#</th>
                    <th class="col-category">Category</th>
                    <th class="col-product">Product</th>
                    <th class="col-colors">Colors & Quantities</th>
                    <th class="col-price">Price</th>
                    <th class="col-qty">Total Qty</th>
                    <th class="col-total">Total</th>
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
                    $totalQuantity = $items->sum('quantity');
                    
                    // Create condensed color & quantity string
                    $colorQuantities = [];
                    foreach($items as $item) {
                        $color = $item->colorVariant ? $item->colorVariant->color : ($item->product->color ?? 'Default');
                        $colorQuantities[] = $color . ': ' . $item->quantity;
                    }
                    $colorQtyString = implode(', ', $colorQuantities);
                @endphp
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td>{{ $firstItem->product->category->name ?? 'N/A' }}</td>
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
                    <td class="text-right"><strong>Rs.{{ number_format($invoice->total_amount, 2) }}</strong></td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td>Discount ({{ $invoice->discount_display }})</td>
                    <td class="text-right" style="color: #dc3545;">-Rs.{{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>After Discount</td>
                    <td class="text-right">Rs.{{ number_format($invoice->subtotal_after_discount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td>CGST ({{ $invoice->gst_rate/2 }}%)</td>
                    <td class="text-right">Rs.{{ number_format($invoice->cgst, 2) }}</td>
                </tr>
                <tr>
                    <td>SGST ({{ $invoice->gst_rate/2 }}%)</td>
                    <td class="text-right">Rs.{{ number_format($invoice->sgst, 2) }}</td>
                </tr>
                <tr style="background-color: #f0f0f0;">
                    <td><strong>Grand Total</strong></td>
                    <td class="text-right"><strong>Rs.{{ number_format($invoice->grand_total, 2) }}</strong></td>
                </tr>
            </table>
        </div>

       
    </div>
</body>
</html>