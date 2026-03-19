<?php

namespace App\Livewire\Client\Panel;

use Livewire\Component;
use App\Models\Client;
use App\Models\UserClient;
use Illuminate\Support\Collection;

class ClientSwitcher extends Component
{
    public Collection $clients;
    public ?int $selectedClientId = null;

    public function mount(): void
    {
        $this->loadClients();

        // Restore from session or default to first client
        $sessionClientId = session('client_panel_selected_client_id');

        if ($sessionClientId && $this->clients->contains('id', $sessionClientId)) {
            $this->selectedClientId = (int) $sessionClientId;
        } elseif ($this->clients->isNotEmpty()) {
            $this->selectedClientId = $this->clients->first()->id;
            session(['client_panel_selected_client_id' => $this->selectedClientId]);
        }
    }

    protected function loadClients(): void
    {
        $clientIds = UserClient::where('user_id', auth()->id())->pluck('client_id');

        $this->clients = Client::whereIn('id', $clientIds)
            ->select(['id', 'name', 'logo'])
            ->orderBy('name')
            ->get();
    }

    public function switchClient(int $clientId): void
    {
        if (!$this->clients->contains('id', $clientId)) {
            return;
        }

        $this->selectedClientId = $clientId;
        session(['client_panel_selected_client_id' => $clientId]);

        // Broadcast to all tab components
        $this->dispatch('client-switched', clientId: $clientId);
    }

    public function getSelectedClientProperty(): ?Client
    {
        if (!$this->selectedClientId) {
            return null;
        }
        return $this->clients->firstWhere('id', $this->selectedClientId);
    }

    public function render()
    {
        return view('livewire.client.panel.client-switcher', [
            'selectedClient' => $this->selectedClient,
        ]);
    }
}
