<?php

namespace App\Livewire\MonthlyPlan;

use App\Models\Invoice;
use App\Models\TaxReport;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class TaxInvoiceManager extends Component implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    
    public $taxReportId;
    public $showInvoiceModal = false;
    public $selectedInvoice = null;
    public $selectedClient = null;
    
    public function mount($taxReportId = null)
    {
        $this->taxReportId = $taxReportId;
        
        if ($taxReportId) {
            $taxReport = TaxReport::with('client')->find($taxReportId);
            if ($taxReport) {
                $this->selectedClient = $taxReport->client;
            }
        }
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(Invoice::query()->when($this->taxReportId, function ($query) {
                return $query->where('tax_report_id', $this->taxReportId);
            }))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Faktur')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('npwp')
                    ->label('NPWP')
                    ->searchable(),
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis Faktur')
                    ->colors([
                        'primary' => fn ($state) => $state === null,
                        'success' => fn ($state) => $state === 'Faktur Keluaran',
                        'warning' => fn ($state) => $state === 'Faktur Masuk',
                    ]),
                    
                Tables\Columns\TextColumn::make('dpp')
                    ->label('DPP')
                    ->money('idr')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN')
                    ->money('idr')
                    ->sortable(),
                    
                Tables\Columns\ToggleColumn::make('nihil')
                    ->label('Nihil')
                    ->onColor('success')
                    ->offColor('danger'),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Faktur')
                    ->options([
                        'Faktur Keluaran' => 'Faktur Keluaran',
                        'Faktur Masuk' => 'Faktur Masuk',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('nihil')
                    ->label('Nihil')
                    ->placeholder('Semua')
                    ->trueLabel('Nihil')
                    ->falseLabel('Tidak Nihil'),
                    
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Tanggal Dari'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Tanggal Sampai'),
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
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Detail Faktur Pajak')
                    ->modalContent(fn (Invoice $record) => view('components.invoice-details', ['invoice' => $record])),
                    
                Tables\Actions\EditAction::make()
                    ->action(function (Invoice $record, array $data): void {
                        $record->update($data);
                        $this->dispatch('invoice-updated');
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Faktur Pajak')
                    ->modalDescription('Apakah Anda yakin ingin menghapus faktur pajak ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->action(function (Invoice $record): void {
                        $record->delete();
                        $this->dispatch('invoice-deleted');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Faktur Pajak yang Dipilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua faktur pajak yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                        
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn (Collection $records) => $this->exportToExcel($records)),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Faktur Pajak')
                    ->modalHeading('Tambah Faktur Pajak Baru')
                    ->form($this->getInvoiceForm())
                    ->action(function (array $data): void {
                        // Add the tax report ID if set
                        if ($this->taxReportId) {
                            $data['tax_report_id'] = $this->taxReportId;
                        }
                        
                        // Create the invoice
                        Invoice::create($data);
                        $this->dispatch('invoice-created');
                    }),
            ])
            ->emptyStateHeading('Belum Ada Faktur Pajak')
            ->emptyStateDescription('Buat faktur pajak baru untuk mengelola dokumen faktur pajak perusahaan Anda.')
            ->emptyStateIcon('heroicon-o-document')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Faktur Pajak')
                    ->modalHeading('Tambah Faktur Pajak Baru')
                    ->form($this->getInvoiceForm())
                    ->action(function (array $data): void {
                        // Add the tax report ID if set
                        if ($this->taxReportId) {
                            $data['tax_report_id'] = $this->taxReportId;
                        }
                        
                        // Create the invoice
                        Invoice::create($data);
                        $this->dispatch('invoice-created');
                    }),
            ]);
    }
    
    protected function getInvoiceForm(): array
    {
        return [
            Forms\Components\Select::make('tax_report_id')
                ->label('Laporan Pajak')
                ->relationship('taxReport', 'month')
                ->hidden(fn () => $this->taxReportId)
                ->required()
                ->preload(),
                
            Forms\Components\TextInput::make('invoice_number')
                ->label('Nomor Faktur')
                ->required()
                ->maxLength(255)
                ->unique(table: Invoice::class, column: 'invoice_number')
                ->helperText('Format: 010.000-00.00000000'),
                
            Forms\Components\TextInput::make('company_name')
                ->label('Nama Perusahaan')
                ->required()
                ->maxLength(255),
                
            Forms\Components\TextInput::make('npwp')
                ->label('NPWP')
                ->required()
                ->maxLength(255)
                ->helperText('Format: 00.000.000.0-000.000'),
                
            Forms\Components\Select::make('type')
                ->label('Jenis Faktur')
                ->options([
                    'Faktur Keluaran' => 'Faktur Keluaran',
                    'Faktur Masuk' => 'Faktur Masuk',
                ])
                ->required(),
                
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('dpp')
                        ->label('DPP')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->afterStateUpdated(function ($state, callable $set) {
                            $ppn = $state * 0.11; // Assuming 11% tax rate
                            $set('ppn', round($ppn, 2));
                        }),
                        
                    Forms\Components\TextInput::make('ppn')
                        ->label('PPN')
                        ->numeric()
                        ->required()
                        ->prefix('Rp'),
                ]),
                
            Forms\Components\Toggle::make('nihil')
                ->label('Nihil')
                ->helperText('Centang jika faktur ini adalah faktur nihil'),
                
            Forms\Components\FileUpload::make('file_path')
                ->label('File Faktur')
                ->required()
                ->disk('public')
                ->directory('invoices')
                ->acceptedFileTypes(['application/pdf']),
                
            Forms\Components\Textarea::make('notes')
                ->label('Catatan')
                ->maxLength(1000)
                ->columnSpanFull(),
        ];
    }
    
    protected function exportToExcel($records)
    {
        // Implementation of Excel export would go here
        // You might use Laravel Excel or other packages
    }
    
    public function render()
    {
        return view('livewire.monthly-plan.tax-invoice-manager');
    }
}