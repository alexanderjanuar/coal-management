<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patch_notes', function (Blueprint $table) {
            $table->id();
            $table->string('version');                       // mis. "1.5.0" atau label rilis
            $table->string('title');
            $table->text('description')->nullable();         // ringkasan rilis (opsional)
            $table->json('changes')->nullable();             // [{type: feature|improvement|fix, text, image}]
            $table->boolean('is_published')->default(false); // hanya yang published yang dimunculkan
            $table->date('released_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patch_notes');
    }
};
