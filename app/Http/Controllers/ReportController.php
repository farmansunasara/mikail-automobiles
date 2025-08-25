<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductColorVariant;
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
        
        // Get color variants with low stock
        $lowStockVariants = ProductColorVariant::with(['product.category', 'product.subcategory'])
            ->where('quantity', '<', $threshold)
            ->orderBy('quantity', 'asc')
            ->paginate(20);

        return view('reports.low_stock', compact('lowStockVariants', 'threshold'));
    }

    /**
     * Show current stock report.
     */
    public function stockReport(Request $request)
    {
        $query = ProductColorVariant::with(['product.category', 'product.subcategory']);

        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $stockReport = $query->orderBy('color')->paginate(20);
        
        // Calculate total value from color variants
        $totalValue = ProductColorVariant::with('product')
            ->when($request->filled('category_id'), function($q) use ($request) {
                return $q->whereHas('product', function($subQ) use ($request) {
                    $subQ->where('category_id', $request->category_id);
                });
            })
            ->get()
            ->sum(function($variant) {
                return $variant->quantity * $variant->product->price;
            });

        return view('reports.stock_report', compact('stockReport', 'totalValue'));
    }

    /**
     * Show product movement history.
     */
    public function productMovement(Request $request)
    {
        $query = StockLog::with(['product', 'colorVariant']);

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
        
        // FIXED: Using selectRaw instead of DB::raw for security and filter only GST invoices
        $gstReport = Invoice::whereYear('invoice_date', $targetMonth->year)
            ->whereMonth('invoice_date', $targetMonth->month)
            ->where('invoice_type', 'gst')
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
            ->where('invoice_type', 'gst')
            ->get();

        return view('reports.gst', compact('gstReport', 'invoices', 'targetMonth'));
    }

    /**
     * Show Non-GST report.
     */
    public function nonGstReport(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);
        $targetMonth = $request->filled('month') ? Carbon::parse($request->month) : Carbon::now();
        // Get Non-GST report summary
        $nonGstReport = Invoice::whereYear('invoice_date', $targetMonth->year)
            ->whereMonth('invoice_date', $targetMonth->month)
            ->where('invoice_type', 'non_gst')
            ->selectRaw('
                SUM(total_amount) as total_amount,
                SUM(grand_total) as grand_total,
                COUNT(*) as invoice_count
            ')
            ->first();

        $invoices = Invoice::with('customer')
            ->whereYear('invoice_date', $targetMonth->year)
            ->whereMonth('invoice_date', $targetMonth->month)
            ->where('invoice_type', 'non_gst')
            ->get();

        return view('reports.non_gst', compact('nonGstReport', 'invoices', 'targetMonth'));
    }

    /**
     * Export low stock report to CSV.
     */
    public function exportLowStock(Request $request)
    {
        $threshold = $request->input('threshold', 10);
        $lowStockVariants = ProductColorVariant::with(['product.category', 'product.subcategory'])
            ->where('quantity', '<', $threshold)
            ->orderBy('quantity', 'asc')
            ->get();

        $filename = 'low_stock_report_' . now()->format('Y_m_d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($lowStockVariants) {
            $file = fopen('php://output', 'w');
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Product Name',
                'Color',
                'Category',
                'Subcategory',
                'Current Quantity',
                'Price',
                'Stock Value'
            ]);

            // Add data rows
            foreach ($lowStockVariants as $variant) {
                fputcsv($file, [
                    $variant->id,
                    $variant->product->name,
                    $variant->color,
                    $variant->product->category->name,
                    $variant->product->subcategory->name ?? 'N/A',
                    $variant->quantity,
                    number_format($variant->product->price, 2),
                    number_format($variant->quantity * $variant->product->price, 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export stock report to CSV.
     */
    public function exportStockReport(Request $request)
    {
        $query = ProductColorVariant::with(['product.category', 'product.subcategory']);

        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $stockReport = $query->orderBy('color')->get();

        $filename = 'stock_report_' . now()->format('Y_m_d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($stockReport) {
            $file = fopen('php://output', 'w');
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Product Name',
                'Color',
                'Category',
                'Subcategory',
                'Quantity',
                'Price',
                'Stock Value'
            ]);

            // Add data rows
            foreach ($stockReport as $variant) {
                fputcsv($file, [
                    $variant->id,
                    $variant->product->name,
                    $variant->color,
                    $variant->product->category->name,
                    $variant->product->subcategory->name ?? 'N/A',
                    $variant->quantity,
                    number_format($variant->product->price, 2),
                    number_format($variant->quantity * $variant->product->price, 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export product movement report to CSV.
     */
    public function exportProductMovement(Request $request)
    {
        $query = StockLog::with(['product', 'colorVariant']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movementHistory = $query->latest()->get();
        $filename = 'product_movement_report_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($movementHistory) {
            $file = fopen('php://output', 'w');
            // Add CSV headers
            fputcsv($file, [
                'Date',
                'Product Name',
                'Color',
                'Change Type',
                'Quantity',
                'Remarks'
            ]);

            // Add data rows
            foreach ($movementHistory as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->product->name,
                    $log->colorVariant ? $log->colorVariant->color : ($log->product->color ?? 'N/A'),
                    ucfirst($log->change_type),
                    $log->quantity,
                    $log->remarks ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sales report to CSV.
     */
    public function exportSalesReport(Request $request)
    {
        $query = Invoice::with('customer');

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $query->whereBetween('invoice_date', [$startDate, $endDate]);
        $salesReport = $query->latest()->get();
        $filename = 'sales_report_' . $startDate->format('Y_m_d') . '_to_' . $endDate->format('Y_m_d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($salesReport) {
            $file = fopen('php://output', 'w');
            // Add CSV headers
            fputcsv($file, [
                'Invoice Number',
                'Customer Name',
                'Invoice Date',
                'Invoice Type',
                'Total Amount',
                'CGST',
                'SGST',
                'Grand Total',
                'Status'
            ]);

            // Add data rows
            foreach ($salesReport as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->customer->name,
                    $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : 'N/A',
                    ucfirst($invoice->invoice_type),
                    number_format((float)$invoice->total_amount, 2),
                    number_format((float)$invoice->cgst, 2),
                    number_format((float)$invoice->sgst, 2),
                    number_format((float)$invoice->grand_total, 2),
                    ucfirst($invoice->status)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export GST report to CSV.
     */
    public function exportGstReport(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $targetMonth = $request->filled('month') ? Carbon::parse($request->month) : Carbon::now();
        $invoices = Invoice::with('customer')
            ->whereYear('invoice_date', $targetMonth->year)
            ->whereMonth('invoice_date', $targetMonth->month)
            ->where('invoice_type', 'gst')
            ->get();

        $filename = 'gst_report_' . $targetMonth->format('Y_m') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($invoices, $targetMonth) {
            $file = fopen('php://output', 'w');
            // Add CSV headers
            fputcsv($file, [
                'Date',
                'Invoice Number',
                'Customer Name',
                'GSTIN',
                'Taxable Value',
                'CGST',
                'SGST',
                'Total Amount'
            ]);

            // Add data rows
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : 'N/A',
                    $invoice->invoice_number,
                    $invoice->customer->name,
                    $invoice->customer->gstin ?? 'N/A',
                    number_format((float)$invoice->total_amount, 2),
                    number_format((float)$invoice->cgst, 2),
                    number_format((float)$invoice->sgst, 2),
                    number_format((float)$invoice->grand_total, 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Non-GST report to CSV.
     */
    public function exportNonGstReport(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);
        $targetMonth = $request->filled('month') ? Carbon::parse($request->month) : Carbon::now();
        $invoices = Invoice::with('customer')
            ->whereYear('invoice_date', $targetMonth->year)
            ->whereMonth('invoice_date', $targetMonth->month)
            ->where('invoice_type', 'non_gst')
            ->get();

        $filename = 'non_gst_report_' . $targetMonth->format('Y_m') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($invoices, $targetMonth) {
            $file = fopen('php://output', 'w');
            // Add CSV headers
            fputcsv($file, [
                'Date',
                'Invoice Number',
                'Customer Name',
                'Total Amount',
                'Grand Total'
            ]);

            // Add data rows
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : 'N/A',
                    $invoice->invoice_number,
                    $invoice->customer->name,
                    number_format((float)$invoice->total_amount, 2),
                    number_format((float)$invoice->grand_total, 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
