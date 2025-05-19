<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // First add the new column
            $table->string('contract_file')->nullable()->after('bupot_contract_file');
            
            // Then drop the specific contract file columns
            $table->dropColumn([
                'ppn_contract_file',
                'pph_contract_file',
                'bupot_contract_file'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Recreate the dropped columns
            $table->string('ppn_contract_file')->nullable()->after('ppn_contract');
            $table->string('pph_contract_file')->nullable()->after('pph_contract');
            $table->string('bupot_contract_file')->nullable()->after('bupot_contract');
            
            // Drop the new column
            $table->dropColumn('contract_file');
        });
    }
};