<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('colors', function (Blueprint $table) {
            // Drop the unique index on name if it exists
            try {
                $table->dropUnique('colors_name_unique');
            } catch (Throwable $e) {
                // Index might not exist (already dropped); ignore
            }
        });
    }

    public function down(): void
    {
        Schema::table('colors', function (Blueprint $table) {
            // Recreate the unique index on name on rollback
            $table->unique('name');
        });
    }
};
