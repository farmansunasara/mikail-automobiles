<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_color_material_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('color_variant_id')->nullable();
            $table->unsignedBigInteger('color_material_id');
            $table->decimal('quantity_grams', 12, 2); // amount of this material used per product/color variant unit
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('color_variant_id')->references('id')->on('product_color_variants')->onDelete('cascade');
            $table->foreign('color_material_id')->references('id')->on('color_materials')->onDelete('cascade');

            $table->unique(['product_id', 'color_variant_id', 'color_material_id'], 'uniq_prod_color_material');
            $table->index(['color_material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_color_material_usage');
    }
};
