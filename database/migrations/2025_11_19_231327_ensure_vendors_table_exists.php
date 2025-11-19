<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->id();

                // nullable user link; we nullOnDelete so removing a user won't hard-block
                $table->foreignId('user_id')->nullable()
                      ->constrained('users')->nullOnDelete();

                $table->string('name')->nullable();       // display name
                $table->string('email')->nullable();      // storing here for quick listing/filter
                $table->string('phone')->nullable();
                $table->string('company_name')->nullable();

                $table->boolean('provides_products')->default(false);
                $table->boolean('provides_services')->default(false);
                $table->boolean('is_active')->default(true);

                $table->timestamps();
                $table->softDeletes();

                $table->index('is_active');
                $table->index(['provides_products', 'provides_services']);
            });
        } else {
            // Ensure required columns exist (idempotent on re-runs)
            Schema::table('vendors', function (Blueprint $table) {
                if (! Schema::hasColumn('vendors', 'user_id')) {
                    $table->foreignId('user_id')->nullable()
                          ->after('id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('vendors', 'name')) {
                    $table->string('name')->nullable()->after('user_id');
                }
                if (! Schema::hasColumn('vendors', 'email')) {
                    $table->string('email')->nullable()->after('name');
                }
                if (! Schema::hasColumn('vendors', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (! Schema::hasColumn('vendors', 'company_name')) {
                    $table->string('company_name')->nullable()->after('phone');
                }
                if (! Schema::hasColumn('vendors', 'provides_products')) {
                    $table->boolean('provides_products')->default(false)->after('company_name');
                }
                if (! Schema::hasColumn('vendors', 'provides_services')) {
                    $table->boolean('provides_services')->default(false)->after('provides_products');
                }
                if (! Schema::hasColumn('vendors', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('provides_services');
                }
                if (! Schema::hasColumn('vendors', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Helpful indexes if they don’t already exist (best-effort)
            try {
                Schema::table('vendors', function (Blueprint $table) {
                    $table->index('is_active', 'vendors_is_active_index');
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('vendors', function (Blueprint $table) {
                    $table->index(['provides_products','provides_services'], 'vendors_provides_combined_index');
                });
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // We won’t drop the table to avoid data loss; this is a no-op rollback.
        // If you really need to remove it, uncomment the next line.
        // Schema::dropIfExists('vendors');
    }
};
