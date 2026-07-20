<?php

namespace App\Livewire\TaxReport\Dashboard;

use App\Services\TaxDeadlineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Daftar triase: klien yang masih menyisakan kewajiban untuk periode terpilih.
 *
 * Menggantikan TopUnreportedClients, yang mengurutkan berdasarkan peredaran
 * bruto. Urutan itu menjawab "siapa yang terbesar", padahal pertanyaan manajer
 * adalah "siapa yang paling mendesak". Di sini urutannya tenggat terdekat dulu,
 * dengan nilai pajak hanya sebagai pemecah seri.
 */
class TriageList extends Component
{
    use Concerns\ReadsDashboardFilters;

    /** Jumlah baris sebelum daftar perlu dibuka penuh. */
    public const PREVIEW_LIMIT = 8;

    public ?string $focused = null;

    public bool $showAll = false;

    public ?string $error = null;

    public function mount(TaxDeadlineService $deadlines): void
    {
        $this->hydrateFiltersFromRequest($deadlines);
    }

    protected function onFiltersUpdated(): void
    {
        $this->showAll = false;
        $this->error = null;
    }

    /**
     * Komponen ini pemilik state sorotan.
     *
     * Garis waktu tenggat duduk di panel ini dan menyaring tabel ini juga, jadi
     * statenya tinggal di sini. Tombol "Sorot di daftar" di panel tenggat cukup
     * mengirim permintaan, lalu ikut menyesuaikan lewat 'deadlineFocusChanged'.
     */
    public function toggleFocus(string $key): void
    {
        $this->applyFocus($this->focused === $key ? null : $key);
    }

    #[On('deadlineFocusRequested')]
    public function focusRequested(string $key): void
    {
        $this->toggleFocus($key);
    }

    public function clearFocus(): void
    {
        $this->applyFocus(null);
    }

    /**
     * Meneruskan permintaan kirim pengingat ke DeadlineSpine.
     *
     * Logikanya (notifikasi ke tiap project manager + banner sitewide) tetap
     * tinggal di sana bersama seluruh perkakas banner-nya; yang pindah ke sini
     * hanya tombolnya. Konfirmasi tetap dipasang di tombol, jadi satu klik saja
     * tidak pernah cukup untuk memicunya.
     */
    public function requestReminder(): void
    {
        if ($this->focused === null) {
            return;
        }

        $this->dispatch('sendDeadlineReminder', key: $this->focused);
    }

    protected function applyFocus(?string $key): void
    {
        $this->focused = $key;
        $this->showAll = false;

        $this->dispatch('deadlineFocusChanged', key: $key);
    }

    public function toggleShowAll(): void
    {
        $this->showAll = ! $this->showAll;
    }

    public function retry(): void
    {
        $this->error = null;
    }

    public function render(TaxDeadlineService $service)
    {
        $period = $this->periodDate();
        $anchor = $service->anchorFor($period);
        $deadlines = collect($service->deadlinesFor($anchor, Carbon::today(), $this->clientId))
            ->keyBy('key');

        try {
            ['rows' => $rows, 'reportCount' => $reportCount] = $this->buildRows($service, $period, $deadlines);
        } catch (\Throwable $e) {
            // Kegagalan query TIDAK boleh menyamar jadi "semua beres". Di surface
            // pengawasan tenggat, daftar kosong palsu jauh lebih berbahaya
            // daripada pesan error.
            Log::error('Gagal memuat daftar triase pajak', [
                'period' => $this->period,
                'exception' => $e,
            ]);

            $this->error = 'Data tidak dapat dimuat, jadi daftar ini belum tentu lengkap.';
            $rows = collect();
            $reportCount = 0;
        }

        $total = $rows->count();
        $visible = $this->showAll ? $rows : $rows->take(self::PREVIEW_LIMIT);

        return view('livewire.tax-report.dashboard.triage-list', [
            'rows' => $visible,
            'total' => $total,
            'hiddenCount' => max(0, $total - $visible->count()),
            'service' => $service,
            'periodDate' => $period,
            'focusedDeadline' => $this->focused ? $deadlines->get($this->focused) : null,
            'hasSecondaryFilters' => (bool) ($this->taxType || $this->reportStatus || $this->paymentStatus),
            // Periode tanpa laporan sama sekali bukan hal yang sama dengan periode
            // yang semua kewajibannya sudah beres. Empty state-nya harus berbeda.
            'periodHasReports' => $reportCount > 0,

            // Tombol aksi per tenggat duduk di panel ini, jadi daftar tenggatnya
            // ikut dikirim. Garis waktunya sendiri tetap di panel tenggat.
            'deadlines' => $deadlines,
            'anchor' => $anchor,
        ]);
    }

