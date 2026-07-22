<?php

namespace App\Livewire\TaxReport\Dashboard;

use App\Services\TaxDeadlineService;
use Livewire\Attributes\Session;
use Livewire\Component;

/**
 * Progres kelengkapan pelaporan untuk satu periode.
 *
 * Menggantikan empat kartu StatsOverview yang seragam. Pertanyaan yang dijawab
 * di sini adalah "seberapa lengkap", bukan "seberapa mendesak", jadi bar-nya
 * sengaja netral: urgensi sudah ditangani tulang punggung tenggat di atas, dan
 * memberi warna dua kali untuk hal yang sama justru melemahkan keduanya.
 */
class PeriodProgress extends Component
{
    use Concerns\ReadsDashboardFilters;

    /**
     * Bentuk tampilan: 'bar' (baris progres) atau 'kolom' (chart batang).
     *
     * Disimpan di sesi, bukan di URL: ini preferensi cara melihat, bukan bagian
     * dari data yang ditampilkan. Menaruhnya di URL akan membuat tautan yang
     * dibagikan ikut memaksakan selera tampilan penerimanya.
     */
    #[Session(key: 'tp-progress-view')]
    public string $view = 'bar';

    public function mount(TaxDeadlineService $deadlines): void
    {
        $this->hydrateFiltersFromRequest($deadlines);
    }

    public function setView(string $view): void
    {
        if (\in_array($view, ['bar', 'kolom'], true)) {
            $this->view = $view;
        }
    }

    /**
     * Mengklik satu jenis pajak menyaring seluruh dashboard ke jenis itu.
     *
     * Filters yang memegang state dan menulisnya ke URL, jadi di sini cukup
     * mengirim permintaan. Mengklik jenis yang sedang aktif akan melepasnya.
     */
    public function toggleTaxType(string $key): void
    {
        $this->dispatch('taxTypeRequested', taxType: $this->taxType === $key ? null : $key);
    }

    public function render(TaxDeadlineService $service)
    {
        $period = $this->periodDate();

        /*
         * Masa yang dinyatakan tanpa aktivitas dihitung terpisah (kolom _idle),
         * bukan dikeluarkan dari total. Statusnya memang "Sudah Lapor" sehingga
         * ia tidak menjadi tunggakan, tapi menampilkannya sebagai "selesai"
         * akan menyamarkan bahwa tidak pernah ada SPT di sana.
         */
        $row = $service->periodQuery($period, $this->clientId)
            ->selectRaw("
                SUM(CASE WHEN s.tax_type = 'ppn' THEN 1 ELSE 0 END) as ppn_total,
                SUM(CASE WHEN s.tax_type = 'ppn' AND s.report_status = 'Sudah Lapor' THEN 1 ELSE 0 END) as ppn_done,
                SUM(CASE WHEN s.tax_type = 'pph' THEN 1 ELSE 0 END) as pph_total,
                SUM(CASE WHEN s.tax_type = 'pph' AND s.report_status = 'Sudah Lapor' THEN 1 ELSE 0 END) as pph_done,

                SUM(CASE WHEN s.tax_type = 'bupot' AND s.no_activity = 0 THEN 1 ELSE 0 END) as bupot_total,
                SUM(CASE WHEN s.tax_type = 'bupot' AND s.no_activity = 0 AND s.report_status = 'Sudah Lapor' THEN 1 ELSE 0 END) as bupot_done,
                SUM(CASE WHEN s.tax_type = 'bupot' AND s.no_activity = 1 THEN 1 ELSE 0 END) as bupot_idle,

                SUM(CASE WHEN s.status_final <> 'Nihil' AND s.no_activity = 0 THEN 1 ELSE 0 END) as pay_total,
                SUM(CASE WHEN s.status_final <> 'Nihil' AND s.no_activity = 0 AND s.bayar_status = 'Sudah Bayar' THEN 1 ELSE 0 END) as pay_done
            ")
            ->first();

        // 'color' memakai warna identitas jenis pajak yang sama dengan titik pada
        // chip di daftar triase, supaya pemetaannya satu dan bisa dipelajari.
        // Pembayaran bukan jenis pajak, jadi barnya netral.
        $definitions = [
            // 'key' tetap nilai tax_type di database; hanya 'label' yang berubah.
            ['key' => 'ppn', 'label' => 'PPN', 'verb' => 'sudah lapor', 'color' => 'var(--tp-ppn)'],
            ['key' => 'pph', 'label' => 'PPh 21', 'verb' => 'sudah lapor', 'color' => 'var(--tp-pph)'],
            ['key' => 'bupot', 'label' => 'PPh Unifikasi', 'verb' => 'sudah lapor', 'color' => 'var(--tp-bupot)'],
            ['key' => 'pay', 'label' => 'Pembayaran', 'verb' => 'sudah bayar', 'color' => 'var(--tp-mark)'],
        ];

        /*
         * Seluruh baris selalu ditampilkan, termasuk saat filter jenis pajak
         * aktif. Sebelumnya baris yang tidak difilter disembunyikan, tapi begitu
         * baris ini jadi kontrol filter, menyembunyikannya membuat pengguna
         * terkunci: tidak ada lagi baris lain untuk diklik supaya berpindah.
         * Yang aktif ditandai, bukan yang lain dihilangkan.
         */
        $rows = collect($definitions)
            ->map(function (array $definition) use ($row) {
                $total = (int) ($row->{$definition['key'] . '_total'} ?? 0);
                $done = (int) ($row->{$definition['key'] . '_done'} ?? 0);

                // Jumlah masa yang tidak menimbulkan kewajiban lapor. Hanya
                // berlaku untuk PPh Unifikasi; jenis lain wajib tiap masa.
                $idle = (int) ($row->{$definition['key'] . '_idle'} ?? 0);

                return $definition + [
                    'total' => $total,
                    'done' => $done,
                    'idle' => $idle,
                    'remaining' => max(0, $total - $done),
                    'percent' => $total > 0 ? round(($done / $total) * 100) : null,
                    // Pembayaran bukan jenis pajak, jadi ia tidak punya padanan
                    // di filter tax_type dan tidak bisa dipakai menyaring.
                    'filterable' => $definition['key'] !== 'pay',
                    'active' => $this->taxType === $definition['key'],
                ];
            })
            ->values();

        return view('livewire.tax-report.dashboard.period-progress', [
            'rows' => $rows,
            'service' => $service,
            'periodDate' => $period,
            // Masa tanpa aktivitas ikut dihitung sebagai "ada data": panelnya
            // memang punya sesuatu untuk dikatakan, yaitu bahwa masa itu tidak
            // menimbulkan kewajiban. Tanpa ini panel berbunyi "belum ada data"
            // padahal datanya ada.
            'hasData' => ($rows->sum('total') + $rows->sum('idle')) > 0,
        ]);
    }
}
