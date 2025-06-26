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
        Schema::table('invoices', function (Blueprint $table) {
            // Add client_type field after invoice_number
            $table->string('client_type', 50)
                  ->nullable()
                  ->after('invoice_number')
                  ->comment('Client type based on first 2 digits of invoice number');
                  
            // Add has_ppn boolean field
            $table->boolean('has_ppn')
                  ->default(true)
                  ->after('client_type')
                  ->comment('Whether this client type is subject to PPN');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'has_ppn']);
        });
    }
};