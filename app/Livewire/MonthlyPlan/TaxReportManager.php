<?php

namespace App\Livewire\MonthlyPlan;

use App\Models\Client;
use App\Models\TaxReport;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use Carbon\Carbon;

class TaxReportManager extends Component implements HasForms
{
    use InteractsWithForms;

    public $data = [];
    public $clients = [];
    public ?Client $selectedClient = null;
    public $months = [];
    public $activeTab = 'overview';
    public $taxReportId = null;

    public function mount(): void
    {
        $this->clients = Client::where('status', 'Active')->get();
        
        // Generate month options with just month names
        $this->months = [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
        
        // Set default values
        $this->form->fill([
            'client_id' => null,
            'month' => Carbon::now()->format('m'),
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Create New Tax Report')
                    ->description('Add a new monthly tax report for a client')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Select::make('client_id')
                            ->label('Client')
                            ->options(function () {
                                return $this->clients->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    $this->selectedClient = Client::find($state);
                                } else {
                                    $this->selectedClient = null;
                                }
                            })
                            ->placeholder('Select a client'),
                            
                        Select::make('month')
                            ->label('Month')
                            ->options($this->months)
                            ->default(Carbon::now()->format('m'))
                            ->searchable()
                            ->required(),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }
    
    public function createTaxReport(): void
    {
        $data = $this->form->getState();
        
        // Get the current year for creating the full date
        $currentYear = Carbon::now()->format('Y');
        $monthValue = $currentYear . '-' . $data['month'];
        
        // Validate if tax report already exists for this client and month
        $existingReport = TaxReport::where('client_id', $data['client_id'])
            ->where('month', $monthValue)
            ->first();
            
        if ($existingReport) {
            Notification::make()
                ->title('Tax report already exists')
                ->body('A tax report already exists for this client and period.')
                ->danger()
                ->send();
                
            return;
        }
        
        // Create the tax report with default PKP status
        $taxReport = TaxReport::create([
            'client_id' => $data['client_id'],
            'month' => $monthValue,
        ]);
        
        // Set the newly created tax report ID
        $this->taxReportId = $taxReport->id;
        $this->activeTab = 'invoices';
        
        // Reset the form
        $this->form->fill([
            'client_id' => null,
            'month' => Carbon::now()->format('m'),
        ]);
        
        $this->selectedClient = null;
        
        // Show success notification
        Notification::make()
            ->title('Tax report created')
            ->body('The tax report has been created successfully.')
            ->success()
            ->send();
    }
    
    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }
    
    public function selectTaxReport($id): void
    {
        $this->taxReportId = $id;
        $this->activeTab = 'overview';
    }
    
    public function render()
    {
        return view('livewire.monthly-plan.tax-report-manager');
    }
}