<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

/**
 * Dashboard Laporan Pajak.
 *
 * Halaman ini sengaja tipis. Seluruh data dan perilakunya ada di komponen
 * Livewire di bawah App\Livewire\TaxReport\Dashboard, yang berkomunikasi lewat
 * event 'taxFiltersUpdated'. Filters memegang satu-satunya state periode.
 */
class DashboardTaxReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Tax Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Dashboard';

    /** Judul dirender oleh komponen Filters agar bisa sebaris dengan pemilih periode. */
    protected static ?string $title = '';

    protected static string $view = 'filament.pages.tax-report.dashboard-tax-report';

    /**
     * Sebelumnya digerbangi 'daily-task.dashboard.*', yang berarti akses ke
     * Dashboard Pajak dikendalikan oleh permission Tugas Harian. Permission
     * 'dashboard-tax-report*' cocok dengan nama kelas ini dan dipegang oleh
     * himpunan role yang sama persis, jadi pertukarannya tidak mengubah siapa
     * pun yang punya akses hari ini.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('dashboard-tax-report*');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('dashboard-tax-report*');
    }
}
