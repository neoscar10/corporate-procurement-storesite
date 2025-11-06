<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('procurement_approvals', function (Blueprint $t) {
      $t->id();
      $t->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
      $t->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
      $t->string('status')->default('pending');
      $t->dateTime('approved_at')->nullable();
      $t->dateTime('rejected_at')->nullable();
      $t->text('comment')->nullable();
      $t->timestamps();

      $t->unique(['procurement_request_id','approver_id']);
      $t->index(['procurement_request_id','status']);
    });
  }
  public function down(): void { Schema::dropIfExists('procurement_approvals'); }
};
