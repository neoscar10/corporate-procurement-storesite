<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('brand_name')->nullable();

            // India IDs
            $table->string('cin', 21)->nullable()->unique();   // 21 chars
            $table->string('pan', 10)->nullable()->unique();   // 10 chars
            $table->string('gstin', 15)->nullable()->unique(); // 15 chars

            $table->timestamp('cin_verified_at')->nullable();
            $table->timestamp('pan_verified_at')->nullable();
            $table->timestamp('gstin_verified_at')->nullable();

            $table->string('organization_type')->nullable();
            $table->string('industry')->nullable();
            $table->string('nature_of_business')->nullable();
            $table->enum('status', ['pending','approved','rejected','cancelled'])
                  ->default('pending');
            $table->text('status_reason')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->index('status');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['organization_type', 'industry']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('companies');
    }
};
