<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL/MariaDB: redefine the enum to include the new value
        DB::statement("
            ALTER TABLE `procurement_requests`
            MODIFY `payment_terms`
            ENUM('advance','net_30','net_45','net_50','on_delivery')
            NULL
        ");
    }

    public function down(): void
    {
        // Revert to the previous enum set (without on_delivery)
        // NOTE: make sure no rows still contain 'on_delivery' before running down()
        DB::statement("
            ALTER TABLE `procurement_requests`
            MODIFY `payment_terms`
            ENUM('advance','net_30','net_45','net_50')
            NULL
        ");
    }
};
