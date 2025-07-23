<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Show the main reports page.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Show low stock report.
     */
    public function lowStock(Request $request)
    {
        $threshold = $request->input('threshold', 10);
        $lowStockProducts = Product::with('category', 'subcategory')
            ->where('quantity', '<', $threshold)
            ->orderBy('quantity', 'asc')
            ->paginate(20);

        return view('reports.low_stock', compact('lowStockProducts', 'threshold'));
    }

    /**
     * Show current stock report.
     */
    public function stockReport(Request $request)
    {
        $query = Product::with('category', 'subcategory');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $stockReport = $query->orderBy('name')->paginate(20);
        
        // FIXED: Using selectRaw instead of DB::raw for security
        $totalValue = Product::selectRaw('SUM(quantity * price) as total_value')
                            ->when($request->filled('category_id'), function($q) use ($request) {
                                return $q->where('category_id', $request->category_id);
                            })
                            ->value('total_value') ?? 0;

        return view('reports.stock_report', compact('stockReport', 'totalValue'));
    }

    /**
     * Show product movement history.
     */
    public function productMovement(Request $request)
    {
        $query = StockLog::with('product');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movementHistory = $query->latest()->paginate(25);
        return view('reports.product_movement', compact('movementHistory'));
    }

    /**
     * Show sales report.
     */
    public function salesReport(Request $request)
    {
        $query = Invoice::with('customer');

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $query->whereBetween('invoice_date', [$startDate, $endDate]);

        $salesReport = $query->latest()->paginate(20);
        
        // FIXED: Using selectRaw instead of DB::raw for security
        $totals = Invoice::selectRaw('
            SUM(total_amount) as total_amount,
            SUM(cgst) as total_cgst,
            SUM(sgst) as total_sgst,
            SUM(grand_total) as grand_total
        ')
        ->whereBetween('invoice_date', [$startDate, $endDate])
        ->first();

        return view('reports.sales', compact('salesReport', 'totals', 'startDate', 'endDate'));
    }

    /**
     * Show GST report.
     */
    public function gstReport(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);
        
        $targetMonth = $request->filled('month') ? Carbon::parse($request->month) : Carbon::now();
        
        // FIXED: Using selectRaw instead of DB::raw for security
        $gstReport = Invoice::whereYear('invoice_date', $targetMonth->year)
            ->whereMonth('invoice_date', $targetMonth->month)
            ->selectRaw('
                SUM(total_amount) as taxable_value,
                SUM(cgst) as total_cgst,
                SUM(sgst) as total_sgst,
                SUM(grand_total) as total_amount
            ')
            ->first();

        $invoices = Invoice::with('customer')
            ->whereYear('invoice_date', $targetMonth->year)
            ->whereMonth('invoice_date', $targetMonth->month)
            ->get();

        return view('reports.gst', compact('gstReport', 'invoices', 'targetMonth'));
    }
}
