<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->string('context_type', 50)
                ->default('general')
                ->after('type')
                ->comment('Konteks atau area dimana usulan ini berkaitan');
        });
    }

    public function down(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->dropColumn(['context_type']);
        });
    }
};