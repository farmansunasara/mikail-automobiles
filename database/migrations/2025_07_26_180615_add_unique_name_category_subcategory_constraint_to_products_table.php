<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add unique constraint on name, category_id, and subcategory_id combination
            // This allows same product name in different categories/subcategories
            $table->unique(['name', 'category_id', 'subcategory_id'], 'products_name_category_subcategory_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_name_category_subcategory_unique');
        });
    }
};
