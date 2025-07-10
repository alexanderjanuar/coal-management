<?php

namespace App\Filament\Resources;

use App\Filament\Exports\TaxReportExporter;
use App\Filament\Resources\TaxReportResource\Pages;
use App\Filament\Resources\TaxReportResource\RelationManagers;
use App\Filament\Resources\TaxReportResource\RelationManagers\IncomeTaxsRelationManager;
use App\Models\Client;
use App\Models\TaxReport;
use App\Models\TaxCompensation;
use Filament\Tables\Actions\ExportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Support\RawJs;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Closure;
use Filament\Tables\Grouping\Group;
use Maatwebsite\Excel\Excel;
use Swis\Filament\Activitylog\Tables\Actions\ActivitylogAction;

use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Components\Tab;

class TaxReportResource extends Resource
{
    protected static ?string $model = TaxReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $modelLabel = 'Laporan Pajak';

    protected static ?string $navigationGroup = 'Tax';

    public static function shouldRegisterNavigation(): bool
    {
        return !auth()->user()->hasRole('client');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tax Report Information')
                    ->schema([
                        Select::make('client_id')
                            ->label('Client')
                            ->required()
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('NPWP')
                                    ->label('NPWP')
                                    ->maxLength(255),
                                TextInput::make('KPP')
                                    ->label('KPP')
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->unique(ignorable: fn($record) => $record)
                                    ->maxLength(255),
                                Select::make('status')
                                    ->options([
                                        'Active' => 'Active',
                                        'Inactive' => 'Inactive',
                                    ])
                                    ->default('Active'),
                            ]),

                        Select::make('month')
                            ->required()
                            ->native(false)
                            ->options([
                                'January' => 'January',
                                'February' => 'February',
                                'March' => 'March',
                                'April' => 'April',
                                'May' => 'May',
                                'June' => 'June',
                                'July' => 'July',
                                'August' => 'August',
                                'September' => 'September',
                                'October' => 'October',
                                'November' => 'November',
                                'December' => 'December',
                            ]),

                        // Show current compensation info if exists
                        Forms\Components\Placeholder::make('current_compensation_info')
                            ->label('Informasi Kompensasi')
                            ->content(function ($record) {
                                if (!$record || !$record->exists) {
                                    return view('components.tax-reports.tax-compensation-information', [
                                        'record' => null,
                                        'showTitle' => false,
                                        'variant' => 'default'
                                    ]);
                                }

                                return view('components.tax-reports.tax-compensation-information', [
                                    'record' => $record,
                                    'showTitle' => false, // Since the placeholder already has a label
                                    'variant' => 'default'
                                ]);
                            })
                            ->visible(function ($record) {
                                return $record && $record->exists;
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // CRITICAL: Add eager loading to prevent N+1 queries
           ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->with([
                        'client:id,name', // Only load necessary client fields
                        'createdBy:id,name', // Eager load creator user
                        'compensationsReceived.sourceTaxReport:id,month', // Load compensation data
                    ])
                    // Fix: Remove the select() calls from withSum - they're not needed and cause the error
                    ->withSum('invoices', 'ppn')
                    ->withSum([
                        'invoices as ppn_masuk_sum' => function ($query) {
                            $query->where('type', 'Faktur Masuk');
                        }
                    ], 'ppn')
                    ->withSum([
                        'invoices as ppn_keluar_sum' => function ($query) {
                            $query->where('type', 'Faktur Keluaran')
                                ->where(function ($q) {
                                    $q->where('invoice_number', 'NOT LIKE', '02%')
                                        ->where('invoice_number', 'NOT LIKE', '03%')
                                        ->where('invoice_number', 'NOT LIKE', '07%')
                                        ->where('invoice_number', 'NOT LIKE', '08%');
                                });
                        }
                    ], 'ppn')
                    // Tambahan untuk Peredaran Bruto - semua faktur keluaran DPP tanpa filter nomor
                    ->withSum([
                        'invoices as peredaran_bruto_sum' => function ($query) {
                            $query->where('type', 'Faktur Keluaran');
                        }
                    ], 'dpp')
                    ->withSum('incomeTaxs', 'pph_21_amount')
                    ->withSum('bupots', 'bupot_amount')
                    ->withCount([
                        'invoices as total_invoices_count',
                        'invoices as invoices_masuk_count' => function ($query) {
                            $query->where('type', 'Faktur Masuk');
                        },
                        'invoices as invoices_keluar_count' => function ($query) {
                            $query->where('type', 'Faktur Keluaran');
                        },
                        'incomeTaxs as income_taxes_count',
                        'bupots as bupots_count',
                        'incomeTaxs as income_taxes_with_bukti_count' => function ($query) {
                            $query->whereNotNull('bukti_setor')->where('bukti_setor', '!=', '');
                        },
                        'bupots as bupots_with_bukti_count' => function ($query) {
                            $query->whereNotNull('bukti_setor')->where('bukti_setor', '!=', '');
                        }
                    ])
                    // Add computed columns for faster access
                    ->selectRaw('
                        tax_reports.*,
                        COALESCE(ppn_dikompensasi_dari_masa_sebelumnya, 0) as compensation_amount,
                        CASE 
                            WHEN ppn_dikompensasi_dari_masa_sebelumnya > 0 THEN 1 
                            ELSE 0 
                        END as has_compensation_flag
                    ');
            })
            ->columns([
                TextColumn::make('client.name')
                    ->label('Client')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('month')
                    ->label('Periode')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                // Optimized: Use preloaded data instead of method calls
                Tables\Columns\BadgeColumn::make('invoice_tax_status')
                    ->label('Status Pembayaran')
                    ->colors([
                        'success' => 'Lebih Bayar',
                        'warning' => 'Kurang Bayar',
                        'gray' => 'Nihil',
                    ])
                    ->formatStateUsing(function (TaxReport $record): string {
                        if (!$record->invoice_tax_status) {
                            return 'Belum Dihitung';
                        }
                        
                        // Use preloaded sums dengan filter nomor faktur untuk PPN masuk
                        $ppnMasuk = $record->ppn_masuk_sum ?? 0; // Sudah filtered di query
                        $ppnKeluar = $record->ppn_keluar_sum ?? 0;
                        $selisih = $ppnKeluar - $ppnMasuk;
                        
                        if ($selisih == 0) {
                            return 'Nihil';
                        }
                        
                        $amount = number_format(abs($selisih), 0, ',', '.');
                        return $record->invoice_tax_status . ' (Rp ' . $amount . ')';
                    })
                    ->tooltip(function (TaxReport $record): string {
                        // Use preloaded sums dengan penjelasan filter
                        $totalMasuk = $record->ppn_masuk_sum ?? 0; // Sudah exclude 02,03,07,08
                        $totalKeluar = $record->ppn_keluar_sum ?? 0;
                        $selisih = $totalKeluar - $totalMasuk;

                        return "Faktur Masuk: Rp " . number_format($totalMasuk, 0, ',', '.') . "\n" .
                            "Faktur Keluar*: Rp " . number_format($totalKeluar, 0, ',', '.') . "\n" .
                            "Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n\n" .
                            "*Tidak termasuk nomor faktur 02, 03, 07, 08";
                    })
                    ->sortable(),


                // Optimized: Use preloaded sums
                TextColumn::make('invoices_breakdown')
                    ->label('Faktur (PPN)')
                    ->state(function (TaxReport $record): string {
                        $totalPPN = $record->invoices_sum_ppn ?? 0;
                        return "Rp " . number_format($totalPPN, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $invoicesCount = $record->total_invoices_count ?? 0;
                        $ppnMasuk = $record->ppn_masuk_sum ?? 0;
                        $ppnKeluar = $record->ppn_keluar_sum ?? 0;
                        $masukCount = $record->invoices_masuk_count ?? 0;
                        $keluarCount = $record->invoices_keluar_count ?? 0;

                        return "Total {$invoicesCount} faktur\n" .
                            "Masuk ({$masukCount}): Rp " . number_format($ppnMasuk, 0, ',', '.') . "\n" .
                            "Keluar ({$keluarCount}): Rp " . number_format($ppnKeluar, 0, ',', '.');
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('invoices_sum_ppn', $direction);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('peredaran_bruto')
                    ->label('Peredaran Bruto')
                    ->state(function (TaxReport $record): string {
                        $peredaranBruto = $record->peredaran_bruto_sum ?? 0;
                        return "Rp " . number_format($peredaranBruto, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $invoicesCount = $record->invoices_keluar_count ?? 0;
                        return "Total DPP dari {$invoicesCount} faktur keluaran (tanpa filter)";
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('peredaran_bruto_sum', $direction);
                    })
                    ->color('info')
                    ->weight('medium'),

                // Optimized: Use preloaded flag
                Tables\Columns\IconColumn::make('has_compensation')
                    ->label('Kompensasi')
                    ->state(function (TaxReport $record): string {
                        return $record->has_compensation_flag ? 'has_compensation' : 'no_compensation';
                    })
                    ->icons([
                        'heroicon-o-check-circle' => 'has_compensation',
                        'heroicon-o-minus-circle' => 'no_compensation',
                    ])
                    ->colors([
                        'success' => 'has_compensation',
                        'gray' => 'no_compensation',
                    ])
                    ->tooltip(function (TaxReport $record): string {
                        $compensationAmount = $record->compensation_amount ?? 0;

                        if ($compensationAmount > 0) {
                            // Use preloaded relationship data
                            $sourceCompensation = $record->compensationsReceived->first();
                            $sourcePeriod = $sourceCompensation?->sourceTaxReport?->month ?? 'Unknown';

                            return "✓ Dikompensasi Rp " . number_format($compensationAmount, 0, ',', '.') . " dari periode {$sourcePeriod}";
                        }

                        return "Tidak ada kompensasi";
                    })
                    ->alignCenter(),

                // Optimized: Use preloaded sums
                TextColumn::make('income_taxes_sum')
                    ->label('PPh 21')
                    ->state(function (TaxReport $record): string {
                        $totalAmount = $record->income_taxs_sum_pph_21_amount ?? 0;
                        return "Rp " . number_format($totalAmount, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $taxesCount = $record->income_taxes_count ?? 0;
                        $withBuktiSetor = $record->income_taxes_with_bukti_count ?? 0;

                        return "Total {$taxesCount} PPh 21\n" .
                            "Dengan Bukti Setor: {$withBuktiSetor}";
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('income_taxs_sum_pph_21_amount', $direction);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                // Optimized: Use preloaded sums
                TextColumn::make('bupots_sum')
                    ->label('Bukti Potong')
                    ->state(function (TaxReport $record): string {
                        $totalAmount = $record->bupots_sum_bupot_amount ?? 0;
                        return "Rp " . number_format($totalAmount, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $bupotsCount = $record->bupots_count ?? 0;
                        $withBuktiSetor = $record->bupots_with_bukti_count ?? 0;

                        return "Total {$bupotsCount} bukti potong\n" .
                            "Dengan Bukti Setor: {$withBuktiSetor}";
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('bupots_sum_bupot_amount', $direction);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                // Optimized: Use preloaded sums
                TextColumn::make('total_tax')
                    ->label('Total Pajak')
                    ->state(function (TaxReport $record): string {
                        $totalPPH21 = $record->income_taxs_sum_pph_21_amount ?? 0;
                        $totalPPN = $record->invoices_sum_ppn ?? 0;
                        $totalBupot = $record->bupots_sum_bupot_amount ?? 0;

                        $total = $totalPPH21 + $totalPPN + $totalBupot;

                        return "Rp " . number_format($total, 0, ',', '.');
                    })
                    ->color('success')
                    ->weight('bold')
                    ->tooltip('Jumlah total dari PPN + PPh 21 + Bukti Potong')
                    ->searchable(false)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(COALESCE(income_taxs_sum_pph_21_amount, 0) + COALESCE(invoices_sum_ppn, 0) + COALESCE(bupots_sum_bupot_amount, 0)) {$direction}");
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Optimized: Use preloaded relationship
                TextColumn::make('created_by')
                    ->label('Dibuat Oleh')
                    ->state(function (TaxReport $record): string {
                        if ($record->createdBy) {
                            return $record->createdBy->name;
                        }
                        return $record->created_by ? 'User #' . $record->created_by : 'System';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Group::make('client.name')
                    ->label('Client'),
                Group::make('month')
                    ->label('Month'),
                Group::make('invoice_tax_status')
                    ->label('Payment Status'),
            ])
            ->filters([
                // Client filter
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                // Invoice Tax Status Filter
                Tables\Filters\SelectFilter::make('invoice_tax_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'Lebih Bayar' => 'Lebih Bayar',
                        'Kurang Bayar' => 'Kurang Bayar',
                        'Nihil' => 'Nihil',
                    ])
                    ->multiple(),

                // Month/Period filter
                Tables\Filters\SelectFilter::make('month')
                    ->label('Period')
                    ->options([
                        'January' => 'January',
                        'February' => 'February',
                        'March' => 'March',
                        'April' => 'April',
                        'May' => 'May',
                        'June' => 'June',
                        'July' => 'July',
                        'August' => 'August',
                        'September' => 'September',
                        'October' => 'October',
                        'November' => 'November',
                        'December' => 'December',
                    ])
                    ->multiple(),

                // Year filter
                Tables\Filters\SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $years = TaxReport::distinct()
                            ->pluck('created_at')
                            ->map(fn($date) => date('Y', strtotime($date)))
                            ->unique()
                            ->toArray();

                        if (empty($years)) {
                            $currentYear = (int) date('Y');
                            $years = range($currentYear - 2, $currentYear);
                        }

                        return array_combine($years, $years);
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn(Builder $query, $years): Builder => $query->whereYear('created_at', $years)
                            );
                    }),

                // Completion Status Filter
                Tables\Filters\SelectFilter::make('completion_status')
                    ->label('Status Kelengkapan')
                    ->options([
                        'complete' => 'Lengkap',
                        'incomplete' => 'Belum Lengkap',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function (Builder $query, $status) {
                            if ($status === 'complete') {
                                return $query->has('invoices');
                            } else {
                                return $query->doesntHave('invoices');
                            }
                        });
                    }),

                // Date range filter
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('info'),

                    Tables\Actions\Action::make('apply_compensation')
                        ->label(function (TaxReport $record): string {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = $record->getSelisihPpnWithFilter();
                            $hasExistingCompensation = ($record->compensation_amount ?? 0) > 0;

                            if ($hasExistingCompensation) {
                                return 'Kelola Kompensasi';
                            }

                            if ($availableCompensations->isEmpty() || $currentSelisih <= 0) {
                                return 'Tidak Ada Kompensasi';
                            }

                            return 'Terapkan Kompensasi';
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->color(function (TaxReport $record): string {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = ($record->ppn_keluar_sum ?? 0) - ($record->ppn_masuk_sum ?? 0);
                            $hasExistingCompensation = ($record->compensation_amount ?? 0) > 0;

                            if ($hasExistingCompensation) {
                                return 'info';
                            }

                            if ($availableCompensations->isEmpty() || $currentSelisih <= 0) {
                                return 'gray';
                            }

                            return 'success';
                        })
                        ->disabled(function (TaxReport $record): bool {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = ($record->ppn_keluar_sum ?? 0) - ($record->ppn_masuk_sum ?? 0);
                            $hasExistingCompensation = ($record->compensation_amount ?? 0) > 0;

                            if ($hasExistingCompensation) {
                                return false;
                            }

                            return $availableCompensations->isEmpty() || $currentSelisih <= 0;
                        })
                        ->form(function (TaxReport $record): array {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = ($record->ppn_keluar_sum ?? 0) - ($record->ppn_masuk_sum ?? 0);
                            $currentCompensation = $record->compensation_amount ?? 0;

                            // Get existing compensation if any
                            $existingCompensation = $record->compensationsReceived()->first();
                            $selectedSourceId = $existingCompensation ? $existingCompensation->source_tax_report_id : null;
                            $selectedAmount = $existingCompensation ? $existingCompensation->amount_compensated : 0;

                            return [
                                // Single placeholder showing current compensation status
                                Forms\Components\Placeholder::make('compensation_info')
                                    ->label('')
                                    ->content(function (Get $get) use ($record, $currentSelisih, $currentCompensation, $availableCompensations) {
                                        $sourceId = $get('source_tax_report_id');
                                        $amount = (float) ($get('compensation_amount') ?? 0);

                                        $sourceReport = null;
                                        $availableAmount = 0;

                                        if ($sourceId) {
                                            $sourceData = $availableCompensations->firstWhere('id', $sourceId);
                                            if ($sourceData) {
                                                $sourceReport = TaxReport::find($sourceId);
                                                $availableAmount = $sourceData['available_amount'];
                                            }
                                        }

                                        $effectiveAmount = max(0, $currentSelisih - $amount);
                                        $newStatus = 'Kurang Bayar';

                                        if ($amount >= $currentSelisih) {
                                            if ($amount > $currentSelisih) {
                                                $newStatus = 'Lebih Bayar';
                                            } else {
                                                $newStatus = 'Nihil';
                                            }
                                        }

                                        return view('components.tax-reports.simple-compensation-view', [
                                            'record' => $record,
                                            'sourceReport' => $sourceReport,
                                            'currentSelisih' => $currentSelisih,
                                            'compensationAmount' => $amount,
                                            'availableAmount' => $availableAmount,
                                            'effectiveAmount' => $effectiveAmount,
                                            'newStatus' => $newStatus,
                                            'hasValidSelection' => $sourceReport && $amount > 0 && $amount <= $availableAmount
                                        ]);
                                    })
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        Select::make('source_tax_report_id')
                                            ->label('Pilih Periode Sumber')
                                            ->options($availableCompensations->pluck('label', 'id'))
                                            ->required()
                                            ->reactive()
                                            ->default($selectedSourceId)
                                            ->placeholder('Pilih periode yang memiliki kelebihan bayar')
                                            ->afterStateUpdated(function (Set $set, Get $get, $state) use ($availableCompensations, $currentSelisih) {
                                                if ($state) {
                                                    $selected = $availableCompensations->firstWhere('id', $state);
                                                    $availableAmount = $selected['available_amount'] ?? 0;

                                                    // Auto-fill with the needed amount or available amount, whichever is smaller
                                                    $suggestedAmount = min($currentSelisih, $availableAmount);
                                                    $set('compensation_amount', $suggestedAmount);
                                                } else {
                                                    $set('compensation_amount', 0);
                                                }
                                            }),

                                        TextInput::make('compensation_amount')
                                            ->label('Jumlah Kompensasi')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->reactive()
                                            ->default($selectedAmount)
                                            ->placeholder('Masukkan jumlah yang akan dikompensasikan')
                                            ->rules([
                                                fn(Get $get) => function (string $attribute, $value, Closure $fail) use ($get, $availableCompensations) {
                                                    $sourceId = $get('source_tax_report_id');
                                                    if ($sourceId && $value) {
                                                        $selected = $availableCompensations->firstWhere('id', $sourceId);
                                                        $maxAmount = $selected['available_amount'] ?? 0;
                                                        if ($value > $maxAmount) {
                                                            $fail("Jumlah tidak boleh lebih dari Rp " . number_format($maxAmount, 0, ',', '.'));
                                                        }
                                                    }
                                                }
                                            ]),
                                    ]),

                                Textarea::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('Catatan tambahan untuk kompensasi ini...')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ];
                        })
                        ->action(function (TaxReport $record, array $data): void {
                            $sourceReportId = $data['source_tax_report_id'];
                            $compensationAmount = (float) $data['compensation_amount'];
                            $notes = $data['notes'] ?? '';

                            if (!$sourceReportId || $compensationAmount <= 0) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Pilih periode sumber dan masukkan jumlah kompensasi yang valid.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $sourceReport = TaxReport::find($sourceReportId);
                            if (!$sourceReport) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Periode sumber tidak ditemukan.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Check available amount
                            $availableAmount = $sourceReport->ppn_lebih_bayar_dibawa_ke_masa_depan - $sourceReport->ppn_sudah_dikompensasi;
                            if ($compensationAmount > $availableAmount) {
                                Notification::make()
                                    ->title('Error')
                                    ->body("Jumlah kompensasi melebihi yang tersedia: Rp " . number_format($availableAmount, 0, ',', '.'))
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Remove existing compensation if any
                            $record->compensationsReceived()->delete();

                            // Create new compensation
                            TaxCompensation::create([
                                'source_tax_report_id' => $sourceReportId,
                                'target_tax_report_id' => $record->id,
                                'amount_compensated' => $compensationAmount,
                                'notes' => $notes ?: "Kompensasi dari periode {$sourceReport->month}"
                            ]);

                            // Update source report
                            $sourceReport->increment('ppn_sudah_dikompensasi', $compensationAmount);

                            // Update current report
                            $record->update([
                                'ppn_dikompensasi_dari_masa_sebelumnya' => $compensationAmount,
                                'kompensasi_notes' => "Dikompensasi Rp " . number_format($compensationAmount, 0, ',', '.') . " dari {$sourceReport->month}"
                            ]);

                            // Recalculate status using optimized method
                            $currentSelisih = $record->getSelisihPpn();
                            $effectiveAmount = $currentSelisih - $compensationAmount;

                            $newStatus = 'Nihil';
                            if ($effectiveAmount > 0) {
                                $newStatus = 'Kurang Bayar';
                            } elseif ($effectiveAmount < 0) {
                                $newStatus = 'Lebih Bayar';
                                // Set amount available for future compensation
                                $record->update([
                                    'ppn_lebih_bayar_dibawa_ke_masa_depan' => abs($effectiveAmount),
                                    'ppn_sudah_dikompensasi' => 0
                                ]);
                            } else {
                                // Reset future compensation fields for Nihil
                                $record->update([
                                    'ppn_lebih_bayar_dibawa_ke_masa_depan' => 0,
                                    'ppn_sudah_dikompensasi' => 0
                                ]);
                            }

                            $record->update(['invoice_tax_status' => $newStatus]);

                            // Send notification
                            $statusMessage = match ($newStatus) {
                                'Nihil' => "Status berubah menjadi <strong>Nihil</strong> - tidak ada kewajiban tersisa.",
                                'Lebih Bayar' => "Status berubah menjadi <strong>Lebih Bayar</strong> - kelebihan Rp " . number_format(abs($effectiveAmount), 0, ',', '.') . " dapat dikompensasikan ke periode berikutnya.",
                                'Kurang Bayar' => "Status tetap <strong>Kurang Bayar</strong> - masih harus bayar Rp " . number_format($effectiveAmount, 0, ',', '.') . ".",
                            };

                            $statusColor = match ($newStatus) {
                                'Nihil' => 'info',
                                'Lebih Bayar' => 'success',
                                'Kurang Bayar' => 'warning',
                            };

                            Notification::make()
                                ->title('Kompensasi Berhasil Diterapkan')
                                ->body("
                                        <div class='space-y-2'>
                                            <div>✅ Kompensasi <strong>Rp " . number_format($compensationAmount, 0, ',', '.') . "</strong> dari periode {$sourceReport->month} berhasil diterapkan.</div>
                                            <div>📊 {$statusMessage}</div>
                                        </div>
                                    ")
                                ->color($statusColor)
                                ->duration(8000)
                                ->send();
                        })
                        ->modalHeading('Terapkan Kompensasi PPN')
                        ->modalDescription('Terapkan kompensasi dari periode sebelumnya yang memiliki kelebihan bayar.')
                        ->modalWidth('4xl')
                        ->slideOver(),

                    RelationManagerAction::make('PPN')
                        ->label(label: 'Lihat PPN')
                        ->icon('heroicon-o-document-chart-bar')
                        ->color('primary')
                        ->modalWidth('7xl')
                        ->relationManager(RelationManagers\InvoicesRelationManager::make()),

                    RelationManagerAction::make('PPh')
                        ->label('Lihat PPh')
                        ->icon('heroicon-o-receipt-percent')
                        ->color('success')
                        ->modalWidth('7xl')
                        ->relationManager(RelationManagers\IncomeTaxsRelationManager::make()),

                    RelationManagerAction::make('Bupot')
                        ->label('Lihat Bupot')
                        ->icon('heroicon-o-document-check')
                        ->color('danger')
                        ->modalWidth('7xl')
                        ->relationManager(RelationManagers\BupotsRelationManager::make())
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->label('Actions')
                    ->size('sm')
                    ->color('gray')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Ekspor Terpilih ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title('Tidak Ada Data')
                                    ->body('Tidak ada laporan pajak yang dipilih.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            
                            // Load relationships for better performance
                            $taxReports = TaxReport::whereIn('id', $records->pluck('id'))
                                ->with(['client', 'invoices', 'incomeTaxs', 'bupots'])
                                ->get();
                            
                            // Generate filename
                            $count = $taxReports->count();
                            $filename = 'Laporan_Pajak_Terpilih_' . $count . '_items_' . date('Y-m-d_H-i-s') . '.xlsx';
                            
                            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TaxReportExporter($taxReports), $filename);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Ekspor Laporan Pajak Terpilih')
                        ->modalDescription(function (\Illuminate\Support\Collection $records) {
                            $count = $records->count();
                            return "Akan mengekspor {$count} laporan pajak yang terpilih ke file Excel dengan sheet terpisah untuk setiap periode.";
                        })
                        ->modalSubmitActionLabel('Ya, Ekspor')
                        ->deselectRecordsAfterCompletion(),

                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all')
                    ->label('Ekspor Semua ke Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        // Get all tax reports with relationships
                        $taxReports = TaxReport::with(['client', 'invoices', 'incomeTaxs', 'bupots'])->get();
                        
                        if ($taxReports->isEmpty()) {
                            Notification::make()
                                ->title('Tidak Ada Data')
                                ->body('Tidak ada laporan pajak yang tersedia untuk diekspor.')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        // Generate filename with current date
                        $filename = 'Laporan_Pajak_' . date('Y-m-d_H-i-s') . '.xlsx';
                        
                        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TaxReportExporter($taxReports), $filename);
                    })
                    ->tooltip('Ekspor semua laporan pajak ke Excel dengan sheet terpisah per periode')
                    ->requiresConfirmation()
                    ->modalHeading('Ekspor Laporan Pajak')
                    ->modalDescription('Akan mengekspor semua laporan pajak yang tersedia ke file Excel dengan sheet terpisah untuk setiap periode/klien.')
                    ->modalSubmitActionLabel('Ya, Ekspor'),
                
                Tables\Actions\Action::make('export_selected_clients')
                    ->label('Ekspor Klien Tertentu')
                    ->icon('heroicon-o-building-office')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('client_ids')
                            ->label('Pilih Klien')
                            ->multiple()
                            ->required()
                            ->options(function () {
                                return \App\Models\Client::whereHas('taxReports')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->placeholder('Pilih satu atau lebih klien'),
                            
                        Forms\Components\Select::make('months')
                            ->label('Pilih Periode (Opsional)')
                            ->multiple()
                            ->options([
                                'January' => 'January',
                                'February' => 'February', 
                                'March' => 'March',
                                'April' => 'April',
                                'May' => 'May',
                                'June' => 'June',
                                'July' => 'July',
                                'August' => 'August',
                                'September' => 'September',
                                'October' => 'October',
                                'November' => 'November',
                                'December' => 'December',
                            ])
                            ->placeholder('Kosongkan untuk semua periode'),
                            
                        Forms\Components\Select::make('year')
                            ->label('Tahun')
                            ->options(function () {
                                $years = [];
                                for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(date('Y'))
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (array $data) {
                        $query = TaxReport::query()
                            ->whereIn('client_id', $data['client_ids']);
                            
                        if (!empty($data['months'])) {
                            $query->whereIn('month', $data['months']);
                        }
                        
                        if (!empty($data['year'])) {
                            $query->whereYear('created_at', $data['year']);
                        }
                        
                        $taxReports = $query->with(['client', 'invoices', 'incomeTaxs', 'bupots'])->get();
                        
                        if ($taxReports->isEmpty()) {
                            Notification::make()
                                ->title('Tidak Ada Data')
                                ->body('Tidak ada laporan pajak yang ditemukan untuk kriteria yang dipilih.')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        // Generate filename
                        $clientNames = \App\Models\Client::whereIn('id', $data['client_ids'])->pluck('name')->take(2)->implode('_');
                        $year = $data['year'] ?? date('Y');
                        $filename = 'Laporan_Pajak_' . str_replace(' ', '_', $clientNames) . '_' . $year . '.xlsx';
                        
                        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TaxReportExporter($taxReports), $filename);
                    })
                    ->modalWidth('lg')
                    ->modalHeading('Ekspor Laporan Pajak Klien Tertentu')
                    ->modalDescription('Pilih klien dan periode tertentu untuk diekspor.'),
            ])
            ->emptyStateHeading('Belum Ada Laporan Pajak')
            ->emptyStateDescription('Laporan pajak akan muncul di sini setelah Anda membuatnya. Laporan pajak adalah ringkasan dari aktivitas perpajakan bulanan per klien.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Buat Laporan Pajak')
                    ->url(route('filament.admin.resources.tax-reports.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),

                Tables\Actions\Action::make('learn_more')
                    ->label('Pelajari Lebih Lanjut')
                    ->url('https://pajak.go.id/panduan-layanan-pajak/pelaporan-2024#:~:text=Lapor%20pajak%20merupakan%20agenda%20rutin,yang%20telah%20disetorkan%20ke%20negara.')
                    ->color('gray')
                    ->icon('heroicon-o-academic-cap')
                    ->openUrlInNewTab(),
            ]);
    }

    /**
     * Get available compensations for a tax report (optimized version)
     */
    protected static function getAvailableCompensations(TaxReport $record)
    {
        return TaxReport::where('client_id', $record->client_id)
            ->where('created_at', '<', $record->created_at ?? now())
            ->where('invoice_tax_status', 'Lebih Bayar')
            ->whereRaw('ppn_lebih_bayar_dibawa_ke_masa_depan > ppn_sudah_dikompensasi')
            ->withSum([
                'invoices as ppn_masuk_filtered' => function ($query) {
                    $query->where('type', 'Faktur Masuk');
                }
            ], 'ppn')
            ->withSum([
                'invoices as ppn_keluar_sum' => function ($query) {
                    $query->where('type', 'Faktur Keluaran')
                        ->where(function ($q) {
                            $q->where('invoice_number', 'NOT LIKE', '02%')
                                ->where('invoice_number', 'NOT LIKE', '03%')
                                ->where('invoice_number', 'NOT LIKE', '07%')
                                ->where('invoice_number', 'NOT LIKE', '08%');
                        });
                }
            ], 'ppn')
            ->select([
                'id',
                'month',
                'ppn_lebih_bayar_dibawa_ke_masa_depan',
                'ppn_sudah_dikompensasi'
            ])
            ->get()
            ->map(function ($report) {
                $available = $report->ppn_lebih_bayar_dibawa_ke_masa_depan - $report->ppn_sudah_dikompensasi;
                return [
                    'id' => $report->id,
                    'month' => $report->month,
                    'total_lebih_bayar' => $report->ppn_lebih_bayar_dibawa_ke_masa_depan,
                    'already_used' => $report->ppn_sudah_dikompensasi,
                    'available_amount' => $available,
                    'label' => "{$report->month} - Tersedia: Rp " . number_format($available, 0, ',', '.') . " (Total: Rp " . number_format($report->ppn_lebih_bayar_dibawa_ke_masa_depan, 0, ',', '.') . ")"
                ];
            });
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoicesRelationManager::class,
            RelationManagers\IncomeTaxsRelationManager::class,
            RelationManagers\BupotsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'dashboard' => Pages\TaxReportDashboard::route('/dashboard'),
            'index' => Pages\ListTaxReports::route('/'),
            'create' => Pages\CreateTaxReport::route('/create'),
            'view' => Pages\ViewTaxReport::route('/{record}'),
            'edit' => Pages\EditTaxReport::route('/{record}/edit'),
        ];
    }
}