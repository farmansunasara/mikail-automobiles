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
        Schema::create('color_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('color_id')->constrained()->onDelete('cascade');
            $table->enum('change_type', ['inward', 'outward']);
            $table->decimal('quantity_grams', 10, 2); // Quantity in grams
            $table->decimal('previous_stock', 10, 2); // Previous stock before change
            $table->decimal('new_stock', 10, 2); // New stock after change
            $table->text('remarks')->nullable();
            $table->string('reference_type')->nullable(); // e.g., 'product_creation', 'manual_adjustment'
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related record
            $table->timestamps();
            
            $table->index(['color_id', 'change_type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('color_stock_logs');
    }
};
