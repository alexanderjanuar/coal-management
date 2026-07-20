<?php

namespace App\Livewire\TaxReport\Dashboard;

use App\Models\Client;
use App\Services\TaxDeadlineService;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
class Filters extends Component implements HasForms
{
    use InteractsWithForms;

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

        // Diisi dengan nilai yang sudah ada, bukan fill() kosong: properti ini
        // mungkin sudah terisi dari query string lewat #[Url], dan fill() tanpa
        // argumen akan mengembalikannya ke default dan membuang filter yang
        // dibawa URL.
        $this->form->fill([
            'clientId' => $this->clientId,
            'taxType' => $this->taxType,
            'reportStatus' => $this->reportStatus,
            'paymentStatus' => $this->paymentStatus,
        ]);

        $this->dispatchFilters();
    }

    /**
     * Sengaja TANPA statePath().
     *
     * Tanpa statePath, Filament mengikat tiap field langsung ke properti publik
     * komponen, sehingga atribut #[Url] di atas tetap bekerja. Kalau state
     * dipindah ke array $data seperti pola statePath biasa, seluruh parameter
     * query (?klien=, ?jenis=, ...) putus, dan concern ReadsDashboardFilters
     * yang dipakai keempat section lain ikut kehilangan sumbernya.
     */
    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make()
                ->schema([
                    Select::make('clientId')
                        ->label('Klien')
                        ->placeholder('Semua klien')
                        ->options(fn () => $this->clientOptions())
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn () => $this->dispatchFilters()),

                    Select::make('taxType')
                        ->label('Jenis pajak')
                        ->placeholder('Semua jenis')
                        // Kunci tetap nilai tax_type di database.
                        ->options([
                            'ppn' => 'PPN',
                            'pph' => 'PPh 21',
                            'bupot' => 'PPh Unifikasi',
                        ])
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn () => $this->dispatchFilters()),

                    Select::make('reportStatus')
                        ->label('Status lapor')
                        ->placeholder('Semua status lapor')
                        ->options([
                            'Belum Lapor' => 'Belum Lapor',
                            'Sudah Lapor' => 'Sudah Lapor',
                        ])
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn () => $this->dispatchFilters()),

                    Select::make('paymentStatus')
                        ->label('Status bayar')
                        ->placeholder('Semua status bayar')
                        ->options([
                            'Kurang Bayar' => 'Kurang Bayar',
                            'Lebih Bayar' => 'Lebih Bayar',
                            'Nihil' => 'Nihil',
                        ])
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn () => $this->dispatchFilters()),
                ])
                ->columns([
                    'default' => 1,
                    'sm' => 2,
                    'lg' => 4,
                ]),
        ]);
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

    /**
     * Dikirim saat satu jenis pajak diklik di panel kelengkapan. State-nya tetap
     * dipegang komponen ini supaya URL dan seluruh section ikut menyesuaikan.
     */
    #[On('taxTypeRequested')]
    public function setTaxType(?string $taxType): void
    {
        if ($taxType !== null && ! \in_array($taxType, ['ppn', 'pph', 'bupot'], true)) {
            return;
        }

        $this->taxType = $taxType;

        // Form Filament perlu diisi ulang: mengubah properti saja tidak
        // menyegarkan state internalnya, dan select-nya akan tetap
        // menampilkan pilihan yang lama.
        $this->form->fill([
            'clientId' => $this->clientId,
            'taxType' => $this->taxType,
            'reportStatus' => $this->reportStatus,
            'paymentStatus' => $this->paymentStatus,
        ]);

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

    public function resetFilters(): void
    {
        $this->clientId = null;
        $this->taxType = null;
        $this->reportStatus = null;
        $this->paymentStatus = null;

        // Form-nya ikut diisi ulang: mengubah properti saja tidak menyegarkan
        // state internal Filament, dan select-nya akan tetap menampilkan nilai lama.
        $this->form->fill([
            'clientId' => null,
            'taxType' => null,
            'reportStatus' => null,
            'paymentStatus' => null,
        ]);

        $this->dispatchFilters();
    }

    public function activeFilterCount(): int
    {
        return \count(array_filter([
            $this->clientId,
            $this->taxType,
            $this->reportStatus,
            $this->paymentStatus,
        ]));
    }

    /**
     * Hanya klien aktif yang punya minimal satu kontrak.
     *
     * Aturan yang sama dipakai form pembuatan laporan di TaxReportResource.
     * Klien tanpa kontrak tidak akan pernah punya kewajiban di dashboard ini,
     * jadi memilihnya hanya menghasilkan tampilan kosong.
     */
    public function clientOptions(): array
    {
        return Client::query()
            ->where('status', 'Active')
            ->where(fn ($q) => $q
                ->where('ppn_contract', true)
                ->orWhere('pph_contract', true)
                ->orWhere('bupot_contract', true)
                ->orWhere('pph_badan_contract', true))
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
