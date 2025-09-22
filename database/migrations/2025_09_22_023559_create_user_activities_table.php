<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            
            // User yang melakukan aksi
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Jenis aksi dan deskripsi
            $table->string('action'); // document_upload, tax_report_submitted, etc.
            $table->text('description'); // Deskripsi yang mudah dibaca
            
            // Polymorphic relationship ke model yang terkait
            $table->string('actionable_type')->nullable();
            $table->unsignedBigInteger('actionable_id')->nullable();
            
            // Data perubahan (untuk tracking changes)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Foreign keys untuk filtering yang lebih mudah
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes untuk performa
            $table->index(['user_id', 'created_at']);
            $table->index(['client_id', 'created_at']);
            $table->index(['project_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['actionable_type', 'actionable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};