<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 0) Helpers
        $indexExists = function (string $indexName): bool {
            $db = DB::getDatabaseName();
            $rows = DB::select(
                'SELECT 1 FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$db, 'vendor_categories', $indexName]
            );
            return !empty($rows);
        };

        // 1) Ensure required columns exist (use NULLable where we need to backfill first)
        Schema::table('vendor_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_categories', 'kind')) {
                $table->enum('kind', ['product','service'])->default('product')->after('id');
            }

            if (! Schema::hasColumn('vendor_categories', 'name')) {
                $table->string('name')->after('kind');
            }

            if (! Schema::hasColumn('vendor_categories', 'slug')) {
                // make nullable first so we can backfill safely before adding unique
                $table->string('slug')->nullable()->after('name');
            }

            // We'll handle display_order vs legacy 'order' just below (1a)
            if (! Schema::hasColumn('vendor_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('slug');
            }

            if (! Schema::hasColumn('vendor_categories', 'description')) {
                $table->text('description')->nullable()->after('is_active');
            }
        });

        // 1a) Migrate legacy `order` -> `display_order` without DBAL
        if (! Schema::hasColumn('vendor_categories', 'display_order')) {
            Schema::table('vendor_categories', function (Blueprint $table) {
                $table->unsignedInteger('display_order')->default(0)->after('slug');
            });

            if (Schema::hasColumn('vendor_categories', 'order')) {
                // copy values then drop old column
                DB::statement('UPDATE vendor_categories SET display_order = COALESCE(`order`, 0)');
                Schema::table('vendor_categories', function (Blueprint $table) {
                    try { $table->dropColumn('order'); } catch (\Throwable $e) {}
                });
            }
        } else {
            // display_order exists; if legacy 'order' still lingers, drop it
            if (Schema::hasColumn('vendor_categories', 'order')) {
                Schema::table('vendor_categories', function (Blueprint $table) {
                    try { $table->dropColumn('order'); } catch (\Throwable $e) {}
                });
            }
        }

        // 2) Backfill/normalize data
        // 2a) Ensure 'kind' is valid
        DB::table('vendor_categories')->whereNull('kind')->update(['kind' => 'product']);
        DB::table('vendor_categories')->whereNotIn('kind', ['product','service'])->update(['kind' => 'product']);

        // 2b) Backfill unique slugs for rows missing / conflicting
        $rows = DB::table('vendor_categories')->select('id','name','slug')->orderBy('id')->get();
        $seen = [];
        foreach ($rows as $r) {
            $slug = $r->slug ? Str::slug($r->slug) : Str::slug((string) $r->name);
            if ($slug === '') {
                $slug = 'category-'.$r->id;
            }
            $base = $slug;
            $i = 1;
            while (
                isset($seen[$slug]) ||
                DB::table('vendor_categories')
                    ->where('slug', $slug)
                    ->where('id', '!=', $r->id)
                    ->exists()
            ) {
                $slug = $base.'-'.(++$i);
            }
            $seen[$slug] = true;

            if ($slug !== $r->slug) {
                DB::table('vendor_categories')->where('id', $r->id)->update(['slug' => $slug]);
            }
        }

        // 3) Drop hierarchical columns if they existed earlier
        Schema::table('vendor_categories', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_categories', 'parent_id')) {
                try { $table->dropForeign(['parent_id']); } catch (\Throwable $e) {}
                try { $table->dropColumn('parent_id'); } catch (\Throwable $e) {}
            }
            foreach (['lft','rgt','depth','path'] as $col) {
                if (Schema::hasColumn('vendor_categories', $col)) {
                    try { $table->dropColumn($col); } catch (\Throwable $e) {}
                }
            }
        });

        // 4) Add unique indexes (guarded to avoid "Duplicate key name")
        Schema::table('vendor_categories', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('vendor_categories_slug_unique')) {
                $table->unique('slug', 'vendor_categories_slug_unique');
            }
            if (! $indexExists('vendor_categories_kind_name_unique')) {
                $table->unique(['kind','name'], 'vendor_categories_kind_name_unique');
            }
        });
    }

    public function down(): void
    {
        // Best-effort rollback of new constraints only (no data loss)
        Schema::table('vendor_categories', function (Blueprint $table) {
            try { $table->dropUnique('vendor_categories_slug_unique'); } catch (\Throwable $e) {}
            try { $table->dropUnique('vendor_categories_kind_name_unique'); } catch (\Throwable $e) {}
        });

        // We intentionally keep columns and do not attempt to recreate legacy hierarchy or 'order' column.
    }
};
