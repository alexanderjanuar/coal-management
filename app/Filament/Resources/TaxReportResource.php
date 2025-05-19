<?php

namespace App\Filament\Resources;

use App\Filament\Exports\TaxReportExporter;
use App\Filament\Resources\TaxReportResource\Pages;
use App\Filament\Resources\TaxReportResource\RelationManagers;
use App\Filament\Resources\TaxReportResource\RelationManagers\IncomeTaxsRelationManager;
use App\Models\Client;
use App\Models\TaxReport;
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
use Filament\Support\RawJs;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Get;
use Closure;
use Filament\Tables\Grouping\Group;

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
                    
                TextColumn::make('invoices_sum')
                    ->label('Faktur (PPN)')
                    ->state(function (TaxReport $record): string {
                        $totalPPN = $record->invoices()->sum('ppn');
                        return "Rp " . number_format($totalPPN, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $invoicesCount = $record->invoices()->count();
                        return "Total {$invoicesCount} faktur";
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum('invoices', 'ppn')
                            ->orderBy('invoices_sum_ppn', $direction);
                    }),
                    
                TextColumn::make('income_taxes_sum')
                    ->label('PPh 21')
                    ->state(function (TaxReport $record): string {
                        $totalAmount = $record->incomeTaxs()->sum('pph_21_amount');
                        return "Rp " . number_format($totalAmount, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $taxesCount = $record->incomeTaxs()->count();
                        return "Total {$taxesCount} PPh 21";
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum('incomeTaxs', 'pph_21_amount')
                            ->orderBy('income_taxs_sum_pph_21_amount', $direction);
                    }),
                    
                TextColumn::make('bupots_sum')
                    ->label('Bukti Potong')
                    ->state(function (TaxReport $record): string {
                        $totalAmount = $record->bupots()->sum('bupot_amount');
                        return "Rp " . number_format($totalAmount, 0, ',', '.');
                    })
                    ->tooltip(function (TaxReport $record): string {
                        $bupotsCount = $record->bupots()->count();
                        return "Total {$bupotsCount} bukti potong";
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum('bupots', 'bupot_amount')
                            ->orderBy('bupots_sum_bupot_amount', $direction);
                    }),
                    
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
                    ->sortable(false),
                    
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->groups([
                'client.name',
            ])
            ->filters([
                // Client filter
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
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
                    ExportBulkAction::make()
                        ->label('Ekspor Laporan Pajak (XLSX)')
                        ->icon('heroicon-o-download')
                        ->color('success')
                        ->exporter(\App\Filament\Exports\TaxReportExporter::class),
                ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(TaxReportExporter::class)
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
