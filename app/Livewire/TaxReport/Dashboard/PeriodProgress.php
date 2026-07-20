<?php

namespace App\Livewire\TaxReport\Dashboard;

use App\Services\TaxDeadlineService;
use Carbon\Carbon;
use Livewire\Attributes\On;
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

    public function mount(TaxDeadlineService $deadlines): void
    {
        $this->hydrateFiltersFromRequest($deadlines);
    }

    public function render(TaxDeadlineService $service)
    {
        $period = $this->periodDate();

        $row = $service->periodQuery($period, $this->clientId)
            ->selectRaw("
                SUM(CASE WHEN s.tax_type = 'ppn' THEN 1 ELSE 0 END) as ppn_total,
                SUM(CASE WHEN s.tax_type = 'ppn' AND s.report_status = 'Sudah Lapor' THEN 1 ELSE 0 END) as ppn_done,
                SUM(CASE WHEN s.tax_type = 'pph' THEN 1 ELSE 0 END) as pph_total,
                SUM(CASE WHEN s.tax_type = 'pph' AND s.report_status = 'Sudah Lapor' THEN 1 ELSE 0 END) as pph_done,
                SUM(CASE WHEN s.tax_type = 'bupot' THEN 1 ELSE 0 END) as bupot_total,
                SUM(CASE WHEN s.tax_type = 'bupot' AND s.report_status = 'Sudah Lapor' THEN 1 ELSE 0 END) as bupot_done,
                SUM(CASE WHEN s.status_final <> 'Nihil' THEN 1 ELSE 0 END) as pay_total,
                SUM(CASE WHEN s.status_final <> 'Nihil' AND s.bayar_status = 'Sudah Bayar' THEN 1 ELSE 0 END) as pay_done
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

        $rows = collect($definitions)
            // Filter jenis pajak menyembunyikan baris yang tidak diminta.
            // Baris pembayaran selalu tampil: ia berlaku untuk semua jenis.
            ->filter(fn (array $d) => ! $this->taxType || $d['key'] === $this->taxType || $d['key'] === 'pay')
            ->map(function (array $definition) use ($row) {
                $total = (int) ($row->{$definition['key'] . '_total'} ?? 0);
                $done = (int) ($row->{$definition['key'] . '_done'} ?? 0);

                return $definition + [
                    'total' => $total,
                    'done' => $done,
                    'percent' => $total > 0 ? round(($done / $total) * 100) : null,
                ];
            })
            ->values();

        return view('livewire.tax-report.dashboard.period-progress', [
            'rows' => $rows,
            'service' => $service,
            'periodDate' => $period,
            'hasData' => $rows->sum('total') > 0,
        ]);
    }
}
