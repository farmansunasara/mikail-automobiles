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
        Schema::table('product_color_variants', function (Blueprint $table) {
            $table->foreignId('color_id')->nullable()->after('color')->constrained()->onDelete('set null');
            $table->decimal('color_usage_grams', 8, 2)->default(0)->after('color_id');
            
            $table->index(['color_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_color_variants', function (Blueprint $table) {
            $table->dropForeign(['color_id']);
            $table->dropColumn(['color_id', 'color_usage_grams']);
        });
    }
};
