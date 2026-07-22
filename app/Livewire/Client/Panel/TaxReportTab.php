<?php

namespace App\Livewire\Client\Panel;

use App\Models\Client;
use App\Models\UserClient;
use App\Models\TaxReport;
use App\Models\TaxCalculationSummary;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;

class TaxReportTab extends Component
{
    public $clients = [];
    public $selectedClient = null;
    public $selectedMonth = null;
    public $selectedYear = null;

    public $currentClient = null;
    public $currentTaxReport = null;

    // Pagination tabel "SPT Dilaporkan"
    public int $sptPerPage = 10;
    public int $sptPage = 1;

    // Mode tampilan: 'list' (hanya tabel SPT) atau 'detail' (detail laporan bulan tsb)
    public string $viewMode = 'list';

    // Filter & sort tabel "SPT Dilaporkan"
    public string $sptSearch = '';
    public array $sptJenis = [];          // filter tax_type (kosong = semua)
    public string $sptYear = '';          // filter tahun (kosong = semua)
    public string $sptSort = 'masa';      // masa | jenis | nominal | status
    public string $sptSortDir = 'desc';   // asc | desc
    public string $sptGroupBy = 'jenis';  // jenis | tahun | status | none (default: kelompok per tipe SPT)

    public function mount()
    {
        $this->loadClients();

        // Restore client from session if set by the sidebar switcher
        $sessionClientId = session('client_panel_selected_client_id');
        if ($sessionClientId && $this->clients->contains('id', (int) $sessionClientId)) {
            $this->selectedClient = (int) $sessionClientId;
        } elseif ($this->clients->isNotEmpty()) {
            $this->selectedClient = $this->clients->first()->id;
        }

        // Auto-select current/latest month
        $this->selectedYear = now()->format('Y');
        if ($this->selectedClient) {
            $this->loadClientData($this->selectedClient);

            if ($this->currentTaxReport) {
                $this->selectedMonth = $this->currentTaxReport->month;
                $this->selectedYear = $this->currentTaxReport->created_at->format('Y');
            }
        }
    }

    public function loadClients()
    {
        // Get all clients linked to current user
        $clientIds = UserClient::where('user_id', auth()->id())
            ->pluck('client_id');

        if ($clientIds->isEmpty()) {
            $this->clients = collect([]);
            return;
        }

        $this->clients = Client::whereIn('id', $clientIds)
            ->with(['pic', 'accountRepresentative'])
            ->orderBy('name')
            ->get();
    }

    #[On('client-switched')]
    public function selectClient($clientId)
    {
        $this->selectedClient = $clientId;
        $this->sptPage = 1;
        $this->viewMode = 'list';
        $this->resetSptFilters();
        $this->loadClientData($clientId);

        // Emit event to child components to refresh their data
        if ($this->currentTaxReport) {
            $this->dispatch('taxReportChanged', $this->currentTaxReport->id);
        }
    }

    public function selectMonth($month)
    {
        $this->selectedMonth = $month;
        $this->loadMonthData($month);

        // Emit event to child components to refresh their data
        if ($this->currentTaxReport) {
            $this->dispatch('taxReportChanged', $this->currentTaxReport->id);
        }
    }

    public function selectYear($year)
    {
        $this->selectedYear = $year;

        // Reload data for the selected year
        if ($this->selectedMonth) {
            $this->loadMonthData($this->selectedMonth);
        } else {
            $this->loadClientData($this->selectedClient);
        }

        // Emit event to child components to refresh their data
        if ($this->currentTaxReport) {
            $this->dispatch('taxReportChanged', $this->currentTaxReport->id);
        }
    }

    /**
     * Get available years for the current client
     */
    public function getAvailableYearsProperty()
    {
        if (!$this->currentClient) {
            return collect([now()->format('Y')]);
        }

        $years = TaxReport::where('client_id', $this->currentClient->id)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // If no years found, return current year
        if ($years->isEmpty()) {
            return collect([now()->format('Y')]);
        }

        return $years;
    }

    public function loadClientData($clientId)
    {
        $this->currentClient = Client::find($clientId);

        if (!$this->currentClient) {
            $this->currentTaxReport = null;
            return;
        }

        // Get the most recent tax report for this client
        $this->currentTaxReport = TaxReport::where('client_id', $clientId)
            ->with([
                'invoices',
                'incomeTaxs',
                'bupots',
                'taxCalculationSummaries' => function ($query) {
                    $query->select('id', 'tax_report_id', 'tax_type', 'report_status');
                }
            ])
            ->latest('created_at')
            ->first();

        if ($this->currentTaxReport) {
            $this->selectedMonth = $this->currentTaxReport->month;
        }
    }

