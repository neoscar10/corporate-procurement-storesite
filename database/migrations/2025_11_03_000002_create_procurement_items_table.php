<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('procurement_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
      $t->foreignId('company_id')->constrained()->cascadeOnDelete();

      $t->string('kind'); // enum cast
      $t->string('name');
      $t->string('short_description')->nullable();
      $t->string('priority')->nullable();
      $t->string('unit')->nullable();
      $t->date('date_required')->nullable();
      $t->decimal('budget_amount',15,2)->nullable();

      $t->enum('service_budget_mode',['per_hour','fixed'])->nullable();
      $t->enum('service_payment_type',['per_hour','fixed'])->nullable();

      $t->boolean('is_draft')->default(true);
      $t->dateTime('detail_completed_at')->nullable();
      $t->dateTime('spec_completed_at')->nullable();
      $t->dateTime('attachments_completed_at')->nullable();
      $t->dateTime('completed_at')->nullable();

      $t->string('status')->default('draft');

      $t->softDeletes();
      $t->timestamps();

      $t->index(['procurement_request_id','status']);
      $t->index(['company_id','kind']);
    });
  }
  public function down(): void { Schema::dropIfExists('procurement_items'); }
};
