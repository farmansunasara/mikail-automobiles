<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ProductComponent;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'colorVariants']);

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
            $query->whereHas('colorVariants', function($q) use ($request) {
                $q->where('color', 'like', '%' . $request->color . '%');
            });
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::all();
        
        // Get unique colors for filter dropdown from color variants
        $colors = \App\Models\ProductColorVariant::distinct()
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
     * Get products by category for composite product components
     */
    public function getProductsByCategoryForComponents(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        // Only return simple (non-composite) products for use as components
        $products = Product::where('category_id', $request->category_id)
            ->where('is_composite', false)
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
        return view('products.create_with_color_variants', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('name', $request->name)
                                ->where('category_id', $request->category_id)
                                ->where('subcategory_id', $request->subcategory_id);
                })
            ],
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'price' => 'required|numeric|min:0',
            'hsn_code' => 'nullable|string|max:50',
            'gst_rate' => 'required|numeric|min:0',
            'is_composite' => 'boolean',
            'components' => 'nullable|array',
            'components.*.component_product_id' => 'required_if:is_composite,1|exists:products,id',
            'components.*.quantity_needed' => 'required_if:is_composite,1|integer|min:1',
            'color_variants' => 'required|array|min:1',
            'color_variants.*.color' => 'nullable|string|max:100',
            'color_variants.*.quantity' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $productData = $request->except(['components', 'color_variants']);
            
            // Handle empty HSN code
            if (empty($productData['hsn_code'])) {
                $productData['hsn_code'] = null;
            }
            
            // Set default values for legacy fields
            $productData['color'] = null;
            $productData['quantity'] = 0;
            
            $product = Product::create($productData);

            // Handle composite product components first
            if ($request->boolean('is_composite') && $request->has('components')) {
                foreach ($request->components as $componentData) {
                    $product->components()->create($componentData);
                }
            }

            // Create color variants and handle stock for composite products
            if ($request->has('color_variants')) {
                foreach ($request->color_variants as $variant) {
                    if ($variant['quantity'] >= 0) {
                        // Handle empty color - set to "No Color" if empty
                        $color = !empty($variant['color']) ? $variant['color'] : 'No Color';
                        
                        // Create color variant with zero quantity first
                        $colorVariant = $product->colorVariants()->create([
                            'color' => $color,
                            'quantity' => 0
                        ]);
                        
                        // If quantity > 0, use StockService to add stock
                        if ($variant['quantity'] > 0) {
                            try {
                                $this->stockService->inwardColorVariantStock(
                                    $colorVariant, 
                                    $variant['quantity'], 
                                    'Initial stock during product creation'
                                );
                            } catch (\Exception $e) {
                                // If assembly fails, delete the product and throw error
                                $product->delete();
                                throw new \Exception("Cannot create composite product: " . $e->getMessage());
                            }
                        }
                    }
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
        $product->load('category', 'subcategory', 'components.componentProduct', 'colorVariants');
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('name', $request->name)
                                ->where('category_id', $request->category_id)
                                ->where('subcategory_id', $request->subcategory_id);
                })->ignore($product->id)
            ],
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'price' => 'required|numeric|min:0',
            'hsn_code' => 'nullable|string|max:50',
            'gst_rate' => 'required|numeric|min:0',
            'is_composite' => 'boolean',
            'components' => 'nullable|array',
            'components.*.component_product_id' => 'required_if:is_composite,1|exists:products,id',
            'components.*.quantity_needed' => 'required_if:is_composite,1|integer|min:1',
            'color_variants' => 'required|array|min:1',
            'color_variants.*.color' => 'nullable|string|max:100',
            'color_variants.*.quantity' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $product) {
            $productData = $request->except(['components', 'color_variants']);
            
            // Handle empty HSN code
            if (empty($productData['hsn_code'])) {
                $productData['hsn_code'] = null;
            }
            
            // Set default values for legacy fields
            $productData['color'] = null;
            $productData['quantity'] = 0;
            
            $product->update($productData);

            // Update components first
            $product->components()->delete();
            if ($request->boolean('is_composite') && $request->has('components')) {
                foreach ($request->components as $componentData) {
                    $product->components()->create($componentData);
                }
            }

            // Update color variants - for composite products, use StockService
            $product->colorVariants()->delete();
            if ($request->has('color_variants')) {
                foreach ($request->color_variants as $variant) {
                    if ($variant['quantity'] >= 0) {
                        // Handle empty color - set to "No Color" if empty
                        $color = !empty($variant['color']) ? $variant['color'] : 'No Color';
                        
                        // Create color variant with zero quantity first
                        $colorVariant = $product->colorVariants()->create([
                            'color' => $color,
                            'quantity' => 0
                        ]);
                        
                        // If quantity > 0, use StockService to add stock
                        if ($variant['quantity'] > 0) {
                            try {
                                $this->stockService->inwardColorVariantStock(
                                    $colorVariant, 
                                    $variant['quantity'], 
                                    'Stock update during product edit'
                                );
                            } catch (\Exception $e) {
                                throw new \Exception("Cannot update composite product: " . $e->getMessage());
                            }
                        }
                    }
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
            // Get by product ID - return all color variants of this product
            $product = Product::find($identifier);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }
            
            // Get color variants for this specific product
            $colorVariants = $product->colorVariants()
                ->orderBy('color')
                ->get();
            
            $variants = [];
            foreach ($colorVariants as $variant) {
                $variantData = [
                    'id' => $variant->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'color' => $variant->color,
                    'quantity' => $variant->quantity,
                    'price' => $product->price,
                    'gst_rate' => $product->gst_rate,
                    'hsn_code' => $product->hsn_code,
                    'category_id' => $product->category_id,
                    'subcategory_id' => $product->subcategory_id,
                    'is_composite' => $product->is_composite,
                    'category' => $product->category,
                    'subcategory' => $product->subcategory,
                ];
                
                // For composite products, add component information and calculate available quantity
                if ($product->is_composite) {
                    $components = $product->components()->with('componentProduct.colorVariants')->get();
                    $variantData['components'] = $components->map(function($component) {
                        return [
                            'id' => $component->id,
                            'quantity_needed' => $component->quantity_needed,
                            'component_product' => [
                                'id' => $component->componentProduct->id,
                                'name' => $component->componentProduct->name,
                                'quantity' => $component->componentProduct->colorVariants->sum('quantity'), // Total stock across all colors
                            ]
                        ];
                    });
                    
                    // Calculate how many composite products can be made based on component availability
                    $maxAssembly = PHP_INT_MAX;
                    foreach ($components as $component) {
                        $componentTotalStock = $component->componentProduct->colorVariants->sum('quantity');
                        $possibleAssembly = floor($componentTotalStock / $component->quantity_needed);
                        $maxAssembly = min($maxAssembly, $possibleAssembly);
                    }
                    
                    // For composite products, show the minimum of stored quantity and what can be assembled
                    $variantData['quantity'] = min($variant->quantity, $maxAssembly === PHP_INT_MAX ? 0 : $maxAssembly);
                }
                
                $variants[] = $variantData;
            }
            
            return response()->json([
                'product_name' => $product->name,
                'variants' => $variants,
                'has_variants' => count($variants) > 1,
                'total_stock' => collect($variants)->sum('quantity')
            ]);
            
        } else {
            // Get by product name - return all products with this name across categories
            $products = Product::where('name', $identifier)
                ->with(['category', 'subcategory', 'colorVariants', 'components.componentProduct.colorVariants'])
                ->orderBy('category_id')
                ->get();

            $allVariants = [];
            foreach ($products as $product) {
                foreach ($product->colorVariants as $variant) {
                    $variantData = [
                        'id' => $variant->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'color' => $variant->color,
                        'quantity' => $variant->quantity,
                        'price' => $product->price,
                        'gst_rate' => $product->gst_rate,
                        'hsn_code' => $product->hsn_code,
                        'category_id' => $product->category_id,
                        'subcategory_id' => $product->subcategory_id,
                        'is_composite' => $product->is_composite,
                        'category' => $product->category,
                        'subcategory' => $product->subcategory,
                    ];
                    
                    // For composite products, add component information and calculate available quantity
                    if ($product->is_composite) {
                        $components = $product->components;
                        $variantData['components'] = $components->map(function($component) {
                            return [
                                'id' => $component->id,
                                'quantity_needed' => $component->quantity_needed,
                                'component_product' => [
                                    'id' => $component->componentProduct->id,
                                    'name' => $component->componentProduct->name,
                                    'quantity' => $component->componentProduct->colorVariants->sum('quantity'),
                                ]
                            ];
                        });
                        
                        // Calculate how many composite products can be made
                        $maxAssembly = PHP_INT_MAX;
                        foreach ($components as $component) {
                            $componentTotalStock = $component->componentProduct->colorVariants->sum('quantity');
                            $possibleAssembly = floor($componentTotalStock / $component->quantity_needed);
                            $maxAssembly = min($maxAssembly, $possibleAssembly);
                        }
                        
                        $variantData['quantity'] = min($variant->quantity, $maxAssembly === PHP_INT_MAX ? 0 : $maxAssembly);
                    }
                    
                    $allVariants[] = $variantData;
                }
            }

            // Group variants by category
            $groupedVariants = collect($allVariants)->groupBy('category.name');

            return response()->json([
                'product_name' => $identifier,
                'variants' => $allVariants,
                'grouped_variants' => $groupedVariants,
                'has_variants' => count($allVariants) > 1,
                'has_multiple_categories' => $groupedVariants->count() > 1,
                'total_stock' => collect($allVariants)->sum('quantity')
            ]);
        }
    }
}
