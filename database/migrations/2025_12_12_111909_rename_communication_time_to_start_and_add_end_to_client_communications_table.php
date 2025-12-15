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
        Schema::table('client_communications', function (Blueprint $table) {
            // Rename communication_time to communication_time_start
            $table->renameColumn('communication_time', 'communication_time_start');
        });

        Schema::table('client_communications', function (Blueprint $table) {
            // Add communication_time_end after communication_time_start
            $table->time('communication_time_end')->nullable()->after('communication_time_start')
                  ->comment('Waktu selesai komunikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_communications', function (Blueprint $table) {
            // Drop communication_time_end
            $table->dropColumn('communication_time_end');
        });

        Schema::table('client_communications', function (Blueprint $table) {
            // Rename back to communication_time
            $table->renameColumn('communication_time_start', 'communication_time');
        });
    }
};