<?php

namespace Database\Seeders;

use App\Models\PatchNote;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatchNoteSeeder extends Seeder
{
    public function run(): void
    {
        PatchNote::updateOrCreate(
            ['version' => '1.5.0'],
            [
                'title'        => 'Peningkatan Modul Klien, Pajak & Proyek',
                'description'  => 'Beberapa penyempurnaan agar mengelola klien, faktur pajak, dan hasil proyek jadi lebih mudah.',
                'is_published' => true,
                'released_at'  => now()->toDateString(),
                'created_by'   => User::query()->value('id'),
                'changes'      => [
                    // Fitur baru
                    [
                        'type' => 'feature',
                        'area' => 'Klien',
                        'text' => 'Saat menambah klien baru, kredensial Core Tax, DJP, dan Email bisa langsung diisi di form yang sama — tidak perlu buka menu lain.',
                    ],
                    [
                        'type' => 'feature',
                        'area' => 'Proyek',
                        'text' => 'Setelah proyek selesai, file hasil pengerjaan (deliverable) bisa langsung dikirim ke arsip Dokumen Klien.',
                    ],
                    [
                        'type' => 'feature',
                        'area' => 'Sistem',
                        'text' => 'Info pembaruan sistem kini tampil sebagai banner di halaman Dashboard (seperti yang Anda lihat sekarang) dan bisa ditutup.',
                    ],

                    // Peningkatan
                    [
                        'type' => 'improvement',
                        'area' => 'Klien',
                        'text' => 'Saat memilih PIC untuk klien Badan, pilihannya dikelompokkan: ambil dari daftar PIC, atau dari klien Pribadi yang sudah terdaftar sebagai PIC.',
                    ],
                    [
                        'type' => 'improvement',
                        'area' => 'Proyek',
                        'text' => 'Saat mengirim hasil proyek ke klien, file bisa langsung dipasang ke slot Dokumen Legal Wajib atau Persyaratan klien — bukan hanya jadi dokumen umum.',
                    ],
                    [
                        'type' => 'improvement',
                        'area' => 'Klien',
                        'text' => 'Halaman daftar Klien kini nyaman dibuka lewat tablet maupun ponsel; tampilannya otomatis menyesuaikan ukuran layar.',
                    ],

                    // Perbaikan
                    [
                        'type' => 'fix',
                        'area' => 'Pajak',
                        'text' => 'Nilai PPN di faktur sekarang boleh diketik manual. Tetap terisi otomatis, tapi bisa disesuaikan bila ada selisih pembulatan.',
                    ],
                ],
            ]
        );
    }
}
