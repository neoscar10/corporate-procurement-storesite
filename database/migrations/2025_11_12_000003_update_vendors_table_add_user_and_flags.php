<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (! Schema::hasColumn('vendors', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('vendors', 'company_name')) {
                    $table->string('company_name')->nullable()->after('user_id');
                }
                if (! Schema::hasColumn('vendors', 'phone')) {
                    $table->string('phone', 40)->nullable()->after('company_name');
                }
                if (! Schema::hasColumn('vendors', 'provides_products')) {
                    $table->boolean('provides_products')->default(false)->after('phone');
                }
                if (! Schema::hasColumn('vendors', 'provides_services')) {
                    $table->boolean('provides_services')->default(false)->after('provides_products');
                }
                if (! Schema::hasColumn('vendors', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('provides_services');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                foreach (['user_id','company_name','phone','provides_products','provides_services','is_active'] as $col) {
                    if (Schema::hasColumn('vendors', $col)) {
                        if ($col === 'user_id') {
                            $table->dropConstrainedForeignId('user_id');
                        } else {
                            $table->dropColumn($col);
                        }
                    }
                }
            });
        }
    }
};
