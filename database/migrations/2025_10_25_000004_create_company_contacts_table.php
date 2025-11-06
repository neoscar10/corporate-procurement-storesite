<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('company_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('official_email')->nullable();
            $table->string('alternate_email')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('contact_mobile')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();

            $table->index('official_email');
            $table->index('primary_phone');
        });
    }

    public function down(): void {
        Schema::dropIfExists('company_contacts');
    }
};
