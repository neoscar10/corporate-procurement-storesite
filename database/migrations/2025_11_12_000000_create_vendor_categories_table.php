<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20)->default('product'); // 'product' | 'service'
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['kind', 'slug']);
            $table->index(['kind', 'is_active']);
            $table->foreign('parent_id')->references('id')->on('vendor_categories')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('vendor_categories');
    }
};
