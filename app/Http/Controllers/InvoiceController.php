<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Services\StockService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    protected $stockService;
    protected $invoiceService;

    public function __construct(StockService $stockService, InvoiceService $invoiceService)
    {
        $this->stockService = $stockService;
        $this->invoiceService = $invoiceService;
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

        $invoices = $query->latest()->paginate(10)->appends($request->query());
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
            'packaging_fees' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.variants' => 'required|array',
        ]);

        try {
            \Log::info('GST Invoice creation started', [
                'user_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'items_count' => count($request->items)
            ]);

            // Use optimized invoice service
            $invoice = $this->invoiceService->createGstInvoice($request->all());

            \Log::info('GST Invoice created successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'grand_total' => $invoice->grand_total
            ]);

            return redirect()->route('invoices.gst.show', $invoice)
                ->with('success', 'GST Invoice created successfully!');

        } catch (\Exception $e) {
            \Log::error('GST Invoice creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Error creating invoice: ' . $e->getMessage());
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

        $invoices = $query->latest()->paginate(10)->appends($request->query());
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
            'packaging_fees' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.variants' => 'required|array',
        ]);

        try {
            \Log::info('Non-GST Invoice creation started', [
                'user_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'items_count' => count($request->items)
            ]);

            // Use optimized invoice service
            $invoice = $this->invoiceService->createNonGstInvoice($request->all());

            \Log::info('Non-GST Invoice created successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'grand_total' => $invoice->grand_total
            ]);

            return redirect()->route('invoices.non_gst.show', $invoice)
                ->with('success', 'Non-GST Invoice created successfully!');

        } catch (\Exception $e) {
            \Log::error('Non-GST Invoice creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Error creating invoice: ' . $e->getMessage());
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
        // Check if invoice can be edited (only draft and sent status)
        if (!in_array($invoice->status, ['draft', 'sent'])) {
            $redirectRoute = $invoice->invoice_type === 'gst' ? 'invoices.gst.index' : 'invoices.non_gst.index';
            return redirect()->route($redirectRoute)
                ->with('error', 'Only draft and sent invoices can be edited.');
        }

        $customers = Customer::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $productNames = Product::select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        // Load invoice with relationships
        $invoice->load('customer', 'items.product.category', 'items.colorVariant');

        // Get all color variants for each product in the invoice
        $productVariants = [];
        $groupedItems = $invoice->items->groupBy('product_id');
        foreach ($groupedItems as $productId => $items) {
            $product = $items->first()->product;
            $allVariants = ProductColorVariant::where('product_id', $productId)
                ->with(['product.components.componentProduct', 'colorModel'])
                ->get();
            // Create a map of existing quantities
            $existingQuantities = [];
            foreach ($items as $item) {
                $existingQuantities[$item->color_variant_id] = $item->quantity;
            }
            $productVariants[$productId] = [
                'product' => $product,
                'variants' => $allVariants,
                'existing_quantities' => $existingQuantities,
                'price' => $items->first()->price
            ];
        }

        if ($invoice->invoice_type === 'gst') {
            return view('invoices.edit_gst', compact('invoice', 'customers', 'productNames', 'categories', 'productVariants'));
        } else {
            return view('invoices.edit_non_gst', compact('invoice', 'customers', 'productNames', 'categories', 'productVariants'));
        }
    }

    public function update(Request $request, Invoice $invoice)
    {
        // Check if invoice can be edited
        if (!in_array($invoice->status, ['draft', 'sent'])) {
            $redirectRoute = $invoice->invoice_type === 'gst' ? 'invoices.gst.index' : 'invoices.non_gst.index';
            return redirect()->route($redirectRoute)
                ->with('error', 'Only draft and sent invoices can be edited.');
        }

        if ($invoice->invoice_type === 'gst') {
            return $this->updateGst($request, $invoice);
        } else {
            return $this->updateNonGst($request, $invoice);
        }
    }

    private function updateGst(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'gst_rate' => 'required|numeric|min:0|max:100',
            'discount_type' => 'nullable|numeric|in:0,1',
            'discount_value' => 'nullable|numeric|min:0',
            'packaging_fees' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.variants' => 'required|array',
        ]);

        try {
            \Log::info('GST Invoice edit started', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'user_id' => auth()->id(),
                'original_total' => $invoice->grand_total
            ]);

            // Parse new invoice items
            $newInvoiceItems = [];
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
                            $newInvoiceItems[] = [
                                'color_variant_id' => $product_id,
                                'product_id' => $colorVariant->product_id,
                                'quantity' => $quantity,
                                'price' => $price,
                            ];
                        }
                    }
                }
            }

            if (empty($newInvoiceItems)) {
                throw new \Exception("Please add at least one item with quantity greater than 0");
            }

            DB::transaction(function () use ($request, $invoice, $newInvoiceItems) {
                // First, restore stock from old invoice items
                foreach ($invoice->items as $oldItem) {
                    $this->stockService->inwardColorVariantStockSaleOnly(
                        $oldItem->colorVariant,
                        $oldItem->quantity,
                        "Stock restored from edited Invoice #{$invoice->invoice_number}"
                    );
                }

                // Check new stock availability and deduct
                foreach ($newInvoiceItems as $item) {
                    $colorVariant = ProductColorVariant::find($item['color_variant_id']);
                    if ($colorVariant->quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$colorVariant->product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$item['quantity']}");
                    }
                }

                // Calculate totals
                $total_amount = 0;
                foreach ($newInvoiceItems as $item) {
                    $subtotal = $item['quantity'] * $item['price'];
                    $total_amount += $subtotal;
                }

                // Calculate discount
                $discount_type = $request->discount_type ?? 0;
                $discount_value = $request->discount_value ?? 0;
                $discount_amount = 0;

                if ($discount_value > 0) {
                    if ($discount_type == 1) {
                        $discount_amount = ($total_amount * $discount_value) / 100;
                    } else {
                        $discount_amount = min($discount_value, $total_amount);
                    }
                }

                $after_discount = $total_amount - $discount_amount;

                // Calculate GST
                $invoice_gst_rate = floatval($request->gst_rate);
                $total_gst_amount = ($after_discount * $invoice_gst_rate) / 100;
                $total_cgst = $total_gst_amount / 2;
                $total_sgst = $total_gst_amount / 2;

                $grand_total = $after_discount + $total_cgst + $total_sgst;

                // Update invoice
                $invoice->update([
                    'customer_id' => $request->customer_id,
                    'invoice_date' => $request->invoice_date,
                    'due_date' => $request->due_date ?? now()->addDays(30),
                    'notes' => $request->notes,
                    'total_amount' => $total_amount,
                    'discount_type' => $discount_type,
                    'discount_value' => $discount_value,
                    'discount_amount' => $discount_amount,
                    'gst_rate' => $invoice_gst_rate,
                    'cgst' => $total_cgst,
                    'sgst' => $total_sgst,
                    'grand_total' => $grand_total,
                ]);

                // Delete old items
                $invoice->items()->delete();

                // Create new items and deduct stock
                foreach ($newInvoiceItems as $item) {
                    $colorVariant = ProductColorVariant::find($item['color_variant_id']);
                    $subtotal = $item['quantity'] * $item['price'];

                    $invoice->items()->create([
                        'product_id' => $item['product_id'],
                        'color_variant_id' => $item['color_variant_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'gst_rate' => $invoice_gst_rate,
                        'cgst' => 0,
                        'sgst' => 0,
                        'subtotal' => $subtotal,
                    ]);

                    $this->stockService->outwardColorVariantStockSaleOnly($colorVariant, $item['quantity'], "Sale via Updated Invoice #{$invoice->invoice_number}");
                }
            });

            \Log::info('GST Invoice edit completed successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'user_id' => auth()->id(),
                'new_total' => $invoice->fresh()->grand_total
            ]);

            return redirect()->route('invoices.gst.index')->with('success', 'GST Invoice updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating GST invoice: ' . $e->getMessage())->withInput();
        }
    }

    private function updateNonGst(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'discount_type' => 'nullable|numeric|in:0,1',
            'discount_value' => 'nullable|numeric|min:0',
            'packaging_fees' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.variants' => 'required|array',
        ]);

        try {
            // Parse new invoice items
            $newInvoiceItems = [];
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
                            $newInvoiceItems[] = [
                                'color_variant_id' => $product_id,
                                'product_id' => $colorVariant->product_id,
                                'quantity' => $quantity,
                                'price' => $price,
                            ];
                        }
                    }
                }
            }

            if (empty($newInvoiceItems)) {
                throw new \Exception("Please add at least one item with quantity greater than 0");
            }

            DB::transaction(function () use ($request, $invoice, $newInvoiceItems) {
                // First, restore stock from old invoice items
                foreach ($invoice->items as $oldItem) {
                    $this->stockService->inwardColorVariantStockSaleOnly(
                        $oldItem->colorVariant,
                        $oldItem->quantity,
                        "Stock restored from edited Invoice #{$invoice->invoice_number}"
                    );
                }

                // Check new stock availability
                foreach ($newInvoiceItems as $item) {
                    $colorVariant = ProductColorVariant::find($item['color_variant_id']);
                    if ($colorVariant->quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$colorVariant->product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$item['quantity']}");
                    }
                }

                // Calculate totals
                $total_amount = 0;
                foreach ($newInvoiceItems as $item) {
                    $subtotal = $item['quantity'] * $item['price'];
                    $total_amount += $subtotal;
                }

                // Calculate discount
                $discount_type = $request->discount_type ?? 0;
                $discount_value = $request->discount_value ?? 0;
                $discount_amount = 0;

                if ($discount_value > 0) {
                    if ($discount_type == 1) {
                        $discount_amount = ($total_amount * $discount_value) / 100;
                    } else {
                        $discount_amount = min($discount_value, $total_amount);
                    }
                }

                $grand_total = $total_amount - $discount_amount;

                // Update invoice
                $invoice->update([
                    'customer_id' => $request->customer_id,
                    'invoice_date' => $request->invoice_date,
                    'due_date' => $request->due_date ?? now()->addDays(30),
                    'notes' => $request->notes,
                    'total_amount' => $total_amount,
                    'discount_type' => $discount_type,
                    'discount_value' => $discount_value,
                    'discount_amount' => $discount_amount,
                    'cgst' => 0,
                    'sgst' => 0,
                    'grand_total' => $grand_total,
                ]);

                // Delete old items
                $invoice->items()->delete();

                // Create new items and deduct stock
                foreach ($newInvoiceItems as $item) {
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

                    $this->stockService->outwardColorVariantStockSaleOnly($colorVariant, $item['quantity'], "Sale via Updated Non-GST Invoice #{$invoice->invoice_number}");
                }
            });

            return redirect()->route('invoices.non_gst.index')->with('success', 'Non-GST Invoice updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating Non-GST invoice: ' . $e->getMessage())->withInput();
        }
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
                    // Restore stock to the actual color variant sold if available
                    $colorVariant = $item->colorVariant ?? null;
                    if ($colorVariant) {
                        $this->stockService->inwardColorVariantStock(
                            $colorVariant,
                            $item->quantity,
                            "Stock restored from deleted Invoice #{$invoice->invoice_number}"
                        );
                    } else {
                        // Fallback to product-level stock restoration
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
