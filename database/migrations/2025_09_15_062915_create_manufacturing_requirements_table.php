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
        Schema::create('manufacturing_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('mr_number')->unique();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('color_variant_id')->nullable()->constrained('product_color_variants')->onDelete('cascade');
            $table->integer('required_quantity');
            $table->integer('available_quantity')->default(0);
            $table->integer('shortage_quantity');
            $table->date('earliest_delivery_date')->nullable();
            $table->enum('status', ['open', 'in_production', 'completed', 'cancelled'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'earliest_delivery_date']);
            $table->index(['product_id', 'color_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturing_requirements');
    }
};
