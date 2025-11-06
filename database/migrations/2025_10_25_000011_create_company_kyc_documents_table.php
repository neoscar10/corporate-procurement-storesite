<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('company_kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type'); // incorporation|pan|gst|udyam|bank_cheque|auth_letter|board_resolution|other
            $table->string('file_path', 1024);
            $table->string('original_name')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id','document_type']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('company_kyc_documents');
    }
};
