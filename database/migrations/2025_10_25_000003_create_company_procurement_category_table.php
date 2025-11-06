<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
        Schema::create('company_procurement_category', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
        $table->foreignId('procurement_category_id')->constrained('procurement_categories')->cascadeOnDelete();
        $table->timestamps();

        // Short, explicit unique index name (fixes 1059)
        $table->unique(['company_id','procurement_category_id'], 'cmpy_proc_cat_uq');
    });

  }
  public function down(): void { Schema::dropIfExists('company_procurement_category'); }
};
