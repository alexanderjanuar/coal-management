<?php

namespace App\Livewire\Client\Management;

use App\Models\Client;
use App\Models\ClientCommunication;
use Livewire\Component;

class KomunikasiTab extends Component
{
    public Client $client;
    public $communications = [];
    
    // Detail modal only
    public $viewingCommunication = null;

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->loadCommunications();
    }

    public function loadCommunications()
    {
        $this->communications = $this->client->communications()
            ->with('user')
            ->latest('communication_date')
            ->latest('communication_time_start')
            ->get();
    }

    public function openDetailModal($communicationId)
    {
        $this->viewingCommunication = ClientCommunication::with('user')
            ->find($communicationId);
        
        if ($this->viewingCommunication) {
            $this->dispatch('open-modal', id: 'detail-communication-modal');
        }
    }

    public function closeDetailModal()
    {
        $this->viewingCommunication = null;
        $this->dispatch('close-modal', id: 'detail-communication-modal');
    }

    public function render()
    {
        return view('livewire.client.management.komunikasi-tab');
    }
}