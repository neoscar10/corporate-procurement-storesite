<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('company_onboarding_progress', function (Blueprint $table) {
            $table->boolean('addresses_done')->default(false)->after('id');
            $table->timestamp('addresses_done_at')->nullable()->after('addresses_done');

            $table->boolean('contact_done')->default(false)->after('addresses_done_at');
            $table->timestamp('contact_done_at')->nullable()->after('contact_done');
        });
    }

    public function down(): void
    {
        Schema::table('company_onboarding_progress', function (Blueprint $table) {
            $table->dropColumn(['addresses_done', 'addresses_done_at', 'contact_done', 'contact_done_at']);
        });
    }
};
