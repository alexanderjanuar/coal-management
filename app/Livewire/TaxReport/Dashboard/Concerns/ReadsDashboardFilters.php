<?php

namespace App\Livewire\TaxReport\Dashboard\Concerns;

use App\Services\TaxDeadlineService;
use Carbon\Carbon;
use Livewire\Attributes\On;

/**
 * State filter bersama untuk section-section dashboard pajak yang hanya membaca.
 *
 * Filters memegang state dan menulis ke URL. Section lain memakai concern ini
 * untuk membaca state yang sama.
 *
 * Kenapa membaca query string langsung, bukan sekadar menunggu event:
 * Filters::mount() memang men-dispatch 'taxFiltersUpdated', tapi pada render
 * server pertama event itu belum pernah terjadi. Tiap section akan merender
 * default-nya sendiri lebih dulu, dan baru ikut berubah setelah satu putaran
 * request. Akibatnya membuka /dashboard-tax-report?periode=2025-08 menampilkan
 * header Agustus 2025 di atas tulang punggung yang masih menunjukkan periode
 * berjalan. Membaca query di mount() membuat render pertama langsung benar.
 *
 * Nama query sengaja sama persis dengan atribut #[Url] di Filters.
 */
trait ReadsDashboardFilters
{
    public string $period = '';

    public ?int $clientId = null;

    public ?string $taxType = null;

    public ?string $reportStatus = null;

    public ?string $paymentStatus = null;

    protected function hydrateFiltersFromRequest(TaxDeadlineService $deadlines): void
    {
        $period = request()->query('periode');

        $this->period = (is_string($period) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period))
            ? $period
            : $deadlines->periodFor(Carbon::today())->format('Y-m');

        $client = request()->query('klien');
        $this->clientId = is_numeric($client) ? (int) $client : null;

        $this->taxType = $this->queryIn('jenis', ['ppn', 'pph', 'bupot']);
        $this->reportStatus = $this->queryIn('lapor', ['Belum Lapor', 'Sudah Lapor']);
        $this->paymentStatus = $this->queryIn('bayar', ['Kurang Bayar', 'Lebih Bayar', 'Nihil']);
    }

    /** Nilai query hanya diterima kalau termasuk daftar yang sah. */
    protected function queryIn(string $key, array $allowed): ?string
    {
        $value = request()->query($key);

        return (is_string($value) && \in_array($value, $allowed, true)) ? $value : null;
    }

    #[On('taxFiltersUpdated')]
    public function updateFilters(array $filters): void
    {
        $this->period = $filters['period'];
        $this->clientId = $filters['client_id'] ? (int) $filters['client_id'] : null;
        $this->taxType = $filters['tax_type'];
        $this->reportStatus = $filters['report_status'];
        $this->paymentStatus = $filters['payment_status'];

        $this->onFiltersUpdated();
    }

    /** Kail opsional untuk section yang perlu mereset state lokalnya. */
    protected function onFiltersUpdated(): void
    {
        //
    }

    protected function periodDate(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
    }
}