    /**
     * Satu query untuk seluruh periode, lalu dikelompokkan di PHP.
     *
     * Versi sebelumnya menjalankan tiga query tambahan per baris di dalam map(),
     * jadi lima baris berarti lima belas query. Di sini jumlahnya tetap satu
     * berapa pun jumlah kliennya.
     */
    protected function buildRows(TaxDeadlineService $service, Carbon $period, $deadlines): array
    {
        $summaries = $service->periodQuery($period, $this->clientId)
            ->select([
                'tax_reports.id as tax_report_id',
                'clients.id as client_id',
                'clients.name as client_name',
                's.tax_type',
                's.report_status',
                's.bayar_status',
                's.status_final',
                's.saldo_final',
            ])
            ->get();

        $rows = $summaries
            ->groupBy('tax_report_id')
            ->map(fn ($group) => $this->buildRow($group, $deadlines))
            ->filter(fn (?array $row) => $row !== null)
            ->sortBy([
                // Paling mendesak lebih dulu. Nilai hanya memecah seri.
                fn ($a, $b) => $a['urgency'] <=> $b['urgency'],
                fn ($a, $b) => $b['value'] <=> $a['value'],
            ])
            ->values();

        return [
            'rows' => $rows,
            'reportCount' => $summaries->pluck('tax_report_id')->unique()->count(),
        ];
    }

    /**
     * Menyusun satu baris klien, atau null kalau tidak ada yang perlu ditindak.
     */
    protected function buildRow($group, $deadlines): ?array
    {
        $first = $group->first();
        $obligations = [];
        $value = 0.0;

        foreach ($group as $summary) {
            $value += (float) ($summary->saldo_final ?? 0);

            if ($summary->report_status === 'Belum Lapor' && $this->wantsReportOf($summary->tax_type)) {
                $obligations[] = [
                    // 'key' adalah tenggat yang menagihnya, 'type' adalah jenis
                    // pajaknya. Keduanya berbeda: PPh dan Bupot sama-sama jatuh
                    // pada tenggat tanggal 10, tapi identitasnya tidak sama.
                    'key' => $summary->tax_type === 'ppn'
                        ? TaxDeadlineService::PPN
                        : TaxDeadlineService::PPH,
                    'type' => $summary->tax_type,
                    'label' => $this->taxTypeLabel($summary->tax_type),
                    'action' => 'Belum lapor',
                ];
            }

            if ($this->isUnpaid($summary) && $this->wantsPaymentOf($summary->tax_type)) {
                $obligations[] = [
                    'key' => TaxDeadlineService::PAYMENT,
                    // Pembayaran bukan jenis pajak, jadi ia tidak punya warna
                    // identitas dan chipnya tetap netral.
                    'type' => null,
                    'label' => 'Bayar',
                    'action' => 'Belum bayar',
                ];
            }
        }

        if ($obligations === []) {
            return null;
        }

        // Chip yang sama bisa muncul dua kali (misalnya PPh dan Bupot sama-sama
        // jatuh pada tenggat tanggal 10), jadi digabung berdasarkan label.
        $obligations = collect($obligations)->unique('label')->values();

        $urgency = $obligations
            ->map(fn ($o) => $deadlines->get($o['key'])['days_remaining'] ?? PHP_INT_MAX)
            ->min();

        // Diurutkan menurut tenggat, bukan menurut urutan baris dari database:
        // PPh dan Bupot (tanggal 10), lalu PPN (20), lalu Bayar (30).
        $obligations = $obligations
            ->map(function (array $obligation) use ($deadlines) {
                $deadline = $deadlines->get($obligation['key']);

                return $obligation + [
                    'tone' => $deadline['tone'] ?? 'neutral',
                    'days_remaining' => $deadline['days_remaining'] ?? null,
                    'date' => $deadline['date'] ?? null,
                ];
            })
            ->sortBy('days_remaining')
            ->values();

        return [
            'tax_report_id' => $first->tax_report_id,
            'client_id' => $first->client_id,
            'client_name' => $first->client_name,
            'obligations' => $obligations,
            'urgency' => $urgency,
            'value' => $value,
            'payment_status' => $first->status_final,
        ];
    }

