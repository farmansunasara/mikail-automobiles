<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $categories = $query->latest()->paginate(10);
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

        $category = Category::create($request->only('name', 'description'));

        if ($request->has('subcategories')) {
            foreach ($request->subcategories as $subName) {
                if (!empty($subName)) {
                    $category->subcategories()->create(['name' => $subName]);
                }
            }
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
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

        $category->update($request->only('name', 'description'));

        // Sync subcategories
        $existingSubIds = $category->subcategories->pluck('id')->all();
        $newSubNames = $request->input('subcategories', []);
        
        // Delete removed subcategories
        Subcategory::destroy(array_diff($existingSubIds, array_keys($newSubNames)));

        // Update existing or create new subcategories
        foreach ($newSubNames as $id => $name) {
            if (!empty($name)) {
                $category->subcategories()->updateOrCreate(['id' => $id], ['name' => $name]);
            }
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->products()->exists() || $category->subcategories()->exists()) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete category with associated products or subcategories.');
        }
        
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Get subcategories for a given category.
     */
    public function getSubcategories(Category $category)
    {
        return response()->json($category->subcategories);
    }
}
