<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
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
        $productNames = Product::where('is_composite', false)
            ->select('name')
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
            'items' => 'required|array|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.gst_rate' => 'required|numeric|min:0',
            'items.*.variants' => 'required|array',
        ]);

        try {
            $invoiceItems = [];
            foreach ($request->items as $itemData) {
                $price = floatval($itemData['price']);
                $gst_rate = floatval($itemData['gst_rate']);
                if (isset($itemData['variants'])) {
                    foreach ($itemData['variants'] as $variantData) {
                        $quantity = intval($variantData['quantity'] ?? 0);
                        $product_id = $variantData['product_id'] ?? null;
                        if ($quantity > 0 && $product_id) {
                            $product = Product::find($product_id);
                            if (!$product) {
                                throw new \Exception("Product not found (ID: {$product_id})");
                            }
                            if ($product->quantity < $quantity) {
                                $colorName = $product->color ?? 'No Color';
                                throw new \Exception("Insufficient stock for {$product->name} ({$colorName}). Available: {$product->quantity}, Required: {$quantity}");
                            }
                            $invoiceItems[] = [
                                'product_id' => $product_id,
                                'quantity' => $quantity,
                                'price' => $price,
                                'gst_rate' => $gst_rate,
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
                $total_cgst = 0;
                $total_sgst = 0;

                foreach ($invoiceItems as $item) {
                    $subtotal = $item['quantity'] * $item['price'];
                    $gst_amount = ($subtotal * $item['gst_rate']) / 100;
                    $total_amount += $subtotal;
                    $total_cgst += $gst_amount / 2;
                    $total_sgst += $gst_amount / 2;
                }

                $grand_total = $total_amount + $total_cgst + $total_sgst;

                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'customer_id' => $request->customer_id,
                    'invoice_date' => $request->invoice_date,
                    'due_date' => $request->due_date ?? now()->addDays(30),
                    'status' => 'draft',
                    'notes' => $request->notes,
                    'total_amount' => $total_amount,
                    'cgst' => $total_cgst,
                    'sgst' => $total_sgst,
                    'grand_total' => $grand_total,
                    'invoice_type' => 'gst',
                ]);

                foreach ($invoiceItems as $item) {
                    $product = Product::find($item['product_id']);
                    $subtotal = $item['quantity'] * $item['price'];
                    $gst_amount = ($subtotal * $item['gst_rate']) / 100;

                    $invoice->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'gst_rate' => $item['gst_rate'],
                        'cgst' => $gst_amount / 2,
                        'sgst' => $gst_amount / 2,
                        'subtotal' => $subtotal,
                    ]);

                    $this->stockService->outwardStock($product, $item['quantity'], "Sale via Invoice #{$invoice->invoice_number}");
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
        $productNames = Product::where('is_composite', false)
            ->select('name')
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
                            $product = Product::find($product_id);
                            if (!$product) {
                                throw new \Exception("Product not found (ID: {$product_id})");
                            }
                            if ($product->quantity < $quantity) {
                                throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->quantity}, Required: {$quantity}");
                            }
                            $invoiceItems[] = [
                                'product_id' => $product_id,
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
                $grand_total = $total_amount;

                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'customer_id' => $request->customer_id,
                    'invoice_date' => $request->invoice_date,
                    'due_date' => $request->due_date ?? now()->addDays(30),
                    'status' => 'draft',
                    'notes' => $request->notes,
                    'total_amount' => $total_amount,
                    'cgst' => 0,
                    'sgst' => 0,
                    'grand_total' => $grand_total,
                    'invoice_type' => 'non_gst',
                ]);

                foreach ($invoiceItems as $item) {
                    $product = Product::find($item['product_id']);
                    $subtotal = $item['quantity'] * $item['price'];

                    $invoice->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'gst_rate' => 0,
                        'cgst' => 0,
                        'sgst' => 0,
                        'subtotal' => $subtotal,
                    ]);

                    $this->stockService->outwardStock($product, $item['quantity'], "Sale via Non-GST Invoice #{$invoice->invoice_number}");
                }
            });

            return redirect()->route('invoices.non_gst.index')->with('success', 'Non-GST Invoice created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating Non-GST invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function showGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory');
        return view('invoices.show', compact('invoice'))->with('invoice_type', 'gst');
    }

    public function showNonGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory');
        return view('invoices.show_non_gst', compact('invoice'))->with('invoice_type', 'non_gst');
    }

    public function downloadPdfGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory');
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function downloadPdfNonGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory');
        $pdf = Pdf::loadView('invoices.pdf_non_gst', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function previewGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory');
        return view('invoices.pdf', compact('invoice'));
    }

    public function previewNonGst(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product.category', 'items.product.subcategory');
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

    public function destroy(Invoice $invoice)
    {
        try {
            DB::transaction(function () use ($invoice) {
                foreach ($invoice->items as $item) {
                    $this->stockService->inwardStock(
                        $item->product, 
                        $item->quantity, 
                        "Stock restored from deleted Invoice #{$invoice->invoice_number}"
                    );
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
