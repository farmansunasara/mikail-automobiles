<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ProductComponent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category', 'subcategory');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }
        if ($request->filled('color')) {
            $query->where('color', 'like', '%' . $request->color . '%');
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::all();
        
        // Get unique colors for filter dropdown
        $colors = Product::whereNotNull('color')
            ->where('color', '!=', '')
            ->distinct()
            ->pluck('color')
            ->sort();

        return view('products.index', compact('products', 'categories', 'colors'));
    }

    /**
     * Get products filtered by category for invoice creation
     */
    public function getProductsByCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $products = Product::where('category_id', $request->category_id)
            ->orderBy('name')
            ->get(['id', 'name', 'is_composite']);

        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $products = Product::where('is_composite', false)->get();
        return view('products.create', compact('categories', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'color' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'hsn_code' => 'nullable|string|max:50',
            'gst_rate' => 'required|numeric|min:0',
            'is_composite' => 'boolean',
            'components' => 'nullable|array',
            'components.*.component_product_id' => 'required_if:is_composite,1|exists:products,id',
            'components.*.quantity_needed' => 'required_if:is_composite,1|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $productData = $request->except('components');
            
            // Handle empty HSN code
            if (empty($productData['hsn_code'])) {
                $productData['hsn_code'] = null;
            }
            
            $product = Product::create($productData);

            if ($request->boolean('is_composite') && $request->has('components')) {
                foreach ($request->components as $componentData) {
                    $product->components()->create($componentData);
                }
            }
        });

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('category', 'subcategory', 'components.componentProduct');
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        $subcategories = $product->category ? $product->category->subcategories : [];
        $simpleProducts = Product::where('is_composite', false)->where('id', '!=', $product->id)->get();
        $product->load('components');
        
        return view('products.edit', compact('product', 'categories', 'subcategories', 'simpleProducts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => ['required','string','max:255',Rule::unique('products')->ignore($product->id)],
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'color' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'hsn_code' => 'nullable|string|max:50',
            'gst_rate' => 'required|numeric|min:0',
            'is_composite' => 'boolean',
            'components' => 'nullable|array',
            'components.*.component_product_id' => 'required_if:is_composite,1|exists:products,id',
            'components.*.quantity_needed' => 'required_if:is_composite,1|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $product) {
            $productData = $request->except('components');
            
            // Handle empty HSN code
            if (empty($productData['hsn_code'])) {
                $productData['hsn_code'] = null;
            }
            
            $product->update($productData);

            $product->components()->delete();
            if ($request->boolean('is_composite') && $request->has('components')) {
                foreach ($request->components as $componentData) {
                    $product->components()->create($componentData);
                }
            }
        });

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->invoiceItems()->exists()) {
            return redirect()->route('products.index')->with('error', 'Cannot delete product with associated invoices.');
        }

        DB::transaction(function () use ($product) {
            $product->components()->delete();
            $product->delete();
        });

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Search for products via API.
     */
    public function search(Request $request)
    {
        $query = Product::query();
        if ($request->filled('term')) {
            $query->where('name', 'like', '%' . $request->term . '%');
        }
        $products = $query->take(15)->get(['id', 'name', 'price', 'quantity', 'hsn_code', 'gst_rate']);
        return response()->json($products);
    }
    
    /**
     * Get components for a composite product.
     */
    public function getComponents(Product $product)
    {
        if (!$product->is_composite) {
            return response()->json(['message' => 'This product is not a composite product.'], 422);
        }

        $components = $product->components()->with('componentProduct:id,name')->get();
        return response()->json($components);
    }

    /**
     * Get product stock information for real-time validation
     */
    public function getStock(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'quantity' => $product->quantity,
            'color' => $product->color,
            'price' => $product->price,
            'gst_rate' => $product->gst_rate,
            'available' => $product->quantity > 0,
            'low_stock' => $product->quantity <= 10, // Consider low stock threshold
        ]);
    }

    /**
     * Get all color variants for a product name
     */
    public function getProductVariants($identifier)
    {
        // Check if identifier is numeric (product ID) or string (product name)
        if (is_numeric($identifier)) {
            // Get by product ID - return all variants of the same product name
            $product = Product::find($identifier);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }
            $productName = $product->name;
        } else {
            // Get by product name
            $productName = $identifier;
        }

        $variants = Product::where('name', $productName)
            ->with(['category', 'subcategory', 'components.componentProduct'])
            ->orderBy('category_id')
            ->orderBy('color')
            ->get(['id', 'name', 'color', 'quantity', 'price', 'gst_rate', 'hsn_code', 'category_id', 'subcategory_id', 'is_composite']);

        // Group variants by category
        $groupedVariants = $variants->groupBy('category.name');

        return response()->json([
            'product_name' => $productName,
            'variants' => $variants,
            'grouped_variants' => $groupedVariants,
            'has_variants' => $variants->count() > 1,
            'has_multiple_categories' => $groupedVariants->count() > 1,
            'total_stock' => $variants->sum('quantity')
        ]);
    }
}
