<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\StockLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        
        // Calculate total stock value
        $totalStockValue = Product::sum(\DB::raw('quantity * price'));
        
        // Get invoices this month
        $invoicesThisMonth = Invoice::whereMonth('invoice_date', Carbon::now()->month)
                                  ->whereYear('invoice_date', Carbon::now()->year)
                                  ->count();
        
        // Get low stock items (quantity < 10)
        $lowStockItems = Product::where('quantity', '<', 10)->count();
        
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
        
        // Get low stock products
        $lowStockProducts = Product::with(['category', 'subcategory'])
                                  ->where('quantity', '<', 10)
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
