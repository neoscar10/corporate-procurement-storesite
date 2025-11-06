<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('company_onboarding_progress', function (Blueprint $table) {
      $table->id();
      $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->unique();
      $table->boolean('procurement_done')->default(false);
      $table->timestamp('procurement_done_at')->nullable();
      $table->boolean('kyc_done')->default(false);
      $table->timestamp('kyc_done_at')->nullable();
      $table->boolean('billing_done')->default(false);
      $table->timestamp('billing_done_at')->nullable();
      $table->timestamp('completed_at')->nullable(); // set when all three are done
      $table->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('company_onboarding_progress'); }
};
