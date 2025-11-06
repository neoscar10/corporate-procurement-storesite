<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Procurement\{RequestType,Priority,RequestStatus};

return new class extends Migration {
  public function up(): void {
    Schema::create('procurement_requests', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained()->cascadeOnDelete();
      $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();

      $t->string('title');
      $t->string('type');   // cast to RequestType enum in model
      $t->string('priority'); // cast to Priority enum
      $t->dateTime('desired_response_at')->nullable();
      $t->dateTime('expected_delivery_at')->nullable();

      $t->char('currency',3)->default('INR');
      $t->decimal('budget_min',15,2)->nullable();
      $t->decimal('budget_max',15,2)->nullable();
      $t->enum('payment_terms',['advance','net_30','net_45','net_50'])->nullable();
      $t->text('delivery_location')->nullable();
      $t->string('preferred_vendor_region')->nullable(); // or json if multi
      $t->text('notes')->nullable();

      $t->string('status')->default(RequestStatus::DRAFT->value);
      $t->string('stage')->default('building');
      $t->unsignedInteger('items_count')->default(0);
      $t->unsignedInteger('attachments_count')->default(0);
      $t->dateTime('approved_at')->nullable();
      $t->dateTime('published_at')->nullable();

      $t->softDeletes();
      $t->timestamps();

      $t->index(['company_id','status','stage']);
      $t->index(['type','priority']);
      $t->index('published_at');
    });
  }
  public function down(): void { Schema::dropIfExists('procurement_requests'); }
};
