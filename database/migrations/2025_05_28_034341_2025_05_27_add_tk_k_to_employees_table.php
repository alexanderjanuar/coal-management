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
        Schema::table('employees', function (Blueprint $table) {
            // Add TK (Tidak Kawin/Single) status - number of dependents for single person
            $table->integer('tk')->default(0)->after('type')->comment('TK status - number of dependents for single person (0-3)');
            
            // Add K (Kawin/Married) status - number of dependents for married person
            $table->integer('k')->default(0)->after('tk')->comment('K status - number of dependents for married person (0-3)');
            
            // Add marital status to determine which status applies
            $table->enum('marital_status', ['single', 'married'])->default('single')->after('k')->comment('Marital status to determine TK or K application');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['tk', 'k', 'marital_status']);
        });
    }
};