<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    // GST Invoice Methods

    public function indexGst(Request $request)
    {
        $query = Invoice::with('customer')->where('invoice_type', 'gst');

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%')
                ->orWhereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('overdue') && $request->overdue == '1') {
            $query->where('due_date', '<', now())
                  ->where('status', '!=', 'paid')
                  ->where('status', '!=', 'cancelled');
        }

        $invoices = $query->latest()->paginate(10);
        $customers = Customer::orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'customers'))->with('invoice_type', 'gst');
    }

    public function createGst()
    {
        $customers = Customer::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $productNames = Product::select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
        $invoice_number = Invoice::generateInvoiceNumber();
        return view('invoices.create_optimized', compact('customers', 'productNames', 'invoice_number', 'categories'))->with('invoice_type', 'gst');
    }

    public function storeGst(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'gst_rate' => 'required|numeric|min:0|max:100',
            'discount_type' => 'nullable|numeric|in:0,1',
            'discount_value' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.variants' => 'required|array',
        ]);

        try {
            $invoiceItems = [];
            foreach ($request->items as $itemData) {
                $price = floatval($itemData['price']);
                if (isset($itemData['variants'])) {
                    foreach ($itemData['variants'] as $variantData) {
                        $quantity = intval($variantData['quantity'] ?? 0);
                        $product_id = $variantData['product_id'] ?? null;
                        if ($quantity > 0 && $product_id) {
                            $colorVariant = ProductColorVariant::find($product_id);
                            if (!$colorVariant) {
                                throw new \Exception("Product color variant not found (ID: {$product_id})");
                            }
                            if ($colorVariant->quantity < $quantity) {
                                throw new \Exception("Insufficient stock for {$colorVariant->product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$quantity}");
                            }
                            $invoiceItems[] = [
                                'color_variant_id' => $product_id,
                                'product_id' => $colorVariant->product_id,
                                'quantity' => $quantity,
                                'price' => $price,
                            ];
                        }
                    }
                }
            }

            if (empty($invoiceItems)) {
                throw new \Exception("Please add at least one item with quantity greater than 0");
            }

            DB::transaction(function () use ($request, $invoiceItems) {
                $total_amount = 0;

                foreach ($invoiceItems as $item) {
                    $subtotal = $item['quantity'] * $item['price'];
                    $total_amount += $subtotal;
                }

                // Calculate discount
                $discount_type = $request->discount_type ?? 0;
                $discount_value = $request->discount_value ?? 0;
                $discount_amount = 0;

                if ($discount_value > 0) {
                    if ($discount_type == 1) {
                        // Percentage discount
                        $discount_amount = ($total_amount * $discount_value) / 100;
                    } else {
                        // Fixed amount discount
                        $discount_amount = min($discount_value, $total_amount);
                    }
                }

                $after_discount = $total_amount - $discount_amount;

                // Calculate GST on discounted amount using single invoice-level GST rate
                $invoice_gst_rate = floatval($request->gst_rate);
                $total_gst_amount = ($after_discount * $invoice_gst_rate) / 100;
                $total_cgst = $total_gst_amount / 2;
                $total_sgst = $total_gst_amount / 2;

                $grand_total = $after_discount + $total_cgst + $total_sgst;

                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'customer_id' => $request->customer_id,
                    'invoice_date' => $request->invoice_date,
                    'due_date' => $request->due_date ?? now()->addDays(30),
                    'status' => 'draft',
                    'notes' => $request->notes,
                    'total_amount' => $total_amount,
                    'discount_type' => $discount_type,
                    'discount_value' => $discount_value,
                    'discount_amount' => $discount_amount,
                    'gst_rate' => $invoice_gst_rate,
                    'cgst' => $total_cgst,
                    'sgst' => $total_sgst,
                    'grand_total' => $grand_total,
                    'invoice_type' => 'gst',
                ]);

                foreach ($invoiceItems as $item) {
                    $colorVariant = ProductColorVariant::find($item['color_variant_id']);
                    $subtotal = $item['quantity'] * $item['price'];

                    $invoice->items()->create([
                        'product_id' => $item['product_id'],
                        'color_variant_id' => $item['color_variant_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'gst_rate' => $invoice_gst_rate, // Store invoice-level GST rate for reference
                        'cgst' => 0, // Individual item GST not calculated anymore
                        'sgst' => 0, // Individual item GST not calculated anymore
                        'subtotal' => $subtotal,
                    ]);

                    $this->stockService->outwardColorVariantStockSaleOnly($colorVariant, $item['quantity'], "Sale via Invoice #{$invoice->invoice_number}");
                }
            });

            return redirect()->route('invoices.gst.index')->with('success', 'GST Invoice created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating GST invoice: ' . $e->getMessage())->withInput();
        }
    }

    // Non-GST Invoice Methods

    public function indexNonGst(Request $request)
    {
        $query = Invoice::with('customer')->where('invoice_type', 'non_gst');

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%')
                ->orWhereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('overdue') && $request->overdue == '1') {
            $query->where('due_date', '<', now())
                  ->where('status', '!=', 'paid')
                  ->where('status', '!=', 'cancelled');
        }

        $invoices = $query->latest()->paginate(10);
        $customers = Customer::orderBy('name')->get();

        return view('invoices.index_non_gst', compact('invoices', 'customers'))->with('invoice_type', 'non_gst');
    }

    public function createNonGst()
    {
        $customers = Customer::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $productNames = Product::select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
        $invoice_number = Invoice::generateInvoiceNumber();
        return view('invoices.create_non_gst', compact('customers', 'productNames', 'invoice_number', 'categories'))->with('invoice_type', 'non_gst');
    }

    public function storeNonGst(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'discount_type' => 'nullable|numeric|in:0,1',
            'discount_value' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.variants' => 'required|array',
        ]);

        try {
            $invoiceItems = [];
            foreach ($request->items as $itemData) {
                $price = floatval($itemData['price']);
                if (isset($itemData['variants'])) {
                    foreach ($itemData['variants'] as $variantData) {
                        $quantity = intval($variantData['quantity'] ?? 0);
                        $product_id = $variantData['product_id'] ?? null;
                        if ($quantity > 0 && $product_id) {
                            $colorVariant = ProductColorVariant::find($product_id);
                            if (!$colorVariant) {
                                throw new \Exception("Product color variant not found (ID: {$product_id})");
                            }
                            if ($colorVariant->quantity < $quantity) {
                                throw new \Exception("Insufficient stock for {$colorVariant->product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$quantity}");
                            }
                            $invoiceItems[] = [
                                'color_variant_id' => $product_id,
                                'product_id' => $colorVariant->product_id,
                                'quantity' => $quantity,
                                'price' => $price,
                                'gst_rate' => 0,
                            ];
                        }
                    }
                }
            }

            if (empty($invoiceItems)) {
                throw new \Exception("Please add at least one item with quantity greater than 0");
            }

            DB::transaction(function () use ($request, $invoiceItems) {
                $total_amount = 0;
                foreach ($invoiceItems as $item) {
                    $subtotal = $item['quantity'] * $item['price'];
                    $total_amount += $subtotal;
                }

                // Calculate discount
                $discount_type = $request->discount_type ?? 0;
                $discount_value = $request->discount_value ?? 0;
                $discount_amount = 0;

                if ($discount_value > 0) {
                    if ($discount_type == 1) {
                        // Percentage discount
                        $discount_amount = ($total_amount * $discount_value) / 100;
                    } else {
                        // Fixed amount discount
                        $discount_amount = min($discount_value, $total_amount);
                    }
                }

                $grand_total = $total_amount - $discount_amount;

                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'customer_id' => $request->customer_id,
                    'invoice_date' => $request->invoice_date,
                    'due_date' => $request->due_date ?? now()->addDays(30),
                    'status' => 'draft',
                    'notes' => $request->notes,
                    'total_amount' => $total_amount,
                    'discount_type' => $discount_type,
                    'discount_value' => $discount_value,
                    'discount_amount' => $discount_amount,
                    'cgst' => 0,
                    'sgst' => 0,
                    'grand_total' => $grand_total,
                    'invoice_type' => 'non_gst',
                ]);

                foreach ($invoiceItems as $item) {
                    $colorVariant = ProductColorVariant::find($item['color_variant_id']);
                    $subtotal = $item['quantity'] * $item['price'];

                    $invoice->items()->create([
                        'product_id' => $item['product_id'],
                        'color_variant_id' => $item['color_variant_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'gst_rate' => 0,
                        'cgst' => 0,
                        'sgst' => 0,
                        'subtotal' => $subtotal,
                    ]);

                    $this->stockService->outwardColorVariantStockSaleOnly($colorVariant, $item['quantity'], "Sale via Non-GST Invoice #{$invoice->invoice_number}");
                }
            });

            return redirect()->route('invoices.non_gst.index')->with('success', 'Non-GST Invoice created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating Non-GST invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function showGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory', 'items.colorVariant');
        return view('invoices.show', compact('invoice'))->with('invoice_type', 'gst');
    }

    public function showNonGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory', 'items.colorVariant');
        return view('invoices.show_non_gst', compact('invoice'))->with('invoice_type', 'non_gst');
    }

    public function downloadPdfGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory', 'items.colorVariant');
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function downloadPdfNonGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory', 'items.colorVariant');
        $pdf = Pdf::loadView('invoices.pdf_non_gst', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function previewGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory', 'items.colorVariant');
        return view('invoices.pdf', compact('invoice'));
    }

    public function previewNonGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory', 'items.colorVariant');
        return view('invoices.pdf_non_gst', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        return redirect()->route('invoices.show', $invoice)->with('info', 'Invoices cannot be edited. Please create a new one if changes are needed.');
    }

    public function update(Request $request, Invoice $invoice)
    {
        return redirect()->route('invoices.show', $invoice);
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0|max:' . $invoice->amount_due,
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'nullable|string|max:255',
        ]);

        try {
            $invoice->markAsPaid(
                $request->payment_amount,
                $request->payment_date,
                $request->payment_method
            );

            $redirectRoute = $invoice->invoice_type === 'gst' ? 'invoices.gst.index' : 'invoices.non_gst.index';
            
            return redirect()->route($redirectRoute)
                ->with('success', 'Invoice marked as paid successfully.');
                
        } catch (\Exception $e) {
            $redirectRoute = $invoice->invoice_type === 'gst' ? 'invoices.gst.index' : 'invoices.non_gst.index';
            
            return redirect()->route($redirectRoute)
                ->with('error', 'Error marking invoice as paid: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        try {
            DB::transaction(function () use ($invoice) {
                foreach ($invoice->items as $item) {
                    // Find the color variant that was used for this invoice item
                    // Since we're storing product_id in invoice_items, we need to find the color variant
                    // that was actually sold. For now, we'll restore to the first available color variant
                    // of this product. In a future enhancement, we could store color_variant_id in invoice_items.
                    $colorVariant = $item->product->colorVariants()->first();
                    
                    if ($colorVariant) {
                        $this->stockService->inwardColorVariantStock(
                            $colorVariant, 
                            $item->quantity, 
                            "Stock restored from deleted Invoice #{$invoice->invoice_number}"
                        );
                    } else {
                        // Fallback to old method if no color variants exist
                        $this->stockService->inwardStock(
                            $item->product, 
                            $item->quantity, 
                            "Stock restored from deleted Invoice #{$invoice->invoice_number}"
                        );
                    }
                }
                $invoice->items()->delete();
                $invoice->delete();
            });

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully and stock has been restored.');
                
        } catch (\Exception $e) {
            return redirect()->route('invoices.index')
                ->with('error', 'Error deleting invoice: ' . $e->getMessage());
        }
    }
}
