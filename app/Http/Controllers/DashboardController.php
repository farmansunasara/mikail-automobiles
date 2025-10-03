<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\StockLog;
use App\Models\ProductColorVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Configuration constants
    private const RECENT_INVOICES_LIMIT = 5;
    private const LOW_STOCK_PRODUCTS_LIMIT = 10;
    private const MONTHLY_SALES_MONTHS = 6;
    
    public function index()
    {
        try {
            // Get dashboard statistics
            $totalProducts = Product::count();
            $totalCustomers = Customer::count();
        
        // Calculate total stock value - Using ProductColorVariant
        $totalStockValue = ProductColorVariant::with('product')
            ->selectRaw('SUM(product_color_variants.quantity * products.price) as total_value')
            ->join('products', 'product_color_variants.product_id', '=', 'products.id')
            ->value('total_value') ?? 0;
        
        // Get invoices this month
        $invoicesThisMonth = Invoice::whereMonth('invoice_date', Carbon::now()->month)
                                  ->whereYear('invoice_date', Carbon::now()->year)
                                  ->count();
        
        // Get low stock items (quantity < color variant minimum_threshold)
        $lowStockItems = ProductColorVariant::whereNotNull('minimum_threshold')
            ->whereColumn('quantity', '<', 'minimum_threshold')
            ->count();
        
        // Get recent invoices
        $recentInvoices = Invoice::with('customer')
                                ->latest()
                                ->take(self::RECENT_INVOICES_LIMIT)
                                ->get();
        
        // Get monthly sales data for chart - Optimized single query
        $startDate = Carbon::now()->subMonths(self::MONTHLY_SALES_MONTHS - 1)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $monthlySalesData = Invoice::selectRaw('
                DATE_FORMAT(invoice_date, "%Y-%m") as month,
                SUM(grand_total) as total_sales
            ')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_sales', 'month')
            ->toArray();
        
        // Fill in missing months with zero values
        $monthlySales = [];
        for ($i = self::MONTHLY_SALES_MONTHS - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $sales = $monthlySalesData[$monthKey] ?? 0;
            
            $monthlySales[] = [
                'month' => $date->format('M Y'),
                'sales' => $sales
            ];
        }
        
        // Get low stock products - Using color variant minimum_threshold
        $lowStockProducts = ProductColorVariant::with(['product.category', 'product.subcategory'])
            ->whereNotNull('minimum_threshold')
            ->whereColumn('quantity', '<', 'minimum_threshold')
            ->orderBy('quantity', 'asc')
            ->take(self::LOW_STOCK_PRODUCTS_LIMIT)
            ->get();
        
            return view('dashboard', compact(
                'totalProducts',
                'totalCustomers', 
                'totalStockValue',
                'invoicesThisMonth',
                'lowStockItems',
                'recentInvoices',
                'monthlySales',
                'lowStockProducts'
            ));
        } catch (\Exception $e) {
            \Log::error('Dashboard data loading failed: ' . $e->getMessage());
            
            // Return dashboard with default values on error
            return view('dashboard', [
                'totalProducts' => 0,
                'totalCustomers' => 0,
                'totalStockValue' => 0,
                'invoicesThisMonth' => 0,
                'lowStockItems' => 0,
                'recentInvoices' => collect(),
                'monthlySales' => [],
                'lowStockProducts' => collect()
            ]);
        }
    }
}