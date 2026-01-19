<?php

namespace App\Livewire\Client\Management;

use App\Models\Client;
use App\Models\Employee;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Livewire\Component;
use Livewire\Attributes\On;

class KaryawanTab extends Component implements HasForms
{
    use InteractsWithForms;

    public Client $client;
    public $employees = [];
    public $stats = [];
    
    // Modal state
    public $editingId = null;
    public $name = '';
    public $npwp = '';
    public $position = '';
    public $marital_status = 'single';
    public $tk = 0;
    public $k = 0;
    public $salary = '';
    public $status = 'active';
    public $type = 'Harian';
    
    // Delete confirmation
    public $employeeToDelete = null;

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->loadEmployees();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama lengkap karyawan')
                                    ->columnSpan(2),
                                
                                TextInput::make('npwp')
                                    ->label('NPWP')
                                    ->mask('99.999.999.9-999.999')
                                    ->placeholder('00.000.000.0-000.000')
                                    ->columnSpan(1),
                                
                                TextInput::make('position')
                                    ->label('Jabatan')
                                    ->placeholder('Contoh: Manager, Staff, Supervisor')
                                    ->columnSpan(1),
                                
                                Select::make('type')
                                    ->label('Tipe Karyawan')
                                    ->options([
                                        'Harian' => 'Harian',
                                        'Karyawan Tetap' => 'Karyawan Tetap',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(1),
                                
                                Select::make('status')
                                    ->label('Status Aktif')
                                    ->options([
                                        'active' => 'Aktif',
                                        'inactive' => 'Tidak Aktif',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->default('active')
                                    ->columnSpan(1),
                                
                                TextInput::make('salary')
                                    ->label('Gaji')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->columnSpan(2),
                                
                                Radio::make('marital_status')
                                    ->label('Status Pernikahan')
                                    ->options([
                                        'single' => 'Belum Menikah (TK)',
                                        'married' => 'Menikah (K)',
                                    ])
                                    ->required()
                                    ->inline()
                                    ->reactive()
                                    ->columnSpan(2),
                                
                                Select::make('tk')
                                    ->label('Tanggungan (TK)')
                                    ->options([
                                        0 => 'TK/0 - Tidak ada tanggungan',
                                        1 => 'TK/1 - 1 tanggungan',
                                        2 => 'TK/2 - 2 tanggungan',
                                        3 => 'TK/3 - 3 tanggungan',
                                    ])
                                    ->native(false)
                                    ->default(0)
                                    ->visible(fn ($get) => $get('marital_status') === 'single')
                                    ->columnSpan(2),
                                
                                Select::make('k')
                                    ->label('Tanggungan (K)')
                                    ->options([
                                        0 => 'K/0 - Tidak ada tanggungan',
                                        1 => 'K/1 - 1 tanggungan',
                                        2 => 'K/2 - 2 tanggungan',
                                        3 => 'K/3 - 3 tanggungan',
                                    ])
                                    ->native(false)
                                    ->default(0)
                                    ->visible(fn ($get) => $get('marital_status') === 'married')
                                    ->columnSpan(2),
                            ]),
                    ])
            ]);
    }

    public function loadEmployees()
    {
        $this->employees = $this->client->employees()
            ->latest()
            ->get();
        
        $this->calculateStats();
    }

    public function calculateStats()
    {
        $this->stats = [
            'total' => $this->employees->count(),
            'active' => $this->employees->where('status', 'active')->count(),
            'inactive' => $this->employees->where('status', 'inactive')->count(),
            'tetap' => $this->employees->where('type', 'Karyawan Tetap')->count(),
        ];
    }

    public function openCreateModal()
    {
        $this->resetModalFields();
        $this->editingId = null;
        $this->dispatch('open-modal', id: 'employee-modal');
    }

    public function openEditModal($employeeId)
    {
        $employee = Employee::find($employeeId);
        
        if ($employee) {
            $this->editingId = $employeeId;
            $this->name = $employee->name;
            $this->npwp = $employee->npwp;
            $this->position = $employee->position;
            $this->marital_status = $employee->marital_status;
            $this->tk = $employee->tk;
            $this->k = $employee->k;
            $this->salary = $employee->salary;
            $this->status = $employee->status;
            $this->type = $employee->type;
            
            $this->dispatch('open-modal', id: 'employee-modal');
        }
    }

    public function saveEmployee()
    {
        $data = $this->form->getState();
        
        try {
            $employeeData = [
                'client_id' => $this->client->id,
                'name' => $data['name'],
                'npwp' => $data['npwp'] ?? null,
                'position' => $data['position'] ?? null,
                'marital_status' => $data['marital_status'],
                'tk' => $data['marital_status'] === 'single' ? ($data['tk'] ?? 0) : 0,
                'k' => $data['marital_status'] === 'married' ? ($data['k'] ?? 0) : 0,
                'salary' => $data['salary'] ?? null,
                'status' => $data['status'],
                'type' => $data['type'],
            ];

            if ($this->editingId) {
                Employee::find($this->editingId)->update($employeeData);
                $message = 'Data karyawan berhasil diperbarui!';
            } else {
                Employee::create($employeeData);
                $message = 'Karyawan berhasil ditambahkan!';
            }

            $this->closeModal();
            $this->loadEmployees();
            
            Notification::make()
                ->title('Berhasil!')
                ->body($message)
                ->success()
                ->duration(3000)
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menyimpan data karyawan: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function deleteConfirm($employeeId)
    {
        $this->employeeToDelete = $employeeId;
        $this->dispatch('open-modal', id: 'delete-employee-modal');
    }

    public function deleteEmployee()
    {
        try {
            if (!$this->employeeToDelete) {
                return;
            }
            
            $employee = Employee::find($this->employeeToDelete);
            if ($employee) {
                $employee->delete();
                $this->loadEmployees();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Karyawan berhasil dihapus!')
                    ->success()
                    ->duration(3000)
                    ->send();
            }
            
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menghapus karyawan: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function closeModal()
    {
        $this->resetModalFields();
        $this->dispatch('close-modal', id: 'employee-modal');
    }

    public function closeDeleteModal()
    {
        $this->employeeToDelete = null;
        $this->dispatch('close-modal', id: 'delete-employee-modal');
    }

    private function resetModalFields()
    {
        $this->name = '';
        $this->npwp = '';
        $this->position = '';
        $this->marital_status = 'single';
        $this->tk = 0;
        $this->k = 0;
        $this->salary = '';
        $this->status = 'active';
        $this->type = 'Harian';
        $this->editingId = null;
    }

    public function getTkKLabel($employee)
    {
        if ($employee->marital_status === 'single') {
            return 'TK/' . $employee->tk;
        } else {
            return 'K/' . $employee->k;
        }
    }

    #[On('refresh-employees')]
    public function refresh()
    {
        $this->loadEmployees();
    }

    public function render()
    {
        return view('livewire.client.management.karyawan-tab');
    }
}