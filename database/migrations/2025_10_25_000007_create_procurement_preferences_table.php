<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('procurement_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('avg_monthly_budget', 14, 2)->nullable();
            $table->enum('procurement_type', ['goods','services','both'])->default('both');
            $table->enum('frequency', ['monthly','quarterly','annual','ad-hoc'])->nullable();
            $table->string('preferred_payment_terms')->nullable();
            $table->json('preferred_vendor_locations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('procurement_preferences');
    }
};
