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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('discount_type', 8, 2)->default(0)->after('total_amount')->comment('0 = fixed amount, 1 = percentage');
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type')->comment('Discount amount or percentage value');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_value')->comment('Calculated discount amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'discount_amount']);
        });
    }
};
