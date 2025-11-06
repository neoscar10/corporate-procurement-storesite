<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // identity
            $table->string('name');
            $table->string('username')->nullable()->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable()->index();

            // auth
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_password_change_at')->nullable();

            // roles/permissions
            $table->unsignedBigInteger('role_id')->nullable()->index(); // FK optional; add constraint when roles table exists
            $table->boolean('is_admin')->default(false);    // superuser switch
            $table->boolean('is_active')->default(true);    // easy disable

            // 2FA (store securely)
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();

            // profile & prefs
            $table->string('avatar_path', 1024)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->string('locale', 8)->default('en');

            // optional SSO hooks
            $table->string('auth_provider', 32)->nullable();
            $table->string('auth_provider_id', 128)->nullable()->index();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_admin','is_active']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
