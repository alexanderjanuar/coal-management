<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Tahunan PPN</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #333;
        }

        .container {
            padding: 6px 10px;
        }

        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
            padding: 8px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 10pt;
            color: #666;
            margin-bottom: 15px;
        }

        .stats-box {
            background-color: #f5f5f5;
            border: 1pt solid #ddd;
            padding: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 8pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 0.5pt solid #000;
            padding: 4px 6px;
            font-size: 8pt;
        }

        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt;
        }


        .col-no {
            width: 3%;
            text-align: center;
        }
        .col-month {
            width: 9%;
            text-align: left;
        }
        .col-money {
            width: 20%;
            text-align: right;
        }
        .col-status {
            width: 8%;
            text-align: center;
        }

        .total-row {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
        }
        .total-row td {
            padding: 5px 6px;
            font-size: 9pt;
        }

        .status-kurang-bayar {
            background-color: #FEE;
            color: #C00;
            font-weight: bold;
        }
        .status-lebih-bayar {
            background-color: #EFE;
            color: #060;
            font-weight: bold;
        }
        .status-nihil {
            background-color: #F5F5F5;
            color: #666;
        }

        .summary-section {
            margin-top: 15px;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .summary-table td {
            padding: 5px 8px;
            font-size: 9pt;
        }

        .summary-label {
            font-weight: bold;
            text-align: left;
            width: 70%;
            background-color: #f5f5f5;
        }

        .summary-value {
            text-align: right;
            font-weight: bold;
            width: 30%;
        }

        .section-header {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            font-size: 10pt;
            text-align: center;
            padding: 5px;
            margin-top: 12px;
            margin-bottom: 5px;
        }

        .legend {
            margin-top: 10px;
            font-size: 7pt;
            padding: 5px;
            border: 0.5pt solid #ddd;
            background-color: #fafafa;
        }
        .legend-title {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .legend-item {
            display: inline-block;
            margin-right: 12px;
        }
        .legend-color {
            display: inline-block;
            width: 12px;
            height: 10px;
            border: 0.5pt solid #999;
            vertical-align: middle;
            margin-right: 3px;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7pt;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">
            REKAP TAHUNAN PPN {{ $year }}
        </div>
        <div class="subtitle">
            {{ $clientName }}
        </div>

        <div class="stats-box">
            <strong>Periode Laporan:</strong> {{ $statistics['months_with_reports'] }} dari {{ $statistics['total_months'] }} bulan
        </div>

        <div class="section-header">RINGKASAN BULANAN</div>
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-month">Bulan</th>
                    <th class="col-money">PPN Masuk</th>
                    <th class="col-money">PPN Keluar</th>
                    <th class="col-money">Peredaran Bruto</th>
                    <th class="col-money">Saldo Final</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthlyData as $index => $data)
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-month">{{ $data['month'] }}</td>
                    <td class="col-money">Rp {{ number_format($data['ppn_masuk'], 0, ',', '.') }}</td>
                    <td class="col-money">Rp {{ number_format($data['ppn_keluar'], 0, ',', '.') }}</td>
                    <td class="col-money">Rp {{ number_format($data['peredaran_bruto'], 0, ',', '.') }}</td>
                    <td class="col-money">Rp {{ number_format(abs($data['saldo_final']), 0, ',', '.') }}</td>
                    <td class="col-status 
                        @if($data['status_final'] === 'Kurang Bayar') status-kurang-bayar
                        @elseif($data['status_final'] === 'Lebih Bayar') status-lebih-bayar
                        @else status-nihil
                        @endif">
                        {{ $data['status_final'] }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; font-style: italic;">Tidak ada data laporan</td>
                </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2" style="text-align: center;">TOTAL</td>
                    <td class="col-money">Rp {{ number_format($totals['ppn_masuk'], 0, ',', '.') }}</td>
                    <td class="col-money">Rp {{ number_format($totals['ppn_keluar'], 0, ',', '.') }}</td>
                    <td class="col-money">Rp {{ number_format($totals['peredaran_bruto'], 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <div class="summary-section">
            <div class="section-header">RINGKASAN TAHUNAN</div>
            <table class="summary-table">
                <tbody>
                    <tr>
                        <td class="summary-label">TOTAL PPN MASUK (KREDIT PAJAK)</td>
                        <td class="summary-value">Rp {{ number_format($totals['ppn_masuk'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">TOTAL PPN KELUAR (PAJAK KELUARAN)</td>
                        <td class="summary-value">Rp {{ number_format($totals['ppn_keluar'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">TOTAL PEREDARAN BRUTO</td>
                        <td class="summary-value">Rp {{ number_format($totals['peredaran_bruto'], 0, ',', '.') }}</td>
                    </tr>
                    <tr style="border-top: 2pt solid #000;">
                        <td class="summary-label">TOTAL KURANG BAYAR</td>
                        <td class="summary-value" style="color: #C00;">Rp {{ number_format($totals['kurang_bayar'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">TOTAL LEBIH BAYAR</td>
                        <td class="summary-value" style="color: #060;">Rp {{ number_format($totals['lebih_bayar'], 0, ',', '.') }}</td>
                    </tr>
                    <tr style="border-top: 2pt solid #000;">
                        <td class="summary-label" style="background-color: #4472C4; color: white;">POSISI NETO</td>
                        <td class="summary-value" style="background-color: #4472C4; color: white;">
                            Rp {{ number_format(abs($totals['net_position']), 0, ',', '.') }}
                            @if($totals['net_position'] > 0)
                                (Kurang Bayar)
                            @elseif($totals['net_position'] < 0)
                                (Lebih Bayar)
                            @else
                                (Nihil)
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="legend">
            <div class="legend-title">KETERANGAN STATUS:</div>
            <span class="legend-item">
                <span class="legend-color" style="background-color: #FEE;"></span>Kurang Bayar
            </span>
            <span class="legend-item">
                <span class="legend-color" style="background-color: #EFE;"></span>Lebih Bayar
            </span>
            <span class="legend-item">
                <span class="legend-color" style="background-color: #F5F5F5;"></span>Nihil
            </span>
        </div>

        <div class="footer">
            Dokumen ini dibuat secara otomatis pada {{ date('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
