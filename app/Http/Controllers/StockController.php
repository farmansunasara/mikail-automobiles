<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Models\StockLog;
use Illuminate\Http\Request;
use App\Http\Requests\StockUpdateRequest;
use Illuminate\Support\Facades\DB;
use App\Services\StockService;

class StockController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display stock management page.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'colorVariants']);

        // Search by product name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by color
        if ($request->filled('color')) {
            $query->whereHas('colorVariants', function($q) use ($request) {
                $q->where('color', 'like', '%' . $request->color . '%');
            });
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'critical':
                    $query->whereHas('colorVariants', function($q) {
                        $q->where('quantity', '<=', 5);
                    });
                    break;
                case 'low':
                    $query->whereHas('colorVariants', function($q) {
                        $q->where('quantity', '<=', 10)->where('quantity', '>', 5);
                    });
                    break;
                case 'medium':
                    $query->whereHas('colorVariants', function($q) {
                        $q->where('quantity', '<=', 20)->where('quantity', '>', 10);
                    });
                    break;
                case 'good':
                    $query->whereHas('colorVariants', function($q) {
                        $q->where('quantity', '>', 20);
                    });
                    break;
            }
        }

        $products = $query->latest()->paginate(10)->appends($request->query());
        
        // Get unique colors for filter dropdown from color variants
        $colors = ProductColorVariant::distinct()
            ->pluck('color')
            ->sort();

        return view('stock.index_color_variants', compact('products', 'colors'));
    }

    /**
     * Update stock for a product color variant.
     */
      public function update(StockUpdateRequest $request)
    {
        \Log::channel('stock')->info('STARTING STOCK UPDATE', [
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'user' => auth()->user()->id ?? null
        ]);

        // StockUpdateRequest already handles validation
        $validated = $request->validated();

        try {
            \Log::channel('stock')->info('VALIDATION PASSED', $validated);

            $product = Product::findOrFail($request->product_id);
            $colorVariant = ProductColorVariant::findOrFail($request->color_variant_id);

            \Log::channel('stock')->info('FETCHED MODELS', [
                'product' => $product->toArray(),
                'variant' => $colorVariant->toArray()
            ]);

            if ($colorVariant->product_id !== $product->id) {
                throw new \Exception('Color variant does not belong to product');
            }

            if ($request->change_type === 'inward') {
                $this->stockService->inwardColorVariantStock($colorVariant, $request->quantity, $request->notes);
            } else {
                // Validate sufficient stock for outward movement
                if ($colorVariant->quantity < $request->quantity) {
                    throw new \Exception("Insufficient stock. Available: {$colorVariant->quantity}, Required: {$request->quantity}");
                }
                $this->stockService->outwardColorVariantStock($colorVariant, $request->quantity, $request->notes);
            }

            \Log::channel('stock')->info('STOCK UPDATE COMPLETED', [
                'product_id' => $product->id,
                'variant_id' => $colorVariant->id,
                'new_quantity' => $colorVariant->fresh()->quantity
            ]);

            $target = $request->input('redirect', url()->previous());
            return redirect(rtrim($target, '#') . '#product-' . $product->id)
                   ->with('success', 'Stock updated successfully.');

        } catch (\Exception $e) {
            \Log::channel('stock')->error('STOCK UPDATE FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                   ->with('error', $e->getMessage());
        }
    }

    /**
     * Display all stock logs.
     */
    public function logs(Request $request)
    {
        $query = StockLog::with(['product.category', 'colorVariant']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('color_variant_id')) {
            $query->where('color_variant_id', $request->color_variant_id);
        }
        if ($request->filled('change_type')) {
            $query->where('change_type', $request->change_type);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('remarks')) {
            $remarks = trim($request->remarks);
            $query->where('remarks', 'like', "%{$remarks}%");
        }

    // Preserve applied filters when navigating pagination links
    $logs = $query->latest()->paginate(20)->appends($request->query());
        $products = Product::orderBy('name')->get();

        return view('stock.logs', compact('logs', 'products'));
    }

    /**
     * Display stock logs for a specific product.
     */
    public function productLogs(Product $product)
    {
        $logs = $product->stockLogs()->with('colorVariant')->latest()->paginate(15);
        return view('stock.product_logs', compact('product', 'logs'));
    }

    /**
     * Get color variants for a product (AJAX endpoint).
     */
    public function getProductColorVariants(Product $product)
    {
        $colorVariants = $product->colorVariants()
            ->orderBy('color')
            ->get(['id', 'color', 'quantity']);

        return response()->json($colorVariants);
    }
}
