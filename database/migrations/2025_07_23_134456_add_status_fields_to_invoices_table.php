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
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled', 'overdue'])
                  ->default('draft')
                  ->after('grand_total');
            $table->date('due_date')->nullable()->after('invoice_date');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('grand_total');
            $table->date('paid_date')->nullable()->after('paid_amount');
            $table->string('payment_method')->nullable()->after('paid_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['status', 'due_date', 'paid_amount', 'paid_date', 'payment_method']);
        });
    }
};
