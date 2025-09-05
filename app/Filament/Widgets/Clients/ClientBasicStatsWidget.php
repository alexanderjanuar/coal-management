<?php

namespace App\Filament\Widgets\Clients;

use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientBasicStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        // Single query untuk semua data yang dibutuhkan
        $stats = Client::selectRaw('
            COUNT(*) as total_klien,
            SUM(CASE WHEN status = "Active" THEN 1 ELSE 0 END) as klien_aktif,
            SUM(CASE WHEN pkp_status = "PKP" THEN 1 ELSE 0 END) as klien_pkp
        ')->first();

        // Hitung persentase
        $persentase_aktif = $stats->total_klien > 0 
            ? round(($stats->klien_aktif / $stats->total_klien) * 100, 1) 
            : 0;

        $persentase_pkp = $stats->total_klien > 0 
            ? round(($stats->klien_pkp / $stats->total_klien) * 100, 1) 
            : 0;

        return [
            Stat::make('Total Klien', $stats->total_klien)
                ->description('Keseluruhan data klien')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Klien Aktif', $stats->klien_aktif)
                ->description("{$persentase_aktif}% dari total klien")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Klien PKP', $stats->klien_pkp)
                ->description("{$persentase_pkp}% dari total klien")
                ->descriptionIcon('heroicon-m-document-check')
                ->color('info'),
        ];
    }
}