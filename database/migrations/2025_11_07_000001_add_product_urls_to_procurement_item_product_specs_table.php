<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('procurement_product_specs', function (Blueprint $t) {
            $t->json('product_urls')->nullable()->after('technical_specs');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_product_specs', function (Blueprint $t) {
            $t->dropColumn('product_urls');
        });
    }
};
