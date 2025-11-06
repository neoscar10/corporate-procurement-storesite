// database/migrations/2025_10_25_100210_create_permissions_tables.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();   // e.g., create_procurement
            $table->string('label')->nullable(); // human label
            $table->timestamps();
        });

        Schema::create('user_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();

            $table->unique(['user_id','permission_id']);
            $table->index(['user_id','is_enabled']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_permission');
        Schema::dropIfExists('permissions');
    }
};
