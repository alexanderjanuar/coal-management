<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('required_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_step_id')->constrained('project_steps')->onDelete('cascade');
            $table->string('name');
            $table->enum('status', ['draft', 'uploaded', 'pending_review', 'approved', 'rejected'])->default('draft');
            $table->text('description')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('required_documents');
    }
};
