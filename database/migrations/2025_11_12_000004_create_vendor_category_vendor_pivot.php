<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function isBigInt(string $table, string $column = 'id'): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
            [$db, $table, $column]
        );
        return $row && strtolower($row->DATA_TYPE) === 'bigint';
    }

    public function up(): void
    {
        // If a previous failed attempt created the table without FKs and it has no data, drop it first.
        if (Schema::hasTable('vendor_category_vendor')) {
            try {
                $count = DB::table('vendor_category_vendor')->count();
            } catch (\Throwable $e) {
                $count = 0; // table exists but might be half-baked
            }
            if ($count === 0) {
                Schema::drop('vendor_category_vendor');
            }
        }

        $vendorIsBig = $this->isBigInt('vendors', 'id');
        $catIsBig    = $this->isBigInt('vendor_categories', 'id');

        if (! Schema::hasTable('vendor_category_vendor')) {
            Schema::create('vendor_category_vendor', function (Blueprint $table) use ($vendorIsBig, $catIsBig) {
                $vendorIsBig ? $table->unsignedBigInteger('vendor_id')
                             : $table->unsignedInteger('vendor_id');

                $catIsBig ? $table->unsignedBigInteger('vendor_category_id')
                          : $table->unsignedInteger('vendor_category_id');

                $table->timestamps();

                // Helpful indexes & uniqueness
                $table->index('vendor_id', 'vcv_vendor_idx');
                $table->index('vendor_category_id', 'vcv_category_idx');
                $table->unique(['vendor_id','vendor_category_id'], 'vcv_vendor_category_unique');
            });
        }

        // Add foreign keys (guard in case they already exist)
        // vendor_id -> vendors(id)
        try {
            Schema::table('vendor_category_vendor', function (Blueprint $table) {
                $table->foreign('vendor_id', 'vcv_vendor_fk')
                      ->references('id')->on('vendors')
                      ->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // ignore if already added / mismatched; see note below
        }

        // vendor_category_id -> vendor_categories(id)
        try {
            Schema::table('vendor_category_vendor', function (Blueprint $table) {
                $table->foreign('vendor_category_id', 'vcv_category_fk')
                      ->references('id')->on('vendor_categories')
                      ->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // ignore if already added / mismatched
        }

        /**
         * If you STILL get errno 150 here, it means the column types differ from the parents.
         * Quick checks you can run:
         *   SHOW COLUMNS FROM vendors LIKE 'id';
         *   SHOW COLUMNS FROM vendor_categories LIKE 'id';
         *   SHOW COLUMNS FROM vendor_category_vendor;
         * Make sure vendor_id type == vendors.id type (both UNSIGNED BIGINT or both UNSIGNED INT),
         * and vendor_category_id type == vendor_categories.id type.
         */
    }

    public function down(): void
    {
        if (Schema::hasTable('vendor_category_vendor')) {
            // Drop FKs first (best-effort)
            try {
                Schema::table('vendor_category_vendor', function (Blueprint $table) {
                    $table->dropForeign('vcv_vendor_fk');
                });
            } catch (\Throwable $e) {}

            try {
                Schema::table('vendor_category_vendor', function (Blueprint $table) {
                    $table->dropForeign('vcv_category_fk');
                });
            } catch (\Throwable $e) {}

            Schema::drop('vendor_category_vendor');
        }
    }
};
