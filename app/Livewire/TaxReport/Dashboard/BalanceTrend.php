<?php

namespace App\Livewire\TaxReport\Dashboard;

use App\Services\TaxDeadlineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Saldo akhir 12 bulan terakhir sampai periode terpilih.
 *
 * saldo_final bertanda: Kurang Bayar positif, Lebih Bayar negatif. Batangnya
 * karena itu digambar di atas dan di bawah garis nol. Tandanya dikodekan lewat
 * posisi, bukan warna, sehingga paletnya tetap netral dan tetap terbaca oleh
 * orang yang tidak membedakan warna.
 */
class BalanceTrend extends Component
{
    use Concerns\ReadsDashboardFilters;

    public const MONTHS = 12;

    public function mount(TaxDeadlineService $deadlines): void
    {
        $this->hydrateFiltersFromRequest($deadlines);
    }

    /** Mengklik satu batang memindahkan seluruh halaman ke periode itu. */
    public function selectPeriod(string $period): void
    {
        $this->dispatch('periodRequested', period: $period);
    }

    public function render(TaxDeadlineService $service)
    {
        $selected = $this->periodDate();

        /** @var array<string, Carbon> $window */
        $window = [];
        for ($i = self::MONTHS - 1; $i >= 0; $i--) {
            $month = $selected->copy()->subMonths($i);
            $window[$service->periodKey($month)] = $month;
        }

        $totals = $this->fetchTotals(array_keys($window));

        $points = collect($window)->map(function (Carbon $month, string $key) use ($service, $selected, $totals) {
            return [
                'key' => $key,
                'period' => $month->format('Y-m'),
                'label' => $service->monthShort($month),
                'full_label' => $service->periodLabel($month),
                'value' => (float) ($totals[$key] ?? 0),
                'is_selected' => $month->equalTo($selected),
                'is_january' => $month->month === 1,
            ];
        })->values();

        /*
         * Garis nol mengikuti data, bukan dipaku di tengah.
         *
         * Kalau semua nilai positif, garis nol turun ke dasar dan batang memakai
         * seluruh tinggi. Versi sebelumnya selalu menaruhnya di 50%, sehingga
         * pada jendela yang seluruhnya kurang bayar separuh chart kosong permanen
         * dan batangnya kerdil tanpa alasan.
         */
        $values = $points->map(fn (array $p) => (float) $p['value']);
        $top = max($values->max() ?? 0.0, 0.0);
        $bottom = min($values->min() ?? 0.0, 0.0);
        $range = $top - $bottom;

        // Persentase dari atas menuju garis nol.
        $zeroLine = $range > 0 ? ($top / $range) * 100 : 50.0;

        $points = $points->map(function (array $point) use ($range) {
            // Nilai bukan-nol yang sangat kecil tetap dijamin punya jejak minimal
            // 1,5%, kalau tidak ia hilang dan tak bisa dibedakan dari nihil.
            $height = $range > 0 ? abs($point['value']) / $range * 100 : 0;

            return $point + [
                'height' => $point['value'] != 0 ? max($height, 1.5) : 0,
            ];
        });

        $first = $selected->copy()->subMonths(self::MONTHS - 1);

        return view('livewire.tax-report.dashboard.balance-trend', [
            'points' => $points,
            'service' => $service,
            'selected' => $selected,
            'selectedValue' => (float) ($totals[$service->periodKey($selected)] ?? 0),
            'zeroLine' => round($zeroLine, 3),
            'hasNegative' => $bottom < 0,
            'hasData' => $range > 0,
            'rangeLabel' => $service->monthShort($first) . ' ' . $first->year
                . ' sampai ' . $service->monthShort($selected) . ' ' . $selected->year,
        ]);
    }

    /**
     * Satu query untuk seluruh jendela 12 bulan.
     *
     * Periode disimpan sebagai nama bulan Inggris plus kolom tahun terpisah,
     * jadi keduanya digabung menjadi satu kunci agar Januari 2026 tidak
     * tercampur dengan Januari 2025.
     *
     * @param  array<int, string>  $keys
     * @return array<string, float>
     */
    protected function fetchTotals(array $keys): array
    {
        $periodKey = "CONCAT(tax_reports.year, '-', tax_reports.month)";

        return DB::table('tax_calculation_summaries as s')
            ->join('tax_reports', 's.tax_report_id', '=', 'tax_reports.id')
            ->join('clients', 'tax_reports.client_id', '=', 'clients.id')
            ->where('clients.status', 'Active')
            // Aturan yang sama dengan periodQuery(): hanya kewajiban yang
            // dikontrakkan. Saat ini semua baris tanpa kontrak bersaldo nol
            // sehingga angkanya tidak berubah, tapi aturannya harus seragam
            // agar tidak menyimpang begitu ada data yang tidak nol.
            ->whereRaw(TaxDeadlineService::contractedCondition())
            ->whereIn(DB::raw($periodKey), $keys)
            ->when($this->clientId, fn ($q) => $q->where('clients.id', $this->clientId))
            ->when($this->taxType, fn ($q) => $q->where('s.tax_type', $this->taxType))
            ->groupBy(DB::raw($periodKey))
            ->selectRaw("{$periodKey} as period_key, SUM(s.saldo_final) as total")
            ->get()
            ->mapWithKeys(fn ($row) => [$row->period_key => (float) $row->total])
            ->all();
    }

    public function formatCurrency(float $amount): string
    {
        $abs = abs($amount);

        $body = match (true) {
            $abs >= 1_000_000_000 => number_format($abs / 1_000_000_000, 1, ',', '.') . ' M',
            $abs >= 1_000_000 => number_format($abs / 1_000_000, 1, ',', '.') . ' Jt',
            $abs >= 1_000 => number_format($abs / 1_000, 0, ',', '.') . ' Rb',
            default => number_format($abs, 0, ',', '.'),
        };

        return ($amount < 0 ? '-' : '') . 'Rp ' . $body;
    }

    public function describeBalance(float $amount): string
    {
        return match (true) {
            $amount > 0 => 'kurang bayar',
            $amount < 0 => 'lebih bayar',
            default => 'nihil',
        };
    }
}
