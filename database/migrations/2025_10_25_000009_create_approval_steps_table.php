<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_workflow_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position'); // 1..n
            $table->string('label')->nullable(); // Manager, CFO, etc.
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approver_role')->nullable(); // optional role label
            $table->decimal('threshold_amount', 14, 2)->nullable();
            $table->timestamps();

            $table->unique(['approval_workflow_id', 'position']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('approval_steps');
    }
};
