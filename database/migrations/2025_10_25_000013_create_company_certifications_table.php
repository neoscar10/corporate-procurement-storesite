<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('company_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // iso|esg|government_registration|license|other
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('issuer')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();

            $table->unique(['company_id','type','code']);
            $table->index('type');
        });
    }

    public function down(): void {
        Schema::dropIfExists('company_certifications');
    }
};
