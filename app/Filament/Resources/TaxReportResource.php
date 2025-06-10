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

                // NEW: Invoice Tax Status Column
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
                        return $record->getStatusWithAmount();
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $totalMasuk = $record->getTotalPpnMasuk();
                        $totalKeluar = $record->getTotalPpnKeluar();
                        $selisih = $record->getSelisihPpn();

                        return "Faktur Masuk: Rp " . number_format($totalMasuk, 0, ',', '.') . "\n" .
                            "Faktur Keluar: Rp " . number_format($totalKeluar, 0, ',', '.') . "\n" .
                            "Selisih: Rp " . number_format($selisih, 0, ',', '.');
                    })
                    ->sortable(),

                // Enhanced Invoices Column with Input/Output breakdown
                TextColumn::make('invoices_breakdown')
                    ->label('Faktur (PPN)')
                    ->state(function (TaxReport $record): string {
                        $totalPPN = $record->invoices()->sum('ppn');
                        return "Rp " . number_format($totalPPN, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $invoicesCount = $record->invoices()->count();
                        $ppnMasuk = $record->getTotalPpnMasuk();
                        $ppnKeluar = $record->getTotalPpnKeluar();
                        $masukCount = $record->invoices()->where('type', 'Faktur Masuk')->count();
                        $keluarCount = $record->invoices()->where('type', 'Faktur Keluaran')->count();

                        return "Total {$invoicesCount} faktur\n" .
                            "Masuk ({$masukCount}): Rp " . number_format($ppnMasuk, 0, ',', '.') . "\n" .
                            "Keluar ({$keluarCount}): Rp " . number_format($ppnKeluar, 0, ',', '.');
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum('invoices', 'ppn')
                            ->orderBy('invoices_sum_ppn', $direction);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                // NEW: PPN Difference Column (Faktur Keluar - Faktur Masuk)
                TextColumn::make('ppn_difference_with_compensation')
                    ->label('Selisih PPN (Setelah Kompensasi)')
                    ->state(function (TaxReport $record): string {
                        $selisih = $record->getSelisihPpn();
                        $compensation = $record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;
                        $finalAmount = $selisih - $compensation;

                        if ($finalAmount == 0) {
                            return 'Rp 0';
                        }

                        if ($finalAmount > 0) {
                            return '-Rp ' . number_format($finalAmount, 0, ',', '.'); // Still need to pay
                        } else {
                            return '+Rp ' . number_format(abs($finalAmount), 0, ',', '.'); // Overpaid
                        }
                    })
                    ->color(function (TaxReport $record): string {
                        $selisih = $record->getSelisihPpn();
                        $compensation = $record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;
                        $finalAmount = $selisih - $compensation;

                        if ($finalAmount > 0) {
                            return 'warning'; // Orange - still need to pay
                        } elseif ($finalAmount < 0) {
                            return 'success'; // Green - overpaid
                        } else {
                            return 'gray'; // Gray - balanced
                        }
                    })
                    ->weight('bold')
                    ->tooltip(function (TaxReport $record): string {
                        $ppnMasuk = $record->getTotalPpnMasuk();
                        $ppnKeluar = $record->getTotalPpnKeluar();
                        $selisih = $record->getSelisihPpn();
                        $compensation = $record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;
                        $finalAmount = $selisih - $compensation;

                        $status = '';
                        if ($finalAmount > 0) {
                            $status = 'Masih kurang bayar setelah kompensasi';
                        } elseif ($finalAmount < 0) {
                            $status = 'Lebih bayar setelah kompensasi';
                        } else {
                            $status = 'Seimbang setelah kompensasi';
                        }

                        $tooltip = "Faktur Keluar: Rp " . number_format($ppnKeluar, 0, ',', '.') . "\n" .
                            "Faktur Masuk: Rp " . number_format($ppnMasuk, 0, ',', '.') . "\n" .
                            "Selisih Awal: Rp " . number_format($selisih, 0, ',', '.');

                        if ($compensation > 0) {
                            $tooltip .= "\nKompensasi: Rp " . number_format($compensation, 0, ',', '.');
                        }

                        $tooltip .= "\nSelisih Akhir: Rp " . number_format($finalAmount, 0, ',', '.') . "\n" . $status;

                        return $tooltip;
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum([
                                'invoices as ppn_keluar_sum' => function ($query) {
                                    $query->where('type', 'Faktur Keluaran');
                                }
                            ], 'ppn')
                            ->withSum([
                                'invoices as ppn_masuk_sum' => function ($query) {
                                    $query->where('type', 'Faktur Masuk');
                                }
                            ], 'ppn')
                            ->orderByRaw("((COALESCE(ppn_keluar_sum, 0) - COALESCE(ppn_masuk_sum, 0)) - COALESCE(ppn_dikompensasi_dari_masa_sebelumnya, 0)) {$direction}");
                    }),

                Tables\Columns\IconColumn::make('has_compensation')
                    ->label('Kompensasi')
                    ->state(function (TaxReport $record): string {
                        $hasCompensation = ($record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0) > 0;
                        return $hasCompensation ? 'has_compensation' : 'no_compensation';
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
                        $compensationAmount = $record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;

                        if ($compensationAmount > 0) {
                            // Get source period information
                            $sourceCompensation = $record->compensationsReceived()->with('sourceTaxReport')->first();
                            $sourcePeriod = $sourceCompensation ? $sourceCompensation->sourceTaxReport->month : 'Unknown';

                            return "✓ Dikompensasi Rp " . number_format($compensationAmount, 0, ',', '.') . " dari periode {$sourcePeriod}";
                        }

                        return "Tidak ada kompensasi";
                    })
                    ->alignCenter(),

                TextColumn::make('income_taxes_sum')
                    ->label('PPh 21')
                    ->state(function (TaxReport $record): string {
                        $totalAmount = $record->incomeTaxs()->sum('pph_21_amount');
                        return "Rp " . number_format($totalAmount, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $taxesCount = $record->incomeTaxs()->count();
                        $withBuktiSetor = $record->incomeTaxs()->whereNotNull('bukti_setor')->where('bukti_setor', '!=', '')->count();

                        return "Total {$taxesCount} PPh 21\n" .
                            "Dengan Bukti Setor: {$withBuktiSetor}";
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum('incomeTaxs', 'pph_21_amount')
                            ->orderBy('income_taxs_sum_pph_21_amount', $direction);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bupots_sum')
                    ->label('Bukti Potong')
                    ->state(function (TaxReport $record): string {
                        $totalAmount = $record->bupots()->sum('bupot_amount');
                        return "Rp " . number_format($totalAmount, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $bupotsCount = $record->bupots()->count();
                        $withBuktiSetor = $record->bupots()->whereNotNull('bukti_setor')->where('bukti_setor', '!=', '')->count();

                        return "Total {$bupotsCount} bukti potong\n" .
                            "Dengan Bukti Setor: {$withBuktiSetor}";
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum('bupots', 'bupot_amount')
                            ->orderBy('bupots_sum_bupot_amount', $direction);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_tax')
                    ->label('Total Pajak')
                    ->state(function (TaxReport $record): string {
                        $totalPPH21 = $record->incomeTaxs()->sum('pph_21_amount');
                        $totalPPN = $record->invoices()->sum('ppn');
                        $totalBupot = $record->bupots()->sum('bupot_amount');

                        $total = $totalPPH21 + $totalPPN + $totalBupot;

                        return "Rp " . number_format($total, 0, ',', '.');
                    })
                    ->color('success')
                    ->weight('bold')
                    ->tooltip('Jumlah total dari PPN + PPh 21 + Bukti Potong')
                    ->searchable(false)
                    ->sortable(false)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // NEW: Created by user
                TextColumn::make('created_by')
                    ->label('Dibuat Oleh')
                    ->state(function (TaxReport $record): string {
                        if ($record->created_by) {
                            $user = \App\Models\User::find($record->created_by);
                            return $user ? $user->name : 'User #' . $record->created_by;
                        }
                        return 'System';
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

                // NEW: Invoice Tax Status Filter
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

                // Year filter (assuming you add a year field to your model)
                Tables\Filters\SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        // Get available years from the database, defaulting to last 3 years if none available
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

                // NEW: Completion Status Filter
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

                // Has data filters
                Tables\Filters\Filter::make('has_invoices')
                    ->label('Memiliki Faktur')
                    ->query(fn(Builder $query): Builder => $query->has('invoices')),

                Tables\Filters\Filter::make('has_income_taxes')
                    ->label('Memiliki PPh 21')
                    ->query(fn(Builder $query): Builder => $query->has('incomeTaxs')),

                Tables\Filters\Filter::make('has_bupots')
                    ->label('Memiliki Bukti Potong')
                    ->query(fn(Builder $query): Builder => $query->has('bupots')),

                // NEW: Missing Bukti Setor Filter
                Tables\Filters\Filter::make('missing_bukti_setor')
                    ->label('Kurang Bukti Setor')
                    ->query(function (Builder $query): Builder {
                        return $query->where(function ($query) {
                            $query->whereHas('incomeTaxs', function ($query) {
                                $query->where(function ($query) {
                                    $query->whereNull('bukti_setor')
                                        ->orWhere('bukti_setor', '');
                                });
                            })
                                ->orWhereHas('bupots', function ($query) {
                                    $query->where(function ($query) {
                                        $query->whereNull('bukti_setor')
                                            ->orWhere('bukti_setor', '');
                                    });
                                });
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

                // Amount-based filters
                Tables\Filters\Filter::make('min_total_tax')
                    ->label('Total Pajak Minimal')
                    ->form([
                        Forms\Components\TextInput::make('min_tax')
                            ->label('Minimal (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['min_tax'], function (Builder $query, $amount) {
                            // Use a subquery to filter by the calculated total
                            return $query->whereHas('invoices', function ($query) use ($amount) {
                                $query->select('tax_report_id')
                                    ->groupBy('tax_report_id')
                                    ->havingRaw('SUM(ppn) >= ?', [$amount]);
                            });
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('info'),



                    // Replace the existing apply_compensation action in your TaxReportResource with this simplified version

                    Tables\Actions\Action::make('apply_compensation')
                        ->label(function (TaxReport $record): string {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = $record->getSelisihPpn();
                            $hasExistingCompensation = ($record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0) > 0;

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
                            $currentSelisih = $record->getSelisihPpn();
                            $hasExistingCompensation = ($record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0) > 0;

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
                            $currentSelisih = $record->getSelisihPpn();
                            $hasExistingCompensation = ($record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0) > 0;

                            if ($hasExistingCompensation) {
                                return false;
                            }

                            return $availableCompensations->isEmpty() || $currentSelisih <= 0;
                        })
                        ->form(function (TaxReport $record): array {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = $record->getSelisihPpn();
                            $currentCompensation = $record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;

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

                            // Recalculate status
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

                    // NEW: Bulk Recalculate Status Action
                    Tables\Actions\BulkAction::make('bulk_recalculate_status')
                        ->label('Hitung Ulang Status')
                        ->icon('heroicon-o-calculator')
                        ->color('warning')
                        ->action(function ($records) {
                            $successCount = 0;
                            $errors = [];

                            foreach ($records as $record) {
                                try {
                                    $record->recalculateStatus();
                                    $successCount++;
                                } catch (\Exception $e) {
                                    $errors[] = "Error untuk {$record->client->name}: " . $e->getMessage();
                                }
                            }

                            if ($successCount > 0) {
                                Notification::make()
                                    ->title('Status Berhasil Dihitung Ulang')
                                    ->body("Berhasil menghitung ulang status untuk {$successCount} laporan pajak.")
                                    ->success()
                                    ->send();
                            }

                            if (!empty($errors)) {
                                Notification::make()
                                    ->title('Beberapa Gagal Dihitung')
                                    ->body(implode('<br>', array_slice($errors, 0, 3)))
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Hitung Ulang Status Pembayaran')
                        ->modalDescription('Apakah Anda yakin ingin menghitung ulang status pembayaran untuk semua laporan pajak yang dipilih?'),

                    ExportBulkAction::make()
                        ->label('Ekspor Laporan Pajak (XLSX)')
                        ->icon('heroicon-o-download')
                        ->color('success')
                        ->exporter(\App\Filament\Exports\TaxReportExporter::class),
                ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(TaxReportExporter::class),
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
     * Get available compensations for a tax report
     */
    protected static function getAvailableCompensations(TaxReport $record)
    {
        return TaxReport::where('client_id', $record->client_id)
            ->where('created_at', '<', $record->created_at ?? now())
            ->where('invoice_tax_status', 'Lebih Bayar')
            ->whereRaw('ppn_lebih_bayar_dibawa_ke_masa_depan > ppn_sudah_dikompensasi')
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