    public function loadMonthData($month)
    {
        if (!$this->currentClient) {
            return;
        }

        // Use selected year or current year
        $currentYear = $this->selectedYear ?? now()->format('Y');

        $this->currentTaxReport = TaxReport::where('client_id', $this->currentClient->id)
            ->where('month', $month)
            ->whereYear('created_at', $currentYear)
            ->with([
                'invoices',
                'incomeTaxs',
                'bupots',
                'taxCalculationSummaries' => function ($query) {
                    $query->select('id', 'tax_report_id', 'tax_type', 'report_status');
                }
            ])
            ->first();

        // If no report found for this month, try to get the latest one
        if (!$this->currentTaxReport) {
            $this->currentTaxReport = TaxReport::where('client_id', $this->currentClient->id)
                ->whereYear('created_at', $currentYear)
                ->with([
                    'invoices',
                    'incomeTaxs',
                    'bupots',
                    'taxCalculationSummaries' => function ($query) {
                        $query->select('id', 'tax_report_id', 'tax_type', 'report_status');
                    }
                ])
                ->latest('created_at')
                ->first();
        }
    }

    #[On('refresh-data')]
    public function refreshData()
    {
        $this->loadClients();
        if ($this->selectedClient) {
            if ($this->selectedMonth) {
                $this->loadMonthData($this->selectedMonth);
            } else {
                $this->loadClientData($this->selectedClient);
            }
        }

        // Emit event to child components after refresh
        if ($this->currentTaxReport) {
            $this->dispatch('taxReportChanged', $this->currentTaxReport->id);
        }
    }

    /**
     * Daftar SPT yang sudah dilaporkan untuk klien aktif (lintas bulan & tahun),
     * untuk tabel ringkasan "SPT Dilaporkan".
     */
    public function getReportedSptsProperty()
    {
        if (!$this->currentClient) {
            return collect();
        }

        $monthsEn = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $monthIndex = array_flip($monthsEn);
        $monthsId = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April',
            'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
            'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
        ];
        $jenisLabels = [
            'ppn'       => 'SPT Masa PPN',
            'pph'       => 'SPT Masa PPh 21',
            'bupot'     => 'SPT Masa PPh Unifikasi',
            'pph_badan' => 'SPT PPh Badan',
        ];

