<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $table = 'vendor_categories';

        $indexExists = function (string $name) use ($table): bool {
            $db = DB::getDatabaseName();
            $rows = DB::select(
                'SELECT 1 FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$db, $table, $name]
            );
            return !empty($rows);
        };

        // Drop unique(slug) if it exists
        if ($indexExists('vendor_categories_slug_unique')) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique('vendor_categories_slug_unique');
            });
        }

        // Ensure unique(kind, slug)
        if (! $indexExists('vendor_categories_kind_slug_unique')) {
            Schema::table($table, function (Blueprint $t) {
                $t->unique(['kind', 'slug'], 'vendor_categories_kind_slug_unique');
            });
        }
    }

    public function down(): void
    {
        $table = 'vendor_categories';

        // Drop composite unique if present
        try {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique('vendor_categories_kind_slug_unique');
            });
        } catch (\Throwable $e) {}

        // (Optional) restore unique(slug)
        try {
            Schema::table($table, function (Blueprint $t) {
                $t->unique('slug', 'vendor_categories_slug_unique');
            });
        } catch (\Throwable $e) {}
    }
};
