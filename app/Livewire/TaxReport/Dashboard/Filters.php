<?php

namespace App\Livewire\TaxReport\Dashboard;

use App\Models\Client;
use App\Services\TaxDeadlineService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Toolbar dashboard laporan pajak.
 *
 * Satu-satunya kontrol waktu di halaman ini adalah periode pelaporan (bulan +
 * tahun), karena kewajiban pajak di Indonesia memang bulanan. Rentang bebas
 * seperti "kuartal ini" atau "tahun lalu" dihapus: agregat status pelaporan
 * lintas bulan tidak bisa ditindaklanjuti, dan tenggat hanya punya arti
 * relatif terhadap satu periode.
 *
 * Komponen ini memegang state, section lain hanya mendengarkan.
 */
class Filters extends Component
{
    /**
     * Nama query di bawah ini adalah kontrak: concern ReadsDashboardFilters
     * membacanya langsung supaya render server pertama tiap section sudah
     * benar tanpa menunggu event dari komponen ini.
     */
    #[Url(as: 'periode', keep: false)]
    public string $period = '';

    #[Url(as: 'klien')]
    public ?string $clientId = null;

    #[Url(as: 'jenis')]
    public ?string $taxType = null;

    #[Url(as: 'lapor')]
    public ?string $reportStatus = null;

    #[Url(as: 'bayar')]
    public ?string $paymentStatus = null;

    public function mount(TaxDeadlineService $deadlines): void
    {
        // Default: periode yang sedang dilaporkan saat ini, yaitu bulan lalu.
        // Pada bulan Juli, yang sedang dikerjakan tim adalah periode Juni.
        if ($this->period === '' || ! $this->isValidPeriod($this->period)) {
            $this->period = $deadlines->periodFor(Carbon::today())->format('Y-m');
        }

        $this->dispatchFilters();
    }

    public function periodDate(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
    }

    public function periodLabel(TaxDeadlineService $deadlines): string
    {
        return $deadlines->periodLabel($this->periodDate());
    }

    public function previousPeriod(): void
    {
        $this->period = $this->periodDate()->subMonth()->format('Y-m');
        $this->dispatchFilters();
    }

    public function nextPeriod(): void
    {
        $this->period = $this->periodDate()->addMonth()->format('Y-m');
        $this->dispatchFilters();
    }

    public function goToCurrentPeriod(TaxDeadlineService $deadlines): void
    {
        $this->period = $deadlines->periodFor(Carbon::today())->format('Y-m');
        $this->dispatchFilters();
    }

    /**
     * Dikirim saat sebuah bulan diklik di chart tren. Periode tetap dipegang
     * komponen ini supaya kontrol waktunya hanya ada satu.
     */
    #[On('periodRequested')]
    public function setPeriod(string $period): void
    {
        if (! $this->isValidPeriod($period)) {
            return;
        }

        $this->period = $period;
        $this->dispatchFilters();
    }

    public function isCurrentPeriod(TaxDeadlineService $deadlines): bool
    {
        return $this->period === $deadlines->periodFor(Carbon::today())->format('Y-m');
    }

    /**
     * Melangkah maju melewati periode berjalan tidak ada gunanya: laporannya
     * belum jatuh tempo, jadi angkanya pasti nol dan hanya membingungkan.
     */
    public function canGoForward(TaxDeadlineService $deadlines): bool
    {
        return $this->periodDate()->lt($deadlines->periodFor(Carbon::today()));
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['clientId', 'taxType', 'reportStatus', 'paymentStatus'], true)) {
            $this->dispatchFilters();
        }
    }

    public function resetFilters(): void
    {
        $this->clientId = null;
        $this->taxType = null;
        $this->reportStatus = null;
        $this->paymentStatus = null;

        $this->dispatchFilters();
    }

    public function activeFilterCount(): int
    {
        return count(array_filter([
            $this->clientId,
            $this->taxType,
            $this->reportStatus,
            $this->paymentStatus,
        ]));
    }

    public function clientOptions(): array
    {
        return Client::query()
            ->where('status', 'Active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function isValidPeriod(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value);
    }

    protected function dispatchFilters(): void
    {
        $this->dispatch('taxFiltersUpdated', filters: [
            'period' => $this->period,
            'client_id' => $this->clientId ?: null,
            'tax_type' => $this->taxType ?: null,
            'report_status' => $this->reportStatus ?: null,
            'payment_status' => $this->paymentStatus ?: null,
        ]);
    }

    public function render()
    {
        return view('livewire.tax-report.dashboard.filters');
    }
}
