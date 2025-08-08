<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('color_material_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('color_material_id');
            $table->enum('type', ['deduction', 'addition', 'adjustment']);
            $table->decimal('quantity_grams', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference_type')->nullable(); // e.g., invoice, manual_adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('color_material_id')->references('id')->on('color_materials')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['color_material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('color_material_logs');
    }
};
