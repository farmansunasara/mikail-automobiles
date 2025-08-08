<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('color_materials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('unit', 20)->default('grams');
            $table->decimal('stock_grams', 12, 2)->default(0);
            $table->decimal('minimum_stock', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('color_materials');
    }
};
