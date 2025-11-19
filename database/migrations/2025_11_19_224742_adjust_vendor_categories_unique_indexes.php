<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexExists = function (string $name): bool {
            $db = DB::getDatabaseName();
            $rows = DB::select(
                'SELECT 1 FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$db, 'vendor_categories', $name]
            );
            return !empty($rows);
        };

        // Drop legacy unique(slug) if present
        if ($indexExists('vendor_categories_slug_unique')) {
            Schema::table('vendor_categories', function (Blueprint $t) {
                $t->dropUnique('vendor_categories_slug_unique');
            });
        }

        // Add composite unique(kind, slug)
        if (! $indexExists('vendor_categories_kind_slug_unique')) {
            Schema::table('vendor_categories', function (Blueprint $t) {
                $t->unique(['kind','slug'], 'vendor_categories_kind_slug_unique');
            });
        }

        // Optional: also keep name unique within kind
        if (! $indexExists('vendor_categories_kind_name_unique')) {
            Schema::table('vendor_categories', function (Blueprint $t) {
                $t->unique(['kind','name'], 'vendor_categories_kind_name_unique');
            });
        }
    }

    public function down(): void
    {
        // Drop the composite uniques
        try {
            Schema::table('vendor_categories', function (Blueprint $t) {
                $t->dropUnique('vendor_categories_kind_slug_unique');
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('vendor_categories', function (Blueprint $t) {
                $t->dropUnique('vendor_categories_kind_name_unique');
            });
        } catch (\Throwable $e) {}

        // (Optional) restore legacy unique(slug)
        try {
            Schema::table('vendor_categories', function (Blueprint $t) {
                $t->unique('slug', 'vendor_categories_slug_unique');
            });
        } catch (\Throwable $e) {}
    }
};
