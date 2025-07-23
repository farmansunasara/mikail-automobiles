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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with('customer');

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

        return view('invoices.index', compact('invoices', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        // Get unique product names for the dropdown
        $productNames = Product::where('is_composite', false)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
        $invoice_number = Invoice::generateInvoiceNumber();
        return view('invoices.create', compact('customers', 'productNames', 'invoice_number'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.gst_rate' => 'required|numeric|min:0',
            'items.*.colors' => 'required|array',
        ]);

        try {
            // Collect all items with quantities > 0 and validate stock
            $invoiceItems = [];
            foreach ($request->items as $productRow) {
                $price = $productRow['price'];
                $gst_rate = $productRow['gst_rate'];
                
                foreach ($productRow['colors'] as $colorKey => $colorData) {
                    $quantity = intval($colorData['quantity'] ?? 0);
                    $product_id = $colorData['product_id'] ?? null;
                    
                    if ($quantity > 0 && $product_id) {
                        $product = Product::find($product_id);
                        if (!$product) {
                            throw new \Exception("Product not found for {$productRow['product_name']} (Color Key: {$colorKey})");
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

            if (empty($invoiceItems)) {
                throw new \Exception("Please add at least one item with quantity greater than 0");
            }

            DB::transaction(function () use ($request, $invoiceItems) {
                $total_amount = 0;
                $total_cgst = 0;
                $total_sgst = 0;

                // Calculate totals
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
                ]);

                // Create invoice items and update stock
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

                    // Deduct stock
                    $this->stockService->outwardStock($product, $item['quantity'], "Sale via Invoice #{$invoice->invoice_number}");
                }
            });

            return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product');
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        // Generally, editing a finalized invoice is not a good practice.
        // It's better to cancel and create a new one.
        // For this example, we'll prevent editing.
        return redirect()->route('invoices.show', $invoice)->with('info', 'Invoices cannot be edited. Please create a new one if changes are needed.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Prevent updates
        return redirect()->route('invoices.show', $invoice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        try {
            DB::transaction(function () use ($invoice) {
                // Restore stock for each item before deleting
                foreach ($invoice->items as $item) {
                    $this->stockService->inwardStock(
                        $item->product, 
                        $item->quantity, 
                        "Stock restored from deleted Invoice #{$invoice->invoice_number}"
                    );
                }
                
                // Delete invoice items and invoice
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

    /**
     * Download the invoice as a PDF.
     */
    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product');
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Show a preview of the invoice.
     */
    public function preview(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product');
        return view('invoices.pdf', compact('invoice'));
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0|max:' . $invoice->amount_due,
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:255'
        ]);

        $amount = $request->amount ?? $invoice->amount_due;
        $date = $request->payment_date ?? now();
        $method = $request->payment_method ?? 'Cash';

        $invoice->markAsPaid($amount, $date, $method);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice marked as paid successfully.');
    }

    /**
     * Update invoice status
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,paid,cancelled,overdue'
        ]);

        $invoice->update(['status' => $request->status]);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice status updated successfully.');
    }
}