        return TaxCalculationSummary::query()
            ->whereHas('taxReport', fn ($q) => $q->where('client_id', $this->currentClient->id))
            ->where(fn ($q) => $q->where('report_status', 'Sudah Lapor')->orWhereNotNull('bukti_lapor'))
            // Masa yang ditandai tanpa aktivitas berstatus "Sudah Lapor" supaya
            // perhitungan internal tetap bekerja, tapi tidak ada SPT yang pernah
            // dilaporkan. Menampilkannya di sini akan membuat klien melihat SPT
            // yang tidak pernah ada dan mencari dokumennya.
            ->where('no_activity', false)
            ->with(['taxReport:id,month,client_id,created_at'])
            ->get()
            ->map(function ($s) use ($monthIndex, $monthsId, $jenisLabels) {
                $report = $s->taxReport;
                if (!$report) {
                    return null;
                }
                $year = $report->created_at?->format('Y');
                $monthEn = $report->month;
                $nominal = $s->saldo_final ?? $s->selisih ?? 0;

                return [
                    'id'         => $s->id,
                    'report_id'  => $report->id,
                    'type'       => $s->tax_type,
                    'jenis'      => $jenisLabels[$s->tax_type] ?? strtoupper((string) $s->tax_type),
                    'masa'       => ($monthsId[$monthEn] ?? $monthEn) . ' ' . $year,
                    'month'      => $monthEn,
                    'year'       => $year,
                    'pembetulan' => 0,
                    'nominal'    => abs((float) $nominal),
                    'paid'       => $s->bayar_status === 'Sudah Bayar',
                    'fileUrl'    => $s->bukti_lapor ? Storage::disk('public')->url($s->bukti_lapor) : null,
                    'nomor'      => $s->nomor_bukti_lapor,
                    'sort'       => ((int) $year) * 100 + ($monthIndex[$monthEn] ?? 0),
                ];
            })
            ->filter()
            ->sortByDesc('sort')
            ->values();
    }

    public function updatedSptPerPage()
    {
        $this->sptPage = 1;
    }

    public function updatedSptSearch()
    {
        $this->sptPage = 1;
    }

    public function updatedSptJenis()
    {
        $this->sptPage = 1;
    }

    public function updatedSptYear()
    {
        $this->sptPage = 1;
    }

    /** Ubah kolom/arah sort tabel SPT. */
    public function sortBy($column)
    {
        if ($this->sptSort === $column) {
            $this->sptSortDir = $this->sptSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sptSort = $column;
            $this->sptSortDir = $column === 'jenis' ? 'asc' : 'desc';
        }
        $this->sptPage = 1;
    }

    public function resetSptFilters()
    {
        $this->sptSearch = '';
        $this->sptJenis = [];
        $this->sptYear = '';
        $this->sptPage = 1;
    }

    public function setSptYear($year)
    {
        $this->sptYear = (string) $year;
        $this->sptPage = 1;
    }

    public function setSptGroup($group)
    {
        $this->sptGroupBy = in_array($group, ['jenis', 'tahun', 'status', 'none'], true) ? $group : 'jenis';
        $this->sptPage = 1;
    }

    /** Opsi filter Jenis SPT — hanya jenis yang ada datanya. */
    public function getSptJenisOptionsProperty()
    {
        $labels = [
            'ppn'       => 'SPT Masa PPN',
            'pph'       => 'SPT Masa PPh 21',
            'bupot'     => 'SPT Masa PPh Unifikasi',
            'pph_badan' => 'SPT PPh Badan',
        ];
        $present = $this->reportedSpts->pluck('type')->unique()->all();

        return collect($labels)->only($present);
    }

    /** Tahun yang tersedia untuk filter. */
    public function getSptYearsProperty()
    {
        return $this->reportedSpts->pluck('year')->filter()->unique()->sortDesc()->values();
    }

    /** SPT setelah search + filter + sort (sebelum pagination). */
    public function getFilteredSptsProperty()
    {
        $rows = $this->reportedSpts;
        $search = trim(mb_strtolower($this->sptSearch));

        if ($search !== '') {
            $rows = $rows->filter(fn ($r) => str_contains(
                mb_strtolower($r['jenis'] . ' ' . $r['masa'] . ' ' . ($r['nomor'] ?? '')),
                $search
            ));
        }
        if (!empty($this->sptJenis)) {
            $rows = $rows->filter(fn ($r) => in_array($r['type'], $this->sptJenis, true));
        }
        if ($this->sptYear !== '') {
            $rows = $rows->filter(fn ($r) => (string) $r['year'] === $this->sptYear);
        }

        $rows = $rows->sortBy(function ($r) {
            return match ($this->sptSort) {
                'jenis'   => $r['jenis'],
                'nominal' => $r['nominal'],
                'status'  => ($r['paid'] ? 1 : 0),
                default   => $r['sort'], // masa (kronologis)
            };
        }, SORT_REGULAR, $this->sptSortDir === 'desc');

        // Kelompokkan: jadikan grup primer (sortBy stabil menjaga urutan sort di dalam grup)
        if ($this->sptGroupBy !== 'none') {
            $rows = $rows->sortBy(function ($r) {
                return match ($this->sptGroupBy) {
                    'tahun'  => -(int) $r['year'],   // tahun terbaru dulu
                    'status' => $r['paid'] ? 0 : 1,  // sudah bayar dulu
                    default  => $r['jenis'],         // tipe SPT (alfabetis)
                };
            }, SORT_REGULAR, false);
        }

        return $rows->values();
    }

    public function gotoSptPage($page)
    {
        $this->sptPage = max(1, (int) $page);
    }

    /**
     * Buka detail laporan pajak untuk masa SPT yang dipilih (inline, seperti klik bulan).
     */
    public function openSptDetail($month, $year)
    {
        $this->selectedYear = (string) $year;
        $this->selectedMonth = $month;
        $this->loadMonthData($month);
        $this->viewMode = 'detail';

        if ($this->currentTaxReport) {
            $this->dispatch('taxReportChanged', $this->currentTaxReport->id);
        }

        $this->dispatch('spt-detail-opened');
    }

    /** Kembali dari detail laporan ke daftar SPT. */
    public function backToSptList()
    {
        $this->viewMode = 'list';
        $this->dispatch('spt-detail-opened'); // scroll ke atas
    }

    public function render()
    {
        return view('livewire.client.panel.tax-report-tab');
    }
}