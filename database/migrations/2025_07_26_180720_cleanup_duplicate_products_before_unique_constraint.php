<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find and merge duplicate products by consolidating their color variants
        $duplicates = DB::select("
            SELECT name, category_id, subcategory_id, COUNT(*) as count 
            FROM products 
            GROUP BY name, category_id, subcategory_id 
            HAVING COUNT(*) > 1
        ");

        foreach ($duplicates as $duplicate) {
            // Get all products with the same name, category, and subcategory
            $products = DB::table('products')
                ->where('name', $duplicate->name)
                ->where('category_id', $duplicate->category_id)
                ->where('subcategory_id', $duplicate->subcategory_id)
                ->orderBy('id')
                ->get();

            if ($products->count() > 1) {
                // Keep the first product as the main one
                $mainProduct = $products->first();
                $duplicateProducts = $products->skip(1);

                foreach ($duplicateProducts as $dupProduct) {
                    // Move color variants from duplicate to main product
                    if ($dupProduct->color) {
                        // Check if color variant already exists for main product
                        $existingVariant = DB::table('product_color_variants')
                            ->where('product_id', $mainProduct->id)
                            ->where('color', $dupProduct->color)
                            ->first();

                        if ($existingVariant) {
                            // Update quantity by adding the duplicate's quantity
                            DB::table('product_color_variants')
                                ->where('id', $existingVariant->id)
                                ->update([
                                    'quantity' => $existingVariant->quantity + $dupProduct->quantity
                                ]);
                        } else {
                            // Create new color variant
                            DB::table('product_color_variants')->insert([
                                'product_id' => $mainProduct->id,
                                'color' => $dupProduct->color,
                                'quantity' => $dupProduct->quantity,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }

                    // Update any invoice items that reference the duplicate product
                    DB::table('invoice_items')
                        ->where('product_id', $dupProduct->id)
                        ->update(['product_id' => $mainProduct->id]);

                    // Update any stock logs that reference the duplicate product
                    DB::table('stock_logs')
                        ->where('product_id', $dupProduct->id)
                        ->update(['product_id' => $mainProduct->id]);

                    // Delete the duplicate product
                    DB::table('products')->where('id', $dupProduct->id)->delete();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed
        // as we've consolidated data from multiple products
    }
};
