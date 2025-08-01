<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Models\StockLog;
use Illuminate\Http\Request;
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

        $products = $query->latest()->paginate(15)->appends($request->query());
        
        // Get unique colors for filter dropdown from color variants
        $colors = ProductColorVariant::distinct()
            ->pluck('color')
            ->sort();

        return view('stock.index_color_variants', compact('products', 'colors'));
    }

    /**
     * Update stock for a product color variant.
     */
    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'color_variant_id' => 'required|exists:product_color_variants,id',
            'change_type' => 'required|in:inward,outward',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $colorVariant = ProductColorVariant::findOrFail($request->color_variant_id);
            
            // Verify the color variant belongs to the product
            if ($colorVariant->product_id !== $product->id) {
                throw new \Exception('Color variant does not belong to the selected product.');
            }
            
            if ($request->change_type === 'inward') {
                $this->stockService->inwardColorVariantStock($colorVariant, $request->quantity, $request->notes);
            } else {
                $this->stockService->outwardColorVariantStock($colorVariant, $request->quantity, $request->notes);
            }

            return redirect()->route('stock.index')->with('success', 'Stock updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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

        $logs = $query->latest()->paginate(20);
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
