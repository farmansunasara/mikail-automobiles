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
        $products = Product::orderBy('name')->get();
        $invoice_number = Invoice::generateInvoiceNumber();
        return view('invoices.create', compact('customers', 'products', 'invoice_number'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.gst_rate' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $total_amount = 0;
                $total_cgst = 0;
                $total_sgst = 0;

                foreach ($request->items as $item) {
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
                    'notes' => $request->notes,
                    'total_amount' => $total_amount,
                    'cgst' => $total_cgst,
                    'sgst' => $total_sgst,
                    'grand_total' => $grand_total,
                ]);

                foreach ($request->items as $item) {
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
        // Add logic to revert stock if needed (complex)
        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully. Note: Stock was not automatically reverted.');
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
}
