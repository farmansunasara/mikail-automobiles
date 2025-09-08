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
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

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

        $products = $query->latest()->paginate(10)->appends($request->query());
        $categories = Category::all();
        
        $colors = \App\Models\ProductColorVariant::distinct()
            ->pluck('color')
            ->sort();

        return view('products.index', compact('products', 'categories', 'colors'));
    }

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

    public function getProductsByCategoryForComponents(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $products = Product::where('category_id', $request->category_id)
            ->where('is_composite', false)
            ->with('colorVariants')
            ->orderBy('name')
            ->get(['id', 'name', 'is_composite']);

        // Add total stock from color variants
        $products = $products->map(function ($product) {
            $product->total_stock = $product->colorVariants->sum('quantity');
            return $product;
        });

        return response()->json($products);
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create_with_color_variants', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            'minimum_threshold' => 'required|integer|min:0',
            'is_composite' => 'boolean',
            'components' => 'nullable|array',
            'components.*.component_product_id' => 'required_if:is_composite,1|exists:products,id',
            'components.*.quantity_needed' => 'required_if:is_composite,1|integer|min:1',
            'default_quantity' => 'nullable|integer|min:0',
            'color_variants' => [
                'array',
                function ($attribute, $value, $fail) use ($request) {
                    if (empty($value) && (empty($request->default_quantity) || $request->default_quantity == 0)) {
                        $fail('You must provide either a default quantity or at least one color variant.');
                    }
                    if (!empty($value)) {
                        foreach ($value as $variant) {
                            if (empty($variant['quantity']) || $variant['quantity'] <= 0) {
                                $fail('All color variant quantities must be greater than 0.');
                            }
                        }
                        $colors = array_column($value, 'color');
                        $uniqueColors = array_unique(array_map('strtolower', array_filter($colors)));
                        if (count($colors) !== count($uniqueColors)) {
                            $fail('Duplicate color variants are not allowed for the same product.');
                        }
                    }
                },
            ],
            'color_variants.*.color' => 'nullable|string|max:100',
            'color_variants.*.quantity' => 'required|integer|min:1',
            'color_variants.*.color_id' => 'nullable|exists:colors,id',
            'color_variants.*.color_usage_grams' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {
            $productData = $request->except(['components', 'color_variants', 'default_quantity']);
            $productData['minimum_threshold'] = $request->input('minimum_threshold', 0);
            
            if (empty($productData['hsn_code'])) {
                $productData['hsn_code'] = null;
            }
            
            $productData['color'] = null;
            $productData['quantity'] = 0;
            
            $product = Product::create($productData);

            if ($request->boolean('is_composite') && $request->has('components')) {
                foreach ($request->components as $componentData) {
                    $product->components()->create($componentData);
                }
            }

            $colorVariants = $request->color_variants ?: [];
            if (empty($colorVariants) && $request->filled('default_quantity') && $request->default_quantity > 0) {
                $colorVariants[] = [
                    'color' => 'No Color',
                    'quantity' => $request->default_quantity,
                ];
            }

            foreach ($colorVariants as $variant) {
                if ($variant['quantity'] > 0) {
                    $color = !empty($variant['color']) ? $variant['color'] : 'No Color';
                    
                    $colorVariant = $product->colorVariants()->create([
                        'color' => $color,
                        'quantity' => 0,
                        'color_id' => $variant['color_id'] ?? null,
                        'color_usage_grams' => $variant['color_usage_grams'] ?? 0
                    ]);
                    
                    try {
                        $this->stockService->inwardColorVariantStock(
                            $colorVariant, 
                            $variant['quantity'], 
                            'Initial stock during product creation'
                        );
                    } catch (\Exception $e) {
                        $product->delete();
                        throw new \Exception("Cannot create composite product: " . $e->getMessage());
                    }
                }
            }
        });

        if ($request->ajax()) {
            return response()->json(['success' => 'Product created successfully.', 'redirect' => route('products.index')]);
        }
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'subcategory', 'components.componentProduct', 'colorVariants');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $subcategories = $product->category ? $product->category->subcategories : [];
        $simpleProducts = Product::where('is_composite', false)->where('id', '!=', $product->id)->get();
        $product->load('components');
        
        return view('products.edit', compact('product', 'categories', 'subcategories', 'simpleProducts'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
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
            'minimum_threshold' => 'required|integer|min:0',
            'is_composite' => 'boolean',
            'components' => 'nullable|array',
            'components.*.component_product_id' => 'required_if:is_composite,1|exists:products,id',
            'components.*.quantity_needed' => 'required_if:is_composite,1|integer|min:1',
            'color_variants' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    foreach ($value as $variant) {
                        if (empty($variant['quantity']) || $variant['quantity'] <= 0) {
                            $fail('All color variant quantities must be greater than 0.');
                        }
                    }
                    $colors = array_column($value, 'color');
                    $uniqueColors = array_unique(array_map('strtolower', array_filter($colors)));
                    if (count($colors) !== count($uniqueColors)) {
                        $fail('Duplicate color variants are not allowed for the same product.');
                    }
                },
            ],
            'color_variants.*.color' => 'nullable|string|max:100',
            'color_variants.*.quantity' => 'required|integer|min:1',
            'color_variants.*.color_id' => 'nullable|exists:colors,id',
            'color_variants.*.color_usage_grams' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $product) {
            $productData = $request->except(['components', 'color_variants']);
            $productData['minimum_threshold'] = $request->input('minimum_threshold', 0);
            
            if (empty($productData['hsn_code'])) {
                $productData['hsn_code'] = null;
            }
            
            $productData['color'] = null;
            $productData['quantity'] = 0;
            
            $originalData = $product->toArray();
            $product->update($productData);

            try {
                // Update components (only structure, not stock)
                $product->components()->delete();
                if ($request->boolean('is_composite') && $request->has('components')) {
                    foreach ($request->components as $componentData) {
                        $product->components()->create($componentData);
                    }
                }

                // Update color variants without re-deducting stock
                if ($request->has('color_variants')) {
                    // Store existing variants for comparison
                    $existingVariants = $product->colorVariants()->get()->keyBy('color');
                    $newVariants = collect($request->color_variants);
                    
                    // Delete variants that are no longer needed
                    foreach ($existingVariants as $existingVariant) {
                        $found = $newVariants->firstWhere('color', $existingVariant->color);
                        if (!$found) {
                            $existingVariant->delete();
                        }
                    }
                    
                    // Update or create variants
                    foreach ($request->color_variants as $variant) {
                        if ($variant['quantity'] > 0) {
                            $color = !empty($variant['color']) ? $variant['color'] : 'No Color';
                            $existingVariant = $existingVariants->get($color);
                            
                            if ($existingVariant) {
                                // Update existing variant (only metadata, not stock)
                                $existingVariant->update([
                                    'color_id' => $variant['color_id'] ?? null,
                                    'color_usage_grams' => $variant['color_usage_grams'] ?? 0
                                ]);
                                
                                // Only adjust stock if quantity changed
                                $quantityDiff = $variant['quantity'] - $existingVariant->quantity;
                                if ($quantityDiff != 0) {
                                    if ($quantityDiff > 0) {
                                        // Increase stock - this will deduct components/colors
                                        $this->stockService->inwardColorVariantStock(
                                            $existingVariant,
                                            $quantityDiff,
                                            'Stock increase during product edit'
                                        );
                                    } else {
                                        // Decrease stock - this will return components/colors
                                        $this->stockService->outwardColorVariantStockSaleOnly(
                                            $existingVariant,
                                            abs($quantityDiff),
                                            'Stock decrease during product edit'
                                        );
                                    }
                                }
                            } else {
                                // Create new variant - this will deduct components/colors
                                $colorVariant = $product->colorVariants()->create([
                                    'color' => $color,
                                    'quantity' => 0,
                                    'color_id' => $variant['color_id'] ?? null,
                                    'color_usage_grams' => $variant['color_usage_grams'] ?? 0
                                ]);
                                $this->stockService->inwardColorVariantStock(
                                    $colorVariant,
                                    $variant['quantity'],
                                    'New variant added during product edit'
                                );
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $product->update($originalData);
                throw new \Exception("Cannot update product: " . $e->getMessage());
            }
        });

        if ($request->ajax()) {
            return response()->json(['success' => 'Product updated successfully.', 'redirect' => route('products.index')]);
        }
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

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

    public function search(Request $request)
    {
        $query = Product::query();
        if ($request->filled('term')) {
            $query->where('name', 'like', '%' . $request->term . '%');
        }
        $products = $query->take(15)->get(['id', 'name', 'price', 'quantity', 'gst_rate']);
        return response()->json($products);
    }
    
    public function getComponents(Product $product)
    {
        if (!$product->is_composite) {
            return response()->json(['message' => 'This product is not a composite product.'], 422);
        }

        $components = $product->components()->with('componentProduct:id,name')->get();
        return response()->json($components);
    }

    public function getStock(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'quantity' => $product->colorVariants->sum('quantity'),
            'color_variants' => $product->colorVariants->map(function ($variant) {
                return ['color' => $variant->color, 'quantity' => $variant->quantity];
            }),
            'price' => $product->price,
            'gst_rate' => $product->gst_rate,
            'available' => $product->colorVariants->sum('quantity') > 0,
            'low_stock' => $product->colorVariants->sum('quantity') <= 10,
        ]);
    }

    public function getProductVariants($identifier)
    {
        if (is_numeric($identifier)) {
            $product = Product::find($identifier);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }
            
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
                
                if ($product->is_composite) {
                    $components = $product->components()->with('componentProduct.colorVariants')->get();
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
                    
                    $maxAssembly = PHP_INT_MAX;
                    foreach ($components as $component) {
                        $componentTotalStock = $component->componentProduct->colorVariants->sum('quantity');
                        $possibleAssembly = floor($componentTotalStock / $component->quantity_needed);
                        $maxAssembly = min($maxAssembly, $possibleAssembly);
                    }
                    
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