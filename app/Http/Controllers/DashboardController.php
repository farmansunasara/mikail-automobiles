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
    public function index()
    {
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
                                ->take(5)
                                ->get();
        
        // Get monthly sales data for chart (last 6 months)
        $monthlySales = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $sales = Invoice::whereMonth('invoice_date', $date->month)
                           ->whereYear('invoice_date', $date->year)
                           ->sum('grand_total');
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
            ->take(10)
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
    }
}