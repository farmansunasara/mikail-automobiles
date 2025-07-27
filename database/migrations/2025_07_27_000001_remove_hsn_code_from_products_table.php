<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'hsn_code')) {
                $table->dropColumn('hsn_code');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'hsn_code')) {
                $table->string('hsn_code')->nullable();
            }
        });
    }
};
