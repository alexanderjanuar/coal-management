<?php
// database/migrations/xxxx_xx_xx_add_compensation_fields_to_tax_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('pkp_status', ['PKP', 'Non-PKP'])
                  ->default('Non-PKP')
                  ->after('status')
                  ->comment('Status Pengusaha Kena Pajak');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'pkp_status',
            ]);
        });
    }
};