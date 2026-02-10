<?php

namespace App\Livewire\Client\Panel;

use Livewire\Component;
use App\Models\Client;
use App\Models\UserClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProfileTab extends Component
{
    public Collection $clients;
    public ?int $selectedClientId = null;
    public bool $isLoading = true;
    public bool $showCredentials = false;

    protected int $cacheDuration = 300;

    protected $listeners = ['refreshProfile' => 'refresh', 'client-changed' => 'handleClientChange'];

    public function mount()
    {
        $this->loadClientData();
    }

    protected function loadClientData()
    {
        $this->isLoading = true;

        $this->clients = UserClient::query()
            ->where('user_id', auth()->id())
            ->with(['client' => fn($q) => $q->select('id', 'name', 'logo', 'status', 'client_type')])
            ->get()
            ->pluck('client')
            ->filter();

        if ($this->clients->isNotEmpty() && !$this->selectedClientId) {
            $this->selectedClientId = $this->clients->first()->id;
        }

        $this->isLoading = false;
    }

    public function getSelectedClientProperty(): ?Client
    {
        if (!$this->selectedClientId) {
            return null;
        }

        $userId = auth()->id();
        $clientId = $this->selectedClientId;

        return Cache::remember(
            "profile_client_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () use ($clientId) {
                return Client::query()
                    ->where('id', $clientId)
                    ->with([
                        'pic:id,name,nik',
                        'accountRepresentative:id,name,email,phone_number,kpp',
                        'clientCredential',
                        'contacts' => fn($q) => $q->where('is_active', true)->limit(5),
                    ])
                    ->first();
            }
        );
    }

    public function selectClient(int $clientId)
    {
        $this->selectedClientId = $clientId;
        $this->clearClientCache();
        $this->dispatch('client-changed', clientId: $clientId);
    }

    public function handleClientChange(int $clientId)
    {
        $this->selectedClientId = $clientId;
        $this->clearClientCache();
    }

    protected function clearClientCache()
    {
        if (!$this->selectedClientId) {
            return;
        }

        $userId = auth()->id();
        $clientId = $this->selectedClientId;

        Cache::forget("profile_client_{$userId}_{$clientId}");
    }

    public function toggleCredentials()
    {
        $this->showCredentials = !$this->showCredentials;
    }

    public function getContractStatusProperty(): array
    {
        $client = $this->selectedClient;

        if (!$client) {
            return [];
        }

        return [
            [
                'name' => 'PPN',
                'active' => $client->ppn_contract ?? false,
                'description' => 'Pajak Pertambahan Nilai',
                'icon' => 'document-currency-dollar',
            ],
            [
                'name' => 'PPh',
                'active' => $client->pph_contract ?? false,
                'description' => 'Pajak Penghasilan',
                'icon' => 'banknotes',
            ],
            [
                'name' => 'Bupot',
                'active' => $client->bupot_contract ?? false,
                'description' => 'Bukti Potong',
                'icon' => 'document-check',
            ],
            [
                'name' => 'PPh Badan',
                'active' => $client->pph_badan_contract ?? false,
                'description' => 'PPh Badan Tahunan',
                'icon' => 'building-office',
            ],
        ];
    }

    public function maskString(string $value, int $visibleChars = 3): string
    {
        if (strlen($value) <= $visibleChars * 2) {
            return str_repeat('*', strlen($value));
        }

        $start = substr($value, 0, $visibleChars);
        $end = substr($value, -$visibleChars);
        $middle = str_repeat('*', max(strlen($value) - ($visibleChars * 2), 4));

        return $start . $middle . $end;
    }

    public function refresh()
    {
        $this->clearClientCache();
        $this->loadClientData();
        $this->dispatch('profile-refreshed');
    }

    public function render()
    {
        return view('livewire.client.panel.profile-tab', [
            'selectedClient' => $this->selectedClient,
            'contractStatus' => $this->contractStatus,
        ]);
    }
}
