<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add pph_badan_contract to clients table (only if it doesn't exist)
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'pph_badan_contract')) {
                $table->boolean('pph_badan_contract')->default(false)->after('bupot_contract');
            }
        });

        // Add 'pph_badan' to tax_type enum in tax_calculation_summaries table
        // For MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tax_calculation_summaries MODIFY COLUMN tax_type ENUM('ppn', 'pph', 'bupot', 'pph_badan') NOT NULL COMMENT 'Jenis pajak'");
        }
        
        // For PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TYPE tax_type ADD VALUE IF NOT EXISTS 'pph_badan'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove pph_badan_contract from clients table (only if it exists)
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'pph_badan_contract')) {
                $table->dropColumn('pph_badan_contract');
            }
        });

        // Revert tax_type enum (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tax_calculation_summaries MODIFY COLUMN tax_type ENUM('ppn', 'pph', 'bupot') NOT NULL COMMENT 'Jenis pajak'");
        }
    }
};