    /**
     * Filter "Sudah Lapor" secara sengaja mengosongkan daftar ini: daftar triase
     * hanya memuat yang belum beres, jadi menyaring ke yang sudah beres memang
     * tidak menyisakan apa pun. Empty state menjelaskan hal itu.
     */
    protected function wantsReportOf(string $taxType): bool
    {
        if ($this->reportStatus === 'Sudah Lapor') {
            return false;
        }

        if ($this->taxType && $this->taxType !== $taxType) {
            return false;
        }

        if ($this->focused === TaxDeadlineService::PPN) {
            return $taxType === 'ppn';
        }

        if ($this->focused === TaxDeadlineService::PPH) {
            return \in_array($taxType, ['pph', 'bupot'], true);
        }

        return $this->focused === null;
    }

    /**
     * Kewajiban bayar juga tunduk pada filter jenis pajak.
     *
     * bayar_status disimpan per summary, dan summary itu per jenis pajak. Tanpa
     * pengecekan ini, menyaring ke PPN tetap memunculkan chip "Bayar" yang
     * sebenarnya berasal dari summary PPh yang belum dibayar, sehingga barisnya
     * mengaku ada tunggakan bayar PPN padahal tidak.
     */
    protected function wantsPaymentOf(string $taxType): bool
    {
        if ($this->reportStatus === 'Sudah Lapor') {
            return false;
        }

        if ($this->taxType && $this->taxType !== $taxType) {
            return false;
        }

        return $this->focused === null || $this->focused === TaxDeadlineService::PAYMENT;
    }

    protected function isUnpaid(object $summary): bool
    {
        if ($summary->bayar_status !== 'Belum Bayar' || $summary->status_final === 'Nihil') {
            return false;
        }

        return ! $this->paymentStatus || $this->paymentStatus === $summary->status_final;
    }

    /**
     * Label tampilan. Nilai tax_type di database tetap 'pph' dan 'bupot';
     * hanya cara menyebutnya ke pengguna yang berubah.
     */
    protected function taxTypeLabel(string $taxType): string
    {
        return match ($taxType) {
            'ppn' => 'PPN',
            'pph' => 'PPh 21',
            'bupot' => 'PPh Unifikasi',
            default => strtoupper($taxType),
        };
    }

    /**
     * saldo_final bertanda: Kurang Bayar positif, Lebih Bayar negatif, Nihil nol.
     * Tandanya diletakkan di depan "Rp" dan angkanya diformat dari nilai mutlak,
     * karena number_format() pada bilangan negatif menghasilkan "Rp -2,1 M".
     */
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

    public function formatCurrencyFull(float $amount): string
    {
        return ($amount < 0 ? '-' : '') . 'Rp ' . number_format(abs($amount), 0, ',', '.');
    }

    /** Angka bertanda tidak bisa dibaca sendirian, jadi selalu dipasangkan label. */
    public function describeBalance(float $amount): string
    {
        return match (true) {
            $amount > 0 => 'Kurang bayar',
            $amount < 0 => 'Lebih bayar',
            default => 'Nihil',
        };
    }

    public function taxReportUrl(int $id): string
    {
        return route('filament.admin.resources.tax-reports.view', ['record' => $id]);
    }
}
