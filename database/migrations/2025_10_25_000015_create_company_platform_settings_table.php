<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('company_platform_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('communication_preference', ['email','sms','whatsapp'])->default('email');
            $table->string('preferred_language', 8)->default('en');
            $table->enum('notification_frequency', ['immediate','daily'])->default('immediate');
            $table->boolean('data_sharing_consent')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('company_platform_settings');
    }
};
