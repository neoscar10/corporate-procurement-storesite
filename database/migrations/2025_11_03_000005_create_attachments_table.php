<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('attachments', function (Blueprint $t) {
      $t->morphs('attachable'); // already indexed
        $t->foreignId('company_id')->constrained()->cascadeOnDelete();
        $t->string('disk')->default('public');
        $t->string('path');
        $t->string('original_name')->nullable();
        $t->string('mime',128)->nullable();
        $t->unsignedBigInteger('size_bytes')->default(0);
        $t->string('url')->nullable();
        $t->timestamps();

        $t->index('company_id'); // keep this one

    });
  }
  public function down(): void { Schema::dropIfExists('attachments'); }
};
