<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('company_representatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('designation')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('govt_id_type')->nullable();
            $table->string('govt_id_number')->nullable();
            $table->string('signature_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // links to users
            $table->timestamps();

            $table->index(['email', 'mobile']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('company_representatives');
    }
};
