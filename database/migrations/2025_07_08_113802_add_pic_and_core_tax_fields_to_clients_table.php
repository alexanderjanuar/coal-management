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
        Schema::table('clients', function (Blueprint $table) {
            // Add PIC reference
            $table->foreignId('pic_id')
                  ->nullable()
                  ->after('person_in_charge')
                  ->constrained('pics')
                  ->nullOnDelete()
                  ->comment('Assigned PIC for this client');
            
            // Add Core Tax application credentials
            $table->string('core_tax_user_id')
                  ->nullable()
                  ->after('pic_id')
                  ->comment('Client ID for Core Tax application');
            
            $table->string('core_tax_password')
                  ->nullable()
                  ->after('core_tax_user_id')
                  ->comment('Password for Core Tax application');
                
            $table->dropColumn('person_in_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['pic_id']);
            $table->dropColumn(['pic_id', 'core_tax_user_id', 'core_tax_password']);
            $table->string('person_in_charge')->nullable();
        });
    }
};
