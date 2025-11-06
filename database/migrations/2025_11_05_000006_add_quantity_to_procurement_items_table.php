<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('procurement_items', 'quantity')) {
            Schema::table('procurement_items', function (Blueprint $table) {
                // keep it general enough; product uses it, services can default to 1
                $table->unsignedInteger('quantity')->default(1)->after('unit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('procurement_items', 'quantity')) {
            Schema::table('procurement_items', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};
