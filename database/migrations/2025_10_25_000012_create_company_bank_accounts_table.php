<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('company_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('branch')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_number');
            $table->string('ifsc', 20)->nullable();
            $table->enum('preferred_payment_method', ['NEFT','RTGS','UPI','IMPS','Cheque'])->nullable();
            $table->string('credit_term')->nullable(); // e.g., Net 30
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'account_number', 'ifsc']);
            $table->index(['company_id', 'is_default']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('company_bank_accounts');
    }
};
