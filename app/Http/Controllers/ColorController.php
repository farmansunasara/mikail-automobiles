<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\ColorStockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $query = Color::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'low_stock') {
                $query->lowStock();
            }
        }

        $colors = $query->latest()->paginate(15);

        return view('colors.index', compact('colors'));
    }

    public function create()
    {
        return view('colors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:colors',
            'hex_code' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'stock_grams' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        DB::transaction(function () use ($request) {
            $color = Color::create($request->all());

            // Create initial stock log if stock is provided
            if ($color->stock_grams > 0) {
                ColorStockLog::create([
                    'color_id' => $color->id,
                    'change_type' => 'inward',
                    'quantity_grams' => $color->stock_grams,
                    'previous_stock' => 0,
                    'new_stock' => $color->stock_grams,
                    'remarks' => 'Initial stock during color creation',
                    'reference_type' => 'manual_adjustment'
                ]);
            }
        });

        return redirect()->route('colors.index')->with('success', 'Color created successfully.');
    }

    public function show(Color $color)
    {
        $color->load('stockLogs', 'productColorVariants.product');
        $recentLogs = $color->stockLogs()->latest()->take(10)->get();
        
        return view('colors.show', compact('color', 'recentLogs'));
    }

    public function edit(Color $color)
    {
        return view('colors.edit', compact('color'));
    }

    public function update(Request $request, Color $color)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:colors,name,' . $color->id,
            'hex_code' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'minimum_stock' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        $color->update($request->except('stock_grams')); // Don't allow direct stock updates

        return redirect()->route('colors.index')->with('success', 'Color updated successfully.');
    }

    public function destroy(Color $color)
    {
        if ($color->productColorVariants()->exists()) {
            return redirect()->route('colors.index')
                ->with('error', 'Cannot delete color that is used in products.');
        }

        $color->delete();

        return redirect()->route('colors.index')->with('success', 'Color deleted successfully.');
    }

    // API endpoint for color search (for dropdowns)
    public function search(Request $request)
    {
        $query = Color::active();

        if ($request->filled('term')) {
            $query->search($request->term);
        }

        $colors = $query->take(20)->get(['id', 'name', 'stock_grams', 'hex_code']);

        return response()->json($colors->map(function ($color) {
            return [
                'id' => $color->id,
                'name' => $color->name,
                'display_name' => $color->display_name,
                'stock_grams' => $color->stock_grams,
                'hex_code' => $color->hex_code,
                'has_stock' => $color->stock_grams > 0
            ];
        }));
    }

    // Update color stock
    public function updateStock(Request $request, Color $color)
    {
        $request->validate([
            'change_type' => 'required|in:inward,outward',
            'quantity_grams' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($request, $color) {
            $previousStock = $color->stock_grams;
            $quantity = $request->quantity_grams;
            
            if ($request->change_type === 'inward') {
                $newStock = $previousStock + $quantity;
            } else {
                if ($previousStock < $quantity) {
                    throw new \Exception("Insufficient stock. Available: {$previousStock}g, Required: {$quantity}g");
                }
                $newStock = $previousStock - $quantity;
            }

            $color->update(['stock_grams' => $newStock]);

            ColorStockLog::create([
                'color_id' => $color->id,
                'change_type' => $request->change_type,
                'quantity_grams' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'remarks' => $request->remarks,
                'reference_type' => 'manual_adjustment'
            ]);
        });

        return redirect()->back()->with('success', 'Color stock updated successfully.');
    }

    // Get colors with low stock
    public function lowStock()
    {
        $colors = Color::active()->lowStock()->with('stockLogs')->get();
        
        return view('colors.low_stock', compact('colors'));
    }
}
