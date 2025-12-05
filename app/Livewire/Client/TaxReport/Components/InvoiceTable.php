<?php

namespace App\Livewire\Client\TaxReport\Components;

use App\Models\Invoice;
use App\Models\TaxReport;
use App\Models\UserClient;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Collection;

// Import services
use App\Services\ClientTypeService;
use App\Services\FileManagementService;

class InvoiceTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $taxReportId;
    public $taxReport;

    public function mount($taxReportId)
    {
        $this->taxReportId = $taxReportId;
        
        // Verify access
        $this->verifyAccess();
        
        $this->taxReport = TaxReport::with('client')->findOrFail($taxReportId);
    }

    /**
     * Verify user has access to the client
     */
    protected function verifyAccess()
    {
        $taxReport = TaxReport::findOrFail($this->taxReportId);
        
        $hasAccess = UserClient::where('user_id', auth()->id())
            ->where('client_id', $taxReport->client_id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke laporan pajak ini.');
        }
    }

    /**
     * Get view-only form schema for invoices
     */
    protected function getViewFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Informasi Faktur')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('invoice_number')
                                ->label('Nomor Faktur')
                                ->disabled(),
                            
                            Forms\Components\DatePicker::make('invoice_date')
                                ->label('Tanggal Faktur')
                                ->disabled(),
                            
                            Forms\Components\TextInput::make('company_name')
                                ->label('Nama Perusahaan')
                                ->disabled(),
                            
                            Forms\Components\TextInput::make('npwp')
                                ->label('NPWP')
                                ->disabled(),
                            
                            Forms\Components\Select::make('type')
                                ->label('Jenis Faktur')
                                ->options([
                                    'Faktur Keluaran' => 'Faktur Keluaran',
                                    'Faktur Masuk' => 'Faktur Masuk',
                                ])
                                ->disabled(),
                            
                            Forms\Components\Select::make('client_type')
                                ->label('Tipe Client')
                                ->options(ClientTypeService::getClientTypeOptions())
                                ->disabled(),
                        ]),
                ]),
            
            Forms\Components\Section::make('Informasi Pajak')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('ppn_percentage')
                                ->label('Tarif PPN')
                                ->options([
                                    '11' => '11%',
                                    '12' => '12%',
                                ])
                                ->disabled(),
                            
                            Forms\Components\TextInput::make('dpp')
                                ->label('DPP')
                                ->prefix('Rp')
                                ->disabled(),
                            
                            Forms\Components\TextInput::make('dpp_nilai_lainnya')
                                ->label('DPP Nilai Lainnya')
                                ->prefix('Rp')
                                ->disabled()
                                ->visible(fn ($get) => $get('ppn_percentage') === '12'),
                            
                            Forms\Components\TextInput::make('ppn')
                                ->label('PPN')
                                ->prefix('Rp')
                                ->disabled(),
                            
                            Forms\Components\Toggle::make('has_ppn')
                                ->label('Subject PPN')
                                ->disabled(),
                            
                            Forms\Components\Toggle::make('nihil')
                                ->label('Nihil')
                                ->disabled(),
                        ]),
                ]),
            
            Forms\Components\Section::make('Catatan')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->disabled()
                        ->columnSpanFull(),
                ])
                ->visible(fn ($get) => !empty($get('notes'))),
            
            Forms\Components\Section::make('Informasi Revisi')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('revision_number')
                                ->label('Nomor Revisi')
                                ->disabled(),
                            
                            Forms\Components\Textarea::make('revision_reason')
                                ->label('Alasan Revisi')
                                ->rows(2)
                                ->disabled()
                                ->columnSpanFull(),
                        ]),
                ])
                ->visible(fn ($get) => $get('is_revision')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Invoice::query()->where('tax_report_id', $this->taxReportId))
            ->columns([
                Tables\Columns\ImageColumn::make('user_avatar')
                    ->label('Dibuat Oleh')
                    ->circular()
                    ->state(function ($record) {
                        if ($record->created_by) {
                            $user = \App\Models\User::find($record->created_by);
                            if ($user && method_exists($user, 'getAvatarUrl')) {
                                return $user->getAvatarUrl();
                            }
                        }
                        return null;
                    })
                    ->defaultImageUrl(asset('images/default-avatar.png'))
                    ->size(40)
                    ->tooltip(function ($record): string {
                        if ($record->created_by) {
                            $user = \App\Models\User::find($record->created_by);
                            return $user ? $user->name : 'User #' . $record->created_by;
                        }
                        return 'System';
                    }),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor Faktur')
                    ->searchable()
                    ->sortable()
                    ->description(function ($record) {
                        if (!$record) return null;
                        
                        if ($record->is_revision) {
                            return "Revisi #{$record->revision_number}";
                        }
                        if ($record->hasRevisions()) {
                            $revisionCount = $record->revisions()->count();
                            return "Memiliki {$revisionCount} revisi";
                        }
                        return null;
                    }),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('Nama Perusahaan')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis Faktur')
                    ->colors([
                        'success' => 'Faktur Keluaran',
                        'warning' => 'Faktur Masuk',
                    ])
                    ->tooltip(function ($record) {
                        if (!$record || $record->type !== 'Faktur Masuk') {
                            return null;
                        }
                        
                        return $record->is_business_related 
                            ? 'Faktur Masukan untuk aktivitas bisnis utama'
                            : 'Faktur Masukan untuk keperluan non-bisnis';
                    }),

                Tables\Columns\TextColumn::make('ppn_percentage')
                    ->label('Tarif PPN')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        '11' => 'success',
                        '12' => 'warning',
                        null => 'gray',
                        default => 'gray',
                    })
                    ->getStateUsing(fn ($record) => $record->ppn_percentage ?? '11')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dpp_nilai_lainnya')
                    ->label('DPP Nilai Lainnya')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => ($record->ppn_percentage ?? '11') === '12')
                    ->tooltip('DPP Nilai Lainnya - untuk tarif 12%'),

                Tables\Columns\TextColumn::make('dpp')
                    ->label('DPP')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('IDR')
                            ->label('Peredaran Bruto')
                            ->query(fn (QueryBuilder $query) => $query->where(function ($q) {
                                // Include original invoices that don't have any revisions
                                $q->where('is_revision', false)
                                ->whereNotExists(function ($subQuery) {
                                    $subQuery->select(\DB::raw(1))
                                            ->from('invoices as revisions')
                                            ->whereColumn('revisions.original_invoice_id', 'invoices.id')
                                            ->where('revisions.is_revision', true);
                                });
                            })->orWhere(function ($q) {
                                // Include only the latest revision for each original invoice
                                $q->where('is_revision', true)
                                ->whereIn('id', function ($subQuery) {
                                    $subQuery->selectRaw('MAX(id)')
                                            ->from('invoices as latest_revisions')
                                            ->where('latest_revisions.is_revision', true)
                                            ->whereNotNull('latest_revisions.original_invoice_id')
                                            ->groupBy('latest_revisions.original_invoice_id');
                                });
                            }))
                    )
                    ->description(function ($record) {
                        if (($record->ppn_percentage ?? '11') === '12' && ($record->dpp_nilai_lainnya ?? 0) > 0) {
                            return 'Dihitung dari DPP Nilai Lainnya';
                        }
                        return null;
                    }),

                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('IDR')
                            ->label('Total PPN')
                            ->query(fn (QueryBuilder $query) => $query->where(function ($q) {
                                // Include original invoices that don't have any revisions
                                $q->where('is_revision', false)
                                ->whereNotExists(function ($subQuery) {
                                    $subQuery->select(\DB::raw(1))
                                            ->from('invoices as revisions')
                                            ->whereColumn('revisions.original_invoice_id', 'invoices.id')
                                            ->where('revisions.is_revision', true);
                                });
                            })->orWhere(function ($q) {
                                // Include only the latest revision for each original invoice
                                $q->where('is_revision', true)
                                ->whereIn('id', function ($subQuery) {
                                    $subQuery->selectRaw('MAX(id)')
                                            ->from('invoices as latest_revisions')
                                            ->where('latest_revisions.is_revision', true)
                                            ->whereNotNull('latest_revisions.original_invoice_id')
                                            ->groupBy('latest_revisions.original_invoice_id');
                                });
                            }))
                    ),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Tanggal Faktur')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->groups(['type'])
            ->defaultSort('invoice_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Faktur')
                    ->options([
                        'Faktur Keluaran' => 'Faktur Keluaran',
                        'Faktur Masuk' => 'Faktur Masuk',
                    ]),

                Tables\Filters\SelectFilter::make('client_type')
                    ->label('Tipe Client')
                    ->options(ClientTypeService::getClientTypeOptions()),

                Tables\Filters\Filter::make('has_ppn')
                    ->label('Subject PPN')
                    ->query(fn (Builder $query): Builder => $query->where('has_ppn', true)),

                Tables\Filters\Filter::make('no_ppn')
                    ->label('Tidak Subject PPN')
                    ->query(fn (Builder $query): Builder => $query->where('has_ppn', false)),

                Tables\Filters\Filter::make('is_revision')
                    ->label('Hanya Revisi')
                    ->query(fn (Builder $query): Builder => $query->where('is_revision', true)),

                Tables\Filters\Filter::make('originals_only')
                    ->label('Hanya Asli')
                    ->query(fn (Builder $query): Builder => $query->where('is_revision', false)),

                Tables\Filters\Filter::make('has_bukti_setor')
                    ->label('Memiliki Bukti Setor')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('bukti_setor')->where('bukti_setor', '!=', '')),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all')
                    ->label('Ekspor Semua ke Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $monthYear = FileManagementService::convertToIndonesianMonth($this->taxReport->month) . '_' . date('Y');
                        $filename = 'Faktur_' . $monthYear . '.xlsx';
                        
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TaxReportInvoicesExport($this->taxReport),
                            $filename
                        );
                    })
                    ->tooltip('Ekspor semua faktur ke format Excel'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->modalWidth('7xl')
                    ->form($this->getViewFormSchema()),

                Tables\Actions\Action::make('view_revisions')
                    ->label('Lihat Revisi')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->visible(fn ($record) => $record && !$record->is_revision && $record->hasRevisions())
                    ->modalContent(function ($record) {
                        $revisions = $record->revisions()->orderBy('revision_number')->get();
                        return view('components.invoices.revisions-modal', [
                            'originalInvoice' => $record,
                            'revisions' => $revisions
                        ]);
                    })
                    ->modalHeading(fn ($record) => 'Revisi Faktur: ' . $record->invoice_number)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('7xl')
                    ->tooltip(function ($record) {
                        $count = $record->revisions()->count();
                        return "Lihat {$count} revisi dari faktur ini";
                    }),

                Tables\Actions\Action::make('view_original')
                    ->label('Lihat Faktur Asli')
                    ->icon('heroicon-o-document')
                    ->color('primary')
                    ->visible(fn ($record) => $record && $record->is_revision)
                    ->modalContent(function ($record) {
                        if ($record->originalInvoice) {
                            return view('components.invoices.original-invoice-modal', [
                                'originalInvoice' => $record->originalInvoice
                            ]);
                        }
                        return view('components.invoices.original-invoice-not-found');
                    })
                    ->modalHeading(fn ($record) => 'Faktur Asli: ' . ($record->originalInvoice ? $record->originalInvoice->invoice_number : 'Tidak Ditemukan'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('7xl')
                    ->tooltip('Lihat detail lengkap faktur asli yang direvisi'),

                Tables\Actions\Action::make('view_bukti_setor')
                    ->label('Lihat Bukti Setor')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn ($record) => $record && !empty($record->bukti_setor))
                    ->url(fn ($record) => asset('storage/' . $record->bukti_setor))
                    ->openUrlInNewTab()
                    ->tooltip('Lihat bukti setor pajak'),

                Tables\Actions\Action::make('download')
                    ->label('Unduh Berkas')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => $record && $record->file_path ? asset('storage/' . $record->file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record && $record->file_path)
                    ->tooltip('Unduh berkas faktur pajak'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Ekspor Terpilih ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $selectedIds = $records->pluck('id')->toArray();
                            $monthYear = FileManagementService::convertToIndonesianMonth($this->taxReport->month) . '_' . date('Y');
                            $filename = 'Faktur_Terpilih_' . $monthYear . '.xlsx';
                            
                            return \Maatwebsite\Excel\Facades\Excel::download(
                                new \App\Exports\TaxReportInvoicesExport($this->taxReport, $selectedIds),
                                $filename
                            );
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Ekspor Faktur Terpilih')
                        ->modalDescription(function (Collection $records) {
                            $count = $records->count();
                            return "Apakah Anda yakin ingin mengekspor {$count} faktur yang terpilih ke Excel?";
                        })
                        ->modalSubmitActionLabel('Ya, Ekspor')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Faktur Pajak')
            ->emptyStateDescription('Belum ada faktur pajak untuk periode ini.')
            ->emptyStateIcon('heroicon-o-document-duplicate');
    }

    public function render()
    {
        return view('livewire.client.tax-report.components.invoice-table');
    }
}