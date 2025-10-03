<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('subcategories', 'products');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->latest()->paginate(10)->appends($request->query());
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $category = Category::create($request->only('name', 'description'));

                if ($request->has('subcategories')) {
                    foreach ($request->subcategories as $subName) {
                        if (!empty($subName)) {
                            $category->subcategories()->create(['name' => $subName]);
                        }
                    }
                }
            });

            return redirect()->route('categories.index')->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load('subcategories', 'products.subcategory', 'products.colorVariants');
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $category->load('subcategories');
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => ['required','string','max:255',Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request, $category) {
                $category->update($request->only('name', 'description'));

                // Sync subcategories with proper validation
                $existingSubIds = $category->subcategories->pluck('id')->all();
                $newSubNames = $request->input('subcategories', []);
                
                // Check if subcategories to be deleted have products
                $subcategoriesToDelete = array_diff($existingSubIds, array_keys($newSubNames));
                foreach ($subcategoriesToDelete as $subId) {
                    $subcategory = Subcategory::find($subId);
                    if ($subcategory && $subcategory->products()->exists()) {
                        throw new \Exception("Cannot delete subcategory '{$subcategory->name}' because it has associated products.");
                    }
                }
                
                // Delete removed subcategories
                if (!empty($subcategoriesToDelete)) {
                    Subcategory::destroy($subcategoriesToDelete);
                }

                // Update existing or create new subcategories
                foreach ($newSubNames as $id => $name) {
                    if (!empty($name)) {
                        $category->subcategories()->updateOrCreate(['id' => $id], ['name' => $name]);
                    }
                }
            });

            return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            // Check for associated products
            if ($category->products()->exists()) {
                $productCount = $category->products()->count();
                return redirect()->route('categories.index')
                    ->with('error', "Cannot delete category '{$category->name}' because it has {$productCount} associated products.");
            }
            
            // Check for subcategories with products
            $subcategoriesWithProducts = $category->subcategories()
                ->whereHas('products')
                ->count();
                
            if ($subcategoriesWithProducts > 0) {
                return redirect()->route('categories.index')
                    ->with('error', "Cannot delete category '{$category->name}' because its subcategories have associated products.");
            }
            
            DB::transaction(function () use ($category) {
                // Delete subcategories first (cascade will handle this, but being explicit)
                $category->subcategories()->delete();
                // Then delete the category
                $category->delete();
            });
            
            return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }

    /**
     * Get subcategories for a given category.
     */
    public function getSubcategories(Category $category)
    {
        return response()->json($category->subcategories);
    }
}
