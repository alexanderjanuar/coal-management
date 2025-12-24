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
        Schema::table('income_taxes', function (Blueprint $table) {
            // Keep employee_id as required - it links to employees table
            // Employee table has: name, npwp, salary (DPP)
            
            // Drop old/legacy fields that are no longer needed
            $table->dropColumn([
                'ter_amount',
                'ter_category', 
                'pph_21_amount',
                'file_path',
                'bukti_setor',
            ]);
            
            // Add new DJP PPh fields based on CSV structure
            // Masa Pajak (Tax Period) - e.g., "06062025"
            $table->string('masa_pajak', 20)->nullable()->after('employee_id');
            
            // Nomor Pemotongan (Withholding Number) - e.g., "2503FY3OF"
            $table->string('nomor_pemotongan', 50)->nullable()->after('masa_pajak');
            
            // Status - e.g., "NORMAL"
            $table->string('status', 50)->default('NORMAL')->after('nomor_pemotongan');
            
            // NITKU/Nomor Identitas Sub Unit Organisasi
            $table->string('nitku', 50)->nullable()->after('status');
            
            // Jenis Pajak - e.g., "Pasal 21", "Pasal 23", "Pasal 4(2)"
            $table->string('jenis_pajak', 50)->nullable()->after('nitku');
            
            // Kode Objek Pajak - e.g., "21-100-01"
            $table->string('kode_objek_pajak', 50)->nullable()->after('jenis_pajak');
            
            // NPWP - Tax ID Number
            $table->string('npwp', 20)->nullable()->after('kode_objek_pajak');
            
            // Nama - Recipient Name
            $table->string('nama', 255)->nullable()->after('npwp');
            
            // Dasar Pengenaan Pajak (DPP) - Tax Base Amount
            $table->decimal('dasar_pengenaan_pajak', 15, 2)->default(0)->after('nama');
            
            // Pajak Penghasilan - Income Tax Amount
            $table->decimal('pajak_penghasilan', 15, 2)->default(0)->after('dasar_pengenaan_pajak');
            
            // Fasilitas Pajak - Tax Facility
            $table->string('fasilitas_pajak', 100)->default('Tanpa Fasilitas')->after('pajak_penghasilan');
            
            // Dilaporkan Dalam SPT - Reported in SPT (TRUE/FALSE)
            $table->boolean('dilaporkan_dalam_spt')->default(true)->after('fasilitas_pajak');
            
            // SPT Telah/Sedang Diperiksa - SPT Being Audited (TRUE/FALSE)
            $table->boolean('spt_sedang_diperiksa')->default(false)->after('dilaporkan_dalam_spt');
            
            // SPT Dalam Penanganan Hukum - SPT in Legal Handling (TRUE/FALSE)
            $table->boolean('spt_dalam_penanganan_hukum')->default(false)->after('spt_sedang_diperiksa');
            
            // Bukti Potong - Withholding certificate file path
            $table->string('bukti_potong')->nullable()->after('spt_dalam_penanganan_hukum');
            
            // Add index for better query performance
            $table->index('masa_pajak');
            $table->index('nomor_pemotongan');
            $table->index('jenis_pajak');
            $table->index('npwp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('income_taxes', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['masa_pajak']);
            $table->dropIndex(['nomor_pemotongan']);
            $table->dropIndex(['jenis_pajak']);
            $table->dropIndex(['npwp']);
            
            // Drop new columns
            $table->dropColumn([
                'masa_pajak',
                'nomor_pemotongan',
                'status',
                'nitku',
                'jenis_pajak',
                'kode_objek_pajak',
                'npwp',
                'nama',
                'dasar_pengenaan_pajak',
                'pajak_penghasilan',
                'fasilitas_pajak',
                'dilaporkan_dalam_spt',
                'spt_sedang_diperiksa',
                'spt_dalam_penanganan_hukum',
                'bukti_potong',
            ]);
            
            // Restore old fields
            $table->decimal('ter_amount', 15, 2)->default(0)->after('employee_id');
            $table->string('ter_category')->after('ter_amount');
            $table->decimal('pph_21_amount', 15, 2)->default(0)->after('ter_category');
            $table->string('file_path')->after('pph_21_amount');
        });
    }
};