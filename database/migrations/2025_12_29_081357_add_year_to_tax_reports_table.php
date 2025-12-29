<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->year('year')
                  ->after('month')
                  ->default(now()->year)
                  ->comment('Tahun pelaporan pajak');
            
            // Create new composite index (includes year)
            $table->index(['client_id', 'year', 'month'], 'tax_reports_client_period_index');
        });
    }

    public function down(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->dropIndex('tax_reports_client_period_index');
            $table->dropColumn('year');
        });
    }
};