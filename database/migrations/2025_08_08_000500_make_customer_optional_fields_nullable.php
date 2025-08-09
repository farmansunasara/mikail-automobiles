<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Make mobile, address, state nullable (MySQL syntax)
        DB::statement("ALTER TABLE customers MODIFY mobile VARCHAR(255) NULL");
        DB::statement("ALTER TABLE customers MODIFY address TEXT NULL");
        DB::statement("ALTER TABLE customers MODIFY state VARCHAR(255) NULL");
    }

    public function down(): void
    {
        // Revert to NOT NULL (set empty string for existing nulls first to avoid failure)
        DB::statement("UPDATE customers SET mobile = '' WHERE mobile IS NULL");
        DB::statement("UPDATE customers SET address = '' WHERE address IS NULL");
        DB::statement("UPDATE customers SET state = '' WHERE state IS NULL");
        DB::statement("ALTER TABLE customers MODIFY mobile VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE customers MODIFY address TEXT NOT NULL");
        DB::statement("ALTER TABLE customers MODIFY state VARCHAR(255) NOT NULL");
    }
};
