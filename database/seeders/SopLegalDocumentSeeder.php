<?php

namespace Database\Seeders;

use App\Models\SopLegalDocument;
use Illuminate\Database\Seeder;

class SopLegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            // ========================================
            // DOKUMEN KLIEN BADAN
            // ========================================
            [
                'name' => 'Akta Pendirian',
                'description' => 'Akta pendirian perusahaan yang telah dilegalisir notaris',
                'client_type' => 'Badan',
                'is_required' => true,
                'category' => 'Dasar',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'NPWP Badan',
                'description' => 'Nomor Pokok Wajib Pajak perusahaan',
                'client_type' => 'Badan',
                'is_required' => true,
                'category' => 'Dasar',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Surat Keterangan Terdaftar (SKT)',
                'description' => 'SKT dari Direktorat Jenderal Pajak',
                'client_type' => 'Badan',
                'is_required' => true,
                'category' => 'Dasar',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'KTP Seluruh Pengurus',
                'description' => 'Fotokopi KTP direktur dan pengurus perusahaan',
                'client_type' => 'Badan',
                'is_required' => true,
                'category' => 'Dasar',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'NPWP Seluruh Pengurus',
                'description' => 'NPWP pribadi direktur dan pengurus perusahaan',
                'client_type' => 'Badan',
                'is_required' => true,
                'category' => 'Dasar',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Draft Pengukuhan PKP',
                'description' => 'Draft surat pengukuhan sebagai Pengusaha Kena Pajak',
                'client_type' => 'Badan',
                'is_required' => false, // Opsional, hanya untuk yang PKP
                'category' => 'PKP',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Surat Domisili',
                'description' => 'Surat keterangan domisili perusahaan dari kelurahan/kecamatan',
                'client_type' => 'Badan',
                'is_required' => false,
                'category' => 'Pendukung',
                'order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Surat Fiskal',
                'description' => 'Dokumen fiskal perusahaan',
                'client_type' => 'Badan',
                'is_required' => false,
                'category' => 'Pendukung',
                'order' => 8,
                'is_active' => true,
            ],

            // ========================================
            // DOKUMEN KLIEN PRIBADI
            // ========================================
            [
                'name' => 'KTP',
                'description' => 'Kartu Tanda Penduduk yang masih berlaku',
                'client_type' => 'Pribadi',
                'is_required' => true,
                'category' => 'Dasar',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'NPWP',
                'description' => 'Nomor Pokok Wajib Pajak pribadi',
                'client_type' => 'Pribadi',
                'is_required' => true,
                'category' => 'Dasar',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Kartu Keluarga',
                'description' => 'Kartu Keluarga terbaru',
                'client_type' => 'Pribadi',
                'is_required' => true,
                'category' => 'Dasar',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'NIB (Nomor Induk Berusaha)',
                'description' => 'NIB untuk yang memiliki usaha',
                'client_type' => 'Pribadi',
                'is_required' => false, // Opsional
                'category' => 'Pendukung',
                'order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($documents as $document) {
            SopLegalDocument::create($document);
        }

        $this->command->info('✅ SOP Legal Documents seeded successfully!');
        $this->command->info('   - Dokumen Badan: 8 items');
        $this->command->info('   - Dokumen Pribadi: 4 items');
    }
}