<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('procurement_product_specs', function (Blueprint $t) {
      $t->id();
      $t->foreignId('procurement_item_id')->unique()->constrained()->cascadeOnDelete();
      $t->string('brand')->nullable();
      $t->string('model')->nullable();
      $t->string('quality_level')->nullable();
      $t->string('packaging_requirement')->nullable();
      $t->boolean('inspection_required')->default(false);
      $t->json('technical_specs')->nullable(); // [{key,value}]
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('procurement_product_specs'); }
};
