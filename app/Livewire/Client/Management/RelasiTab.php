<?php

namespace App\Livewire\Client\Management;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Livewire\Component;

class RelasiTab extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->client = $client->load([
            'group',
            'group.clients' => fn ($query) => $query->orderBy('name'),
        ]);
    }

    /** URL edit klien ini (dipakai empty state untuk menghubungkan grup). */
    public function editUrl(): string
    {
        return ClientResource::getUrl('edit', ['record' => $this->client]);
    }

    public function render()
    {
        return view('livewire.client.management.relasi-tab');
    }
}
