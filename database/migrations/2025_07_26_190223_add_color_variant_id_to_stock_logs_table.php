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
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('color_variant_id')->nullable()->after('product_id');
            $table->foreign('color_variant_id')->references('id')->on('product_color_variants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropForeign(['color_variant_id']);
            $table->dropColumn('color_variant_id');
        });
    }
};
