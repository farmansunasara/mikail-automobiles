<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Redirect generic invoices.index to GST invoices index
Route::middleware('auth')->group(function () {
    Route::get('/invoices', function () {
        return redirect()->route('invoices.gst.index');
    })->name('invoices.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Categories Management (Admin only)
    Route::resource('categories', CategoryController::class)->middleware('role:admin');
    Route::get('/api/subcategories/{category}', [CategoryController::class, 'getSubcategories'])->name('api.subcategories');
    
    // Colors Management (Admin only)
    Route::get('/colors/low-stock', [ColorController::class, 'lowStock'])->name('colors.low-stock');
    Route::resource('colors', ColorController::class)->middleware('role:admin');
    Route::get('/api/colors/search', [ColorController::class, 'search'])->name('api.colors.search');
    Route::post('/colors/{color}/update-stock', [ColorController::class, 'updateStock'])->name('colors.update-stock');
    
    // Products Management (Admin only)
    Route::resource('products', ProductController::class)->middleware('role:admin');
    Route::get('/api/products/search', [ProductController::class, 'search'])->name('api.products.search');
    Route::get('/api/products/{product}/components', [ProductController::class, 'getComponents'])->name('api.products.components');
    Route::get('/api/products/{product}/stock', [ProductController::class, 'getStock'])->name('api.products.stock');
    Route::get('/api/products/variants/{productName}', [ProductController::class, 'getProductVariants'])->name('api.products.variants');
    Route::get('/api/products/{product}/color-variants', [StockController::class, 'getProductColorVariants'])->name('api.products.color-variants');
    Route::get('/api/products/by-category', [ProductController::class, 'getProductsByCategory'])->name('api.products.by-category');
    Route::get('/api/products/by-category-components', [ProductController::class, 'getProductsByCategoryForComponents'])->name('api.products.by-category-components');
    
    // Stock Management (Admin only)
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/logs', [StockController::class, 'logs'])->name('stock.logs');
    Route::post('/stock/update', [StockController::class, 'update'])->name('stock.update')->middleware('role:admin');
    Route::get('/stock/product/{product}', [StockController::class, 'productLogs'])->name('stock.product');
    
    // Customers Management (Admin only)
    Route::resource('customers', CustomerController::class)->middleware('role:admin');
    Route::get('/api/customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
    
    // GST Invoices
    Route::prefix('invoices/gst')->name('invoices.gst.')->group(function () {
        Route::get('/', [InvoiceController::class, 'indexGst'])->name('index');
        Route::get('/create', [InvoiceController::class, 'createGst'])->name('create');
        Route::post('/', [InvoiceController::class, 'storeGst'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'showGst'])->name('show');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::get('/{invoice}/download', [InvoiceController::class, 'downloadPdfGst'])->name('download');
        Route::get('/{invoice}/preview', [InvoiceController::class, 'previewGst'])->name('preview');
    });

    // Non-GST Invoices
    Route::prefix('invoices/non-gst')->name('invoices.non_gst.')->group(function () {
        Route::get('/', [InvoiceController::class, 'indexNonGst'])->name('index');
        Route::get('/create', [InvoiceController::class, 'createNonGst'])->name('create');
        Route::post('/', [InvoiceController::class, 'storeNonGst'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'showNonGst'])->name('show');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::get('/{invoice}/download', [InvoiceController::class, 'downloadPdfNonGst'])->name('download');
        Route::get('/{invoice}/preview', [InvoiceController::class, 'previewNonGst'])->name('preview');
    });
    
    // Common invoice routes (used by both GST and Non-GST) - Admin only for sensitive operations
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::post('/invoices/{invoice}/mark-sent', [InvoiceController::class, 'markSent'])->name('invoices.mark-sent');
    Route::post('/invoices/{invoice}/mark-cancelled', [InvoiceController::class, 'markCancelled'])->name('invoices.mark-cancelled');
    Route::post('/invoices/{invoice}/partial-payment', [InvoiceController::class, 'partialPayment'])->name('invoices.partial-payment');
    Route::post('/invoices/{invoice}/dispute', [InvoiceController::class, 'markDisputed'])->name('invoices.dispute');
    Route::get('/invoices/{invoice}/payment-history', [InvoiceController::class, 'paymentHistory'])->name('invoices.payment-history');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy')->middleware('role:admin');
    
    // Orders Management
    Route::resource('orders', \App\Http\Controllers\OrderController::class);
    Route::get('/orders/{order}/generate-invoice', [\App\Http\Controllers\OrderController::class, 'generateInvoice'])->name('orders.generate-invoice');
    Route::delete('/orders/{order}/cancel', [\App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/api/orders/product-variants', [\App\Http\Controllers\OrderController::class, 'getProductVariants'])->name('orders.product-variants');
    Route::get('/api/orders/stock-availability', [\App\Http\Controllers\OrderController::class, 'getStockAvailability'])->name('orders.stock-availability');
    
    // Manufacturing Requirements (Simplified Dynamic System)
    Route::get('/manufacturing/requirements', [\App\Http\Controllers\ManufacturingRequirementController::class, 'index'])->name('manufacturing.requirements.index');
    Route::post('/manufacturing/requirements/add-stock', [\App\Http\Controllers\ManufacturingRequirementController::class, 'addStock'])->name('manufacturing.requirements.add-stock');
    Route::post('/manufacturing/requirements/add-component-stock', [\App\Http\Controllers\ManufacturingRequirementController::class, 'addComponentStock'])->name('manufacturing.requirements.add-component-stock');
    Route::get('/api/manufacturing/requirements-data', [\App\Http\Controllers\ManufacturingRequirementController::class, 'getRequirementsData'])->name('manufacturing.requirements.data');
    Route::get('/api/manufacturing/ready-orders', [\App\Http\Controllers\ManufacturingRequirementController::class, 'getReadyOrders'])->name('manufacturing.ready-orders');
    Route::get('/api/manufacturing/assembly-preview', [\App\Http\Controllers\ManufacturingRequirementController::class, 'getAssemblyPreview'])->name('manufacturing.assembly-preview');
    Route::get('/api/manufacturing/component-colors', [\App\Http\Controllers\ManufacturingRequirementController::class, 'getComponentColors'])->name('manufacturing.component-colors');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.low-stock');
    Route::get('/reports/stock-report', [ReportController::class, 'stockReport'])->name('reports.stock-report');
    Route::get('/reports/product-movement', [ReportController::class, 'productMovement'])->name('reports.product-movement');
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/gst', [ReportController::class, 'gstReport'])->name('reports.gst');
    Route::get('/reports/non-gst', [ReportController::class, 'nonGstReport'])->name('reports.non-gst');

    // Report Exports
    Route::get('/reports/export/low-stock', [ReportController::class, 'exportLowStock'])->name('reports.export.low-stock');
    Route::get('/reports/export/stock-report', [ReportController::class, 'exportStockReport'])->name('reports.export.stock-report');
    Route::get('/reports/export/product-movement', [ReportController::class, 'exportProductMovement'])->name('reports.export.product-movement');
    Route::get('/reports/export/sales', [ReportController::class, 'exportSalesReport'])->name('reports.export.sales');
    Route::get('/reports/export/gst', [ReportController::class, 'exportGstReport'])->name('reports.export.gst');
    Route::get('/reports/export/non-gst', [ReportController::class, 'exportNonGstReport'])->name('reports.export.non-gst');
});

require __DIR__.'/auth.php';
