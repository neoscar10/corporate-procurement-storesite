<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('procurement_request_watchers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamps();

            $t->unique(['procurement_request_id','user_id'], 'pr_watchers_unique');
            $t->index(['procurement_request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_request_watchers');
    }
};
