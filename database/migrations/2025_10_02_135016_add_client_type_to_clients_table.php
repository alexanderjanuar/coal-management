<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('client_type', ['Badan', 'Pribadi'])
                  ->default('Badan')
                  ->after('id')
                  ->comment('Tipe klien: Badan (memerlukan PIC) atau Pribadi');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('client_type');
        });
    }
};