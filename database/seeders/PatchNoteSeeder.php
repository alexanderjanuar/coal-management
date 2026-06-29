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

        PatchNote::updateOrCreate(
            ['version' => '1.6.0'],
            [
                'title'        => 'Pelaporan SPT Masa & Penyempurnaan Modul Pajak',
                'description'  => 'Unggah SPT Masa dari Coretax di tiap jenis pajak, ringkasan SPT untuk klien, notifikasi otomatis, serta navigasi & filter laporan pajak yang lebih pintar.',
                'is_published' => true,
                'released_at'  => now()->toDateString(),
                'created_by'   => User::query()->value('id'),
                'changes'      => [
                    // Fitur baru
                    [
                        'type' => 'feature',
                        'area' => 'Pajak',
                        'text' => 'Unggah SPT Masa: di tiap jenis pajak (PPN & PPh) kini ada tab "SPT" untuk mengunggah berkas SPT/BPE dari Coretax. Setelah diunggah, status masa otomatis menjadi Sudah Lapor & Sudah Bayar, lengkap dengan pratinjau dokumen.',
                    ],
                    [
                        'type' => 'feature',
                        'area' => 'Pajak',
                        'text' => 'Halaman klien menampilkan tabel "SPT Dilaporkan" — ringkasan seluruh SPT yang telah dilaporkan. Klik salah satu baris untuk membuka detail laporan masa tersebut.',
                    ],
                    [
                        'type' => 'feature',
                        'area' => 'Sistem',
                        'text' => 'Klien otomatis menerima notifikasi begitu SPT mereka diunggah & dilaporkan oleh tim.',
                    ],

                    // Peningkatan
                    [
                        'type' => 'improvement',
                        'area' => 'Pajak',
                        'text' => 'Navigasi bulan pada laporan pajak kini berwarna sesuai status pelaporan, dan hanya menghitung jenis pajak yang benar-benar dikontrak klien (berlaku di panel admin maupun klien).',
                    ],
                    [
                        'type' => 'improvement',
                        'area' => 'Pajak',
                        'text' => 'Daftar laporan pajak dilengkapi filter "Jenis Kontrak" untuk menyaring klien berdasarkan PPN, PPh, Bupot, atau PPh Badan.',
                    ],
                    [
                        'type' => 'improvement',
                        'area' => 'Pajak',
                        'text' => 'Tab PPh kini fokus pada PPh 21; jenis PPh lainnya (23 & 4(2)) akan dikelola di menu PPh Unifikasi.',
                    ],
                    [
                        'type' => 'improvement',
                        'area' => 'Pajak',
                        'text' => 'Tabel SPT pada halaman klien dilengkapi pencarian, filter (jenis & tahun), pengurutan kolom, dan pengelompokan (default per jenis SPT).',
                    ],
                ],
            ]
        );
    }
}
