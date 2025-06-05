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
                                    ->unique(ignorable: fn ($record) => $record)
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
                TextColumn::make('ppn_difference')
                    ->label('Selisih PPN')
                    ->state(function (TaxReport $record): string {
                            $selisih = $record->getSelisihPpn();
                            
                            if ($selisih == 0) {
                                return 'Rp 0';
                            }
                            
                            if ($record->invoice_tax_status === 'Lebih Bayar') {
                                return '+Rp ' . number_format(abs($selisih), 0, ',', '.');
                            } elseif ($record->invoice_tax_status === 'Kurang Bayar') {
                                return '-Rp ' . number_format(abs($selisih), 0, ',', '.');
                            }
                            
                            // Fallback: use the actual calculation if status not set
                            if ($selisih > 0) {
                                return '-Rp ' . number_format($selisih, 0, ',', '.'); // Positive selisih = need to pay more
                            } else {
                                return '+Rp ' . number_format(abs($selisih), 0, ',', '.'); // Negative selisih = overpaid
                            }
                        })
                    ->color(function (TaxReport $record): string {
                        $selisih = $record->getSelisihPpn();
                        
                        if ($selisih > 0) {
                            return 'warning'; // Orange - need to pay
                        } elseif ($selisih < 0) {
                            return 'success'; // Green - overpaid/refund
                        } else {
                            return 'gray'; // Gray - balanced
                        }
                    })
                    ->weight('bold')
                    ->tooltip(function (TaxReport $record): string {
                        $ppnMasuk = $record->getTotalPpnMasuk();
                        $ppnKeluar = $record->getTotalPpnKeluar();
                        $selisih = $record->getSelisihPpn();
                        
                        $status = '';
                        if ($selisih > 0) {
                            $status = 'Kurang bayar - perlu bayar tambahan';
                        } elseif ($selisih < 0) {
                            $status = 'Lebih bayar - bisa klaim restitusi';
                        } else {
                            $status = 'Seimbang - nihil';
                        }
                        
                        return "Faktur Keluar: Rp " . number_format($ppnKeluar, 0, ',', '.') . "\n" .
                            "Faktur Masuk: Rp " . number_format($ppnMasuk, 0, ',', '.') . "\n" .
                            "Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n" .
                            $status;
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum(['invoices as ppn_keluar_sum' => function ($query) {
                                $query->where('type', 'Faktur Keluaran');
                            }], 'ppn')
                            ->withSum(['invoices as ppn_masuk_sum' => function ($query) {
                                $query->where('type', 'Faktur Masuk');
                            }], 'ppn')
                            ->orderByRaw("(COALESCE(ppn_keluar_sum, 0) - COALESCE(ppn_masuk_sum, 0)) {$direction}");
                    }),
                    
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

                // NEW: Completion Status Column
                Tables\Columns\IconColumn::make('completion_status')
                    ->label('Kelengkapan')
                    ->state(function (TaxReport $record): string {
                        $hasInvoices = $record->invoices()->exists();
                        $hasIncomeTax = $record->incomeTaxs()->exists();
                        $hasBupots = $record->bupots()->exists();
                        
                        // Consider complete if has at least invoices (minimum requirement)
                        $isComplete = $hasInvoices;
                        
                        return $isComplete ? 'complete' : 'incomplete';
                    })
                    ->icons([
                        'heroicon-o-check-circle' => 'complete',
                        'heroicon-o-exclamation-triangle' => 'incomplete',
                    ])
                    ->colors([
                        'success' => 'complete',
                        'warning' => 'incomplete',
                    ])
                    ->tooltip(function (TaxReport $record): string {
                        $hasInvoices = $record->invoices()->exists();
                        $hasIncomeTax = $record->incomeTaxs()->exists();
                        $hasBupots = $record->bupots()->exists();
                        
                        $status = [];
                        $status[] = $hasInvoices ? '✓ Faktur' : '✗ Faktur';
                        $status[] = $hasIncomeTax ? '✓ PPh 21' : '✗ PPh 21';
                        $status[] = $hasBupots ? '✓ Bukti Potong' : '✗ Bukti Potong';
                        
                        return implode("\n", $status);
                    }),
                    
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
                            ->map(fn ($date) => date('Y', strtotime($date)))
                            ->unique()
                            ->toArray();
                        
                        if (empty($years)) {
                            $currentYear = (int)date('Y');
                            $years = range($currentYear - 2, $currentYear);
                        }
                        
                        return array_combine($years, $years);
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn (Builder $query, $years): Builder => $query->whereYear('created_at', $years)
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
                    ->query(fn (Builder $query): Builder => $query->has('invoices')),
                    
                Tables\Filters\Filter::make('has_income_taxes')
                    ->label('Memiliki PPh 21')
                    ->query(fn (Builder $query): Builder => $query->has('incomeTaxs')),
                    
                Tables\Filters\Filter::make('has_bupots')
                    ->label('Memiliki Bukti Potong')
                    ->query(fn (Builder $query): Builder => $query->has('bupots')),

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
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
                    
                    // NEW: Apply Compensation Action
                    Tables\Actions\Action::make('apply_compensation')
                        ->label(function (TaxReport $record): string {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = $record->getSelisihPpn();
                            $hasExistingCompensation = ($record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0) > 0;
                            
                            // If already has compensation
                            if ($hasExistingCompensation) {
                                return 'Kelola Kompensasi';
                            }
                            
                            // If no compensation available
                            if ($availableCompensations->isEmpty()) {
                                return 'Tidak Ada Kompensasi';
                            }
                            
                            // If current report doesn't need compensation (nihil or lebih bayar)
                            if ($currentSelisih <= 0) {
                                return 'Tidak Perlu Kompensasi';
                            }
                            
                            // If compensation is available and needed
                            return 'Terapkan Kompensasi';
                        })
                        ->icon(function (TaxReport $record): string {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = $record->getSelisihPpn();
                            $hasExistingCompensation = ($record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0) > 0;
                            
                            if ($hasExistingCompensation) {
                                return 'heroicon-o-cog-6-tooth';
                            }
                            
                            if ($availableCompensations->isEmpty() || $currentSelisih <= 0) {
                                return 'heroicon-o-x-circle';
                            }
                            
                            return 'heroicon-o-currency-dollar';
                        })
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
                            
                            // Enable if has existing compensation (for management)
                            if ($hasExistingCompensation) {
                                return false;
                            }
                            
                            // Disable if no compensation available or not needed
                            return $availableCompensations->isEmpty() || $currentSelisih <= 0;
                        })
                        ->tooltip(function (TaxReport $record): string {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = $record->getSelisihPpn();
                            $hasExistingCompensation = ($record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0) > 0;
                            
                            if ($hasExistingCompensation) {
                                $compensation = number_format($record->ppn_dikompensasi_dari_masa_sebelumnya, 0, ',', '.');
                                return "Sudah ada kompensasi: Rp {$compensation}. Klik untuk mengelola.";
                            }
                            
                            if ($availableCompensations->isEmpty()) {
                                return "Tidak ada periode sebelumnya dengan status 'Lebih Bayar' yang dapat dikompensasikan.";
                            }
                            
                            if ($currentSelisih <= 0) {
                                $status = $currentSelisih < 0 ? 'Lebih Bayar' : 'Nihil';
                                return "Periode ini berstatus '{$status}', tidak memerlukan kompensasi.";
                            }
                            
                            $availableCount = $availableCompensations->count();
                            $totalAvailable = $availableCompensations->sum('available_amount');
                            return "Tersedia {$availableCount} periode dengan total kompensasi Rp " . number_format($totalAvailable, 0, ',', '.');
                        })
                        ->form(function (TaxReport $record): array {
                            $availableCompensations = self::getAvailableCompensations($record);
                            $currentSelisih = $record->getSelisihPpn();
                            $currentCompensation = $record->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;
                            $effectiveAmount = $currentSelisih - $currentCompensation;
                            
                            // Get existing compensations for this record
                            $existingCompensations = $record->compensationsReceived()
                                ->with('sourceTaxReport')
                                ->get()
                                ->map(function ($compensation) use ($availableCompensations) {
                                    $sourceReport = $compensation->sourceTaxReport;
                                    $availableData = $availableCompensations->firstWhere('id', $sourceReport->id);
                                    
                                    return [
                                        'source_tax_report_id' => $sourceReport->id,
                                        'amount' => $compensation->amount_compensated,
                                        'available_amount' => $availableData ? $availableData['available_amount'] + $compensation->amount_compensated : $compensation->amount_compensated,
                                        'max_amount' => $availableData ? $availableData['available_amount'] + $compensation->amount_compensated : $compensation->amount_compensated,
                                        'notes' => $compensation->notes,
                                        'existing_compensation_id' => $compensation->id
                                    ];
                                })
                                ->toArray();

                            
                            return [
                                Section::make('Informasi Periode Saat Ini')
                                    ->schema([
                                        Forms\Components\Placeholder::make('current_info')
                                            ->content(function () use ($record, $currentSelisih, $currentCompensation, $effectiveAmount) {
                                                return view('components.tax-reports.tax-compensation-period', [
                                                    'record' => $record,
                                                    'currentSelisih' => $currentSelisih,
                                                    'currentCompensation' => $currentCompensation,
                                                    'effectiveAmount' => $effectiveAmount,
                                                    'showTitle' => false // Since Section already has title
                                                ]);
                                            })
                                            ->columnSpanFull(),
                                    ]),
                                
                                Section::make('Periode dengan Kelebihan Bayar')
                                    ->description('Pilih periode mana yang ingin dikompensasikan')
                                    ->schema([
                                        Repeater::make('compensations')
                                            ->label('')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('source_tax_report_id')
                                                            ->label('Periode Sumber')
                                                            ->options($availableCompensations->pluck('label', 'id'))
                                                            ->required()
                                                            ->reactive()
                                                            ->afterStateUpdated(function (Set $set, Get $get, $state) use ($availableCompensations, $effectiveAmount) {
                                                                
                                                                if ($state) {
                                                                    $selected = $availableCompensations->firstWhere('id', $state);
                                                                    $availableAmount = $selected['available_amount'] ?? 0;
                                                                    
                                                                    $set('available_amount', $availableAmount);
                                                                    $set('max_amount', $availableAmount);
                                                                    
                                                                    // Auto-fill with full available amount
                                                                    $set('amount', $availableAmount);
                                                                }
                                                            }),
                                                        
                                                        Forms\Components\Placeholder::make('available_amount')
                                                            ->label('Tersedia')
                                                            ->content(function (Get $get) {
                                                                $amount = $get('available_amount') ?? 0;
                                                                return 'Rp ' . number_format($amount, 0, ',', '.');
                                                            }),
                                                        
                                                        TextInput::make('amount')
                                                            ->label('Jumlah Kompensasi')
                                                            ->numeric()
                                                            ->prefix('Rp')
                                                            ->required()
                                                            ->reactive()
                                                            ->rules([
                                                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                                                    $maxAmount = $get('max_amount') ?? 0;
                                                                    if ($value > $maxAmount) {
                                                                        $fail("Jumlah tidak boleh lebih dari Rp " . number_format($maxAmount, 0, ',', '.'));
                                                                    }
                                                                }
                                                            ]),
                                                        
                                                        Forms\Components\Hidden::make('max_amount'),
                                                    ]),
                                                
                                                Textarea::make('notes')
                                                    ->label('Catatan')
                                                    ->placeholder('Catatan tambahan untuk kompensasi ini...')
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ])
                                            ->default($existingCompensations)
                                            ->defaultItems(max(1, count($existingCompensations)))
                                            ->addActionLabel('Tambah Kompensasi Lain')
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->itemLabel(function (array $state): ?string {
                                                if (!$state['source_tax_report_id']) {
                                                    return 'Kompensasi Baru';
                                                }
                                                
                                                $amount = $state['amount'] ?? 0;
                                                $isExisting = !empty($state['existing_compensation_id']);
                                                $prefix = $isExisting ? '📝 Edit: ' : '➕ Baru: ';
                                                
                                                return $prefix . 'Rp ' . number_format($amount, 0, ',', '.');
                                            }),
                                    ]),
                                
                                Section::make('Ringkasan Kompensasi')
                                    ->schema([
                                        Forms\Components\Placeholder::make('compensation_summary')
                                            ->content(function (Get $get) use ($effectiveAmount, $currentCompensation) {
                                                $compensations = $get('compensations') ?? [];
                                                $totalNewCompensation = (float) collect($compensations)->sum('amount');
                                                $finalAmount = $effectiveAmount - $totalNewCompensation;
                                                
                                                return view('components.tax-reports.tax-compensation-summary', [
                                                    'effectiveAmount' => $effectiveAmount,
                                                    'currentCompensation' => $currentCompensation,
                                                    'totalNewCompensation' => $totalNewCompensation,
                                                    'finalAmount' => $finalAmount,
                                                    'showTitle' => false // Since Section already has title
                                                ]);
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ];
                        })
                        ->action(function (TaxReport $record, array $data): void {
                            $totalCompensationApplied = 0;
                            $compensationNotes = [];
                            $processedCompensations = [];
                            
                            foreach ($data['compensations'] as $compensation) {
                                if (empty($compensation['source_tax_report_id']) || empty($compensation['amount'])) {
                                    continue;
                                }
                                
                                $sourceReport = TaxReport::find($compensation['source_tax_report_id']);
                                if (!$sourceReport) {
                                    continue;
                                }
                                
                                $requestedAmount = (float) $compensation['amount'];
                                $existingCompensationId = $compensation['existing_compensation_id'] ?? null;
                                
                                // Handle existing compensation update
                                if ($existingCompensationId) {
                                    $existingCompensation = TaxCompensation::find($existingCompensationId);
                                    if ($existingCompensation) {
                                        $oldAmount = $existingCompensation->amount_compensated;
                                        $amountDifference = $requestedAmount - $oldAmount;
                                        
                                        // Check if we have enough available amount for the increase
                                        $currentlyUsed = $sourceReport->ppn_sudah_dikompensasi;
                                        $available = $sourceReport->ppn_lebih_bayar_dibawa_ke_masa_depan - $currentlyUsed + $oldAmount;
                                        
                                        if ($requestedAmount <= $available) {
                                            // Update existing compensation
                                            $existingCompensation->update([
                                                'amount_compensated' => $requestedAmount,
                                                'notes' => $compensation['notes'] ?? $existingCompensation->notes
                                            ]);
                                            
                                            // Update source report
                                            $sourceReport->increment('ppn_sudah_dikompensasi', $amountDifference);
                                            
                                            $totalCompensationApplied += $amountDifference;
                                            $compensationNotes[] = "Diperbarui: Rp " . number_format($requestedAmount, 0, ',', '.') . " dari {$sourceReport->month}";
                                        }
                                    }
                                } else {
                                    // Create new compensation
                                    $availableAmount = $sourceReport->ppn_lebih_bayar_dibawa_ke_masa_depan - $sourceReport->ppn_sudah_dikompensasi;
                                    $actualAmount = min($requestedAmount, $availableAmount);
                                    
                                    if ($actualAmount > 0) {
                                        // Check if compensation already exists for this combination
                                        $existingRecord = TaxCompensation::where('source_tax_report_id', $sourceReport->id)
                                            ->where('target_tax_report_id', $record->id)
                                            ->first();
                                        
                                        if ($existingRecord) {
                                            // Update existing record
                                            $oldAmount = $existingRecord->amount_compensated;
                                            $amountDifference = $actualAmount - $oldAmount;
                                            
                                            $existingRecord->update([
                                                'amount_compensated' => $actualAmount,
                                                'notes' => $compensation['notes'] ?? "Kompensasi dari periode {$sourceReport->month}"
                                            ]);
                                            
                                            $sourceReport->increment('ppn_sudah_dikompensasi', $amountDifference);
                                            $totalCompensationApplied += $amountDifference;
                                            $compensationNotes[] = "Diperbarui: Rp " . number_format($actualAmount, 0, ',', '.') . " dari {$sourceReport->month}";
                                        } else {
                                            // Create new compensation record
                                            TaxCompensation::create([
                                                'source_tax_report_id' => $sourceReport->id,
                                                'target_tax_report_id' => $record->id,
                                                'amount_compensated' => $actualAmount,
                                                'notes' => $compensation['notes'] ?? "Kompensasi dari periode {$sourceReport->month}"
                                            ]);
                                            
                                            $sourceReport->increment('ppn_sudah_dikompensasi', $actualAmount);
                                            $totalCompensationApplied += $actualAmount;
                                            $compensationNotes[] = "Rp " . number_format($actualAmount, 0, ',', '.') . " dari {$sourceReport->month}";
                                        }
                                    }
                                }
                                
                                $processedCompensations[] = $sourceReport->id;
                            }
                            
                            // Recalculate total compensation for the target record
                            $newTotalCompensation = $record->compensationsReceived()->sum('amount_compensated');
                            
                            // Update current report compensation
                            $record->update([
                                'ppn_dikompensasi_dari_masa_sebelumnya' => $newTotalCompensation,
                                'kompensasi_notes' => "Dikompensasi: " . implode(', ', $compensationNotes)
                            ]);
                            
                            // Recalculate invoice tax status based on new compensation
                            $ppnKeluar = (float) $record->getTotalPpnKeluar();
                            $ppnMasuk = (float) $record->getTotalPpnMasuk();
                            $selisihPpn = $ppnKeluar - $ppnMasuk;
                            $effectivePayment = $selisihPpn - $newTotalCompensation;
                            
                            // Determine new status based on effective payment after compensation
                            $newStatus = 'Nihil';
                            if ($effectivePayment > 0) {
                                $newStatus = 'Kurang Bayar';
                            } elseif ($effectivePayment < 0) {
                                $newStatus = 'Lebih Bayar';
                                // If becomes Lebih Bayar, set amount available for future compensation
                                $record->update([
                                    'ppn_lebih_bayar_dibawa_ke_masa_depan' => abs($effectivePayment),
                                    'ppn_sudah_dikompensasi' => 0 // Reset since this is a new Lebih Bayar status
                                ]);
                            } else {
                                // If Nihil, reset future compensation fields
                                $record->update([
                                    'ppn_lebih_bayar_dibawa_ke_masa_depan' => 0,
                                    'ppn_sudah_dikompensasi' => 0
                                ]);
                            }
                            
                            // Update the invoice tax status
                            $record->update(['invoice_tax_status' => $newStatus]);
                            
                            // Prepare status message
                            $statusMessage = '';
                            $statusColor = 'success';
                            
                            switch ($newStatus) {
                                case 'Nihil':
                                    $statusMessage = "Status berubah menjadi <strong>Nihil</strong> - tidak ada kewajiban tersisa.";
                                    $statusColor = 'info';
                                    break;
                                case 'Lebih Bayar':
                                    $statusMessage = "Status berubah menjadi <strong>Lebih Bayar</strong> - kelebihan Rp " . number_format(abs($effectivePayment), 0, ',', '.') . " dapat dikompensasikan ke periode berikutnya.";
                                    $statusColor = 'success';
                                    break;
                                case 'Kurang Bayar':
                                    $statusMessage = "Status tetap <strong>Kurang Bayar</strong> - masih harus bayar Rp " . number_format($effectivePayment, 0, ',', '.') . ".";
                                    $statusColor = 'warning';
                                    break;
                            }
                            
                            Notification::make()
                                ->title('Kompensasi Berhasil Dikelola')
                                ->body("
                                    <div class='space-y-2'>
                                        <div>✅ Total kompensasi <strong>Rp " . number_format($newTotalCompensation, 0, ',', '.') . "</strong> telah diterapkan.</div>
                                        <div>📊 {$statusMessage}</div>
                                    </div>
                                ")
                                ->color($statusColor)
                                ->duration(8000)
                                ->send();
                        })
                        ->modalHeading('Terapkan Kompensasi PPN')
                        ->modalDescription('Terapkan kompensasi dari periode sebelumnya yang memiliki kelebihan bayar ke periode ini.')
                        ->modalWidth('7xl')
                        ->slideOver(),
                        
                    ActivitylogAction::make(),
                    
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