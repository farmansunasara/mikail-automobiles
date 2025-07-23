<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Categories Management
    Route::resource('categories', CategoryController::class);
    Route::get('/api/subcategories/{category}', [CategoryController::class, 'getSubcategories'])->name('api.subcategories');
    
    // Products Management
    Route::resource('products', ProductController::class);
    Route::get('/api/products/search', [ProductController::class, 'search'])->name('api.products.search');
    Route::get('/api/products/{product}/components', [ProductController::class, 'getComponents'])->name('api.products.components');
    Route::get('/api/products/{product}/stock', [ProductController::class, 'getStock'])->name('api.products.stock');
    Route::get('/api/products/variants/{productName}', [ProductController::class, 'getProductVariants'])->name('api.products.variants');
    
    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/logs', [StockController::class, 'logs'])->name('stock.logs');
    Route::post('/stock/update', [StockController::class, 'update'])->name('stock.update');
    Route::get('/stock/product/{product}', [StockController::class, 'productLogs'])->name('stock.product');
    
    // Customers Management
    Route::resource('customers', CustomerController::class);
    Route::get('/api/customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
    
    // Invoices Management
    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
    Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    Route::post('/invoices/{invoice}/update-status', [InvoiceController::class, 'updateStatus'])->name('invoices.update-status');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.low-stock');
    Route::get('/reports/stock-report', [ReportController::class, 'stockReport'])->name('reports.stock-report');
    Route::get('/reports/product-movement', [ReportController::class, 'productMovement'])->name('reports.product-movement');
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/gst', [ReportController::class, 'gstReport'])->name('reports.gst');
});

require __DIR__.'/auth.php';
