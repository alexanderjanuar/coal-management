<?php

namespace App\Livewire\TaxReport;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\TaxReport;

class TaxReportTypeChart extends ApexChartWidget
{
    protected static ?string $chartId = 'TaxReportTypeChart';
    
    protected static ?string $heading = 'Laporan Belum Dilaporkan';
    
    protected static ?string $subheading = 'Jumlah laporan PPN, PPh, dan Bupot yang belum dilaporkan';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public ?string $filter = 'all';

    protected function getOptions(): array
    {
        // Build base query
        $query = TaxReport::query();

        // Apply year filter if selected
        if ($this->filter !== 'all') {
            $query->whereYear('created_at', $this->filter);
        }

        // Get counts for reports that are not yet filed (Belum Lapor)
        $ppnBelumLapor = (clone $query)->where('ppn_report_status', 'Belum Lapor')->count();
        $pphBelumLapor = (clone $query)->where('pph_report_status', 'Belum Lapor')->count();
        $bupotBelumLapor = (clone $query)->where('bupot_report_status', 'Belum Lapor')->count();

        // Create arrays for series and labels
        $series = [$ppnBelumLapor, $pphBelumLapor, $bupotBelumLapor];
        $labels = ['PPN Belum Lapor', 'PPh Belum Lapor', 'Bupot Belum Lapor'];

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 400,
            ],
            'series' => $series,
            'labels' => $labels,
            'colors' => [
                '#ef4444', // PPN - red-500
                '#f97316', // PPh - orange-500  
                '#a855f7', // Bupot - violet-500
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '60%',
                        'expandOnClick' => true,
                        'labels' => [
                            'show' => true,
                            'name' => [
                                'show' => true,
                                'fontSize' => '14px',
                                'fontWeight' => 'bold',
                            ],
                            'value' => [
                                'show' => true,
                                'fontSize' => '20px',
                                'fontWeight' => '600',
                                'formatter' => 'function (val) { return val }'
                            ],
                            'total' => [
                                'show' => true,
                                'showAlways' => true,
                                'fontSize' => '16px',
                                'fontWeight' => 'bold',
                                'label' => 'Total Tertunda',
                                'formatter' => 'function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0) }'
                            ]
                        ]
                    ]
                ]
            ],
            'legend' => [
                'position' => 'bottom',
                'horizontalAlign' => 'center',
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val, opts) { 
                        const total = opts.w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                        return val + " Laporan (" + percentage + "%)";
                    }'
                ]
            ],
            'dataLabels' => [
                'enabled' => true,
                'formatter' => 'function (val, opts) {
                    const value = opts.w.globals.series[opts.seriesIndex];
                    if (value === 0) return "";
                    return value;
                }',
                'style' => [
                    'fontSize' => '14px',
                    'fontWeight' => 'bold',
                    'colors' => ['#fff']
                ],
                'dropShadow' => [
                    'enabled' => true,
                    'color' => '#000',
                    'blur' => 2,
                ]
            ],
            'responsive' => [
                [
                    'breakpoint' => 480,
                    'options' => [
                        'chart' => [
                            'height' => 300
                        ],
                        'legend' => [
                            'position' => 'bottom'
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Semua Tahun',
            date('Y') => 'Tahun ' . date('Y'),
            date('Y') - 1 => 'Tahun ' . (date('Y') - 1),
            date('Y') - 2 => 'Tahun ' . (date('Y') - 2),
        ];
    }
}