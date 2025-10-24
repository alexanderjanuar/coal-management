<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['meeting', 'email', 'phone', 'video_call', 'other'])->default('other');
            $table->date('communication_date');
            $table->time('communication_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'communication_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_communications');
    }
};