<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('procurement_service_specs', function (Blueprint $t) {
      $t->id();
      $t->foreignId('procurement_item_id')->unique()->constrained()->cascadeOnDelete();
      $t->longText('scope_of_work')->nullable();
      $t->json('deliverables')->nullable();  // [{milestone,criteria,due_date?}]
      $t->json('key_personnels')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('procurement_service_specs'); }
};
