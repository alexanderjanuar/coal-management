<?php

namespace App\Livewire\Client\Management;

use Livewire\Component;
use App\Models\Client;

class IdentitasTab extends Component
{
    public Client $client;

    public function mount(Client $client)
    {
        $this->client = $client->load([
            'accountRepresentative',
        ]);
    }

    public function getPkpStatusLabel()
    {
        return match ($this->client->pkp_status) {
            'PKP' => 'Pengusaha Kena Pajak',
            'Non-PKP' => 'Non Pengusaha Kena Pajak',
            default => 'Belum Ditentukan',
        };
    }

    public function getFormattedNpwp()
    {
        if (!$this->client->NPWP) {
            return '-';
        }

        $npwp = preg_replace('/[^0-9]/', '', $this->client->NPWP);

        if (strlen($npwp) === 15) {
            return substr($npwp, 0, 2) . '.' .
                substr($npwp, 2, 3) . '.' .
                substr($npwp, 5, 3) . '.' .
                substr($npwp, 8, 1) . '-' .
                substr($npwp, 9, 3) . '.' .
                substr($npwp, 12, 3);
        }

        return $this->client->NPWP;
    }

    public function hasEfin()
    {
        return !empty($this->client->EFIN);
    }

    public function render()
    {
        return view('livewire.client.management.identitas-tab');
    }
}
