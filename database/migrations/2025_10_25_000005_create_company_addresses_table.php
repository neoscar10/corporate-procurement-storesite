<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('company_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['registered','corporate','billing']);
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pin_code', 12)->nullable();
            $table->string('country', 64)->default('India');
            $table->timestamps();

            $table->unique(['company_id','type']); // one of each per company
            $table->index(['state', 'city']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('company_addresses');
    }
};
