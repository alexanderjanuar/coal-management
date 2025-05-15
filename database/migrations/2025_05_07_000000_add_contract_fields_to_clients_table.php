<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('ppn_contract')->default(false)->after('email');
            $table->string('ppn_contract_file')->nullable()->after('ppn_contract');
            $table->boolean('pph_contract')->default(false)->after('ppn_contract_file');
            $table->string('pph_contract_file')->nullable()->after('pph_contract');
            $table->boolean('bupot_contract')->default(false)->after('pph_contract_file');
            $table->string('bupot_contract_file')->nullable()->after('bupot_contract');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'ppn_contract',
                'ppn_contract_file',
                'pph_contract',
                'pph_contract_file',
                'bupot_contract',
                'bupot_contract_file'
            ]);
        });
    }
};
