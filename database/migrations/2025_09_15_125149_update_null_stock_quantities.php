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
        // Update all null quantity values to 1000 (default stock)
        DB::table('product_color_variants')
            ->whereNull('quantity')
            ->update(['quantity' => 1000]);
            
        echo "Updated null quantities to 1000\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to null if needed
        DB::table('product_color_variants')
            ->where('quantity', 1000)
            ->update(['quantity' => null]);
    }
};
