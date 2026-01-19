<?php

namespace App\Livewire\Client\Management;

use App\Models\Client;
use Livewire\Component;

class PerpajakanTab extends Component
{
    public Client $client;

    public function mount(Client $client)
    {
        // Load relasi yang diperlukan untuk informasi perpajakan
        $this->client = $client->load([
            'accountRepresentative', // Untuk data Account Representative
            'contacts'
        ]);
    }

    /**
     * Get status PKP dalam format yang mudah dibaca
     */
    public function getPkpStatusLabel()
    {
        return match($this->client->pkp_status) {
            'PKP' => 'Pengusaha Kena Pajak',
            'Non-PKP' => 'Non Pengusaha Kena Pajak',
            default => 'Belum Ditentukan'
        };
    }

    /**
     * Format NPWP dengan pemisah yang sesuai
     */
    public function getFormattedNpwp()
    {
        if (!$this->client->NPWP) {
            return '-';
        }

        $npwp = preg_replace('/[^0-9]/', '', $this->client->NPWP);
        
        if (strlen($npwp) === 15) {
            // Format: XX.XXX.XXX.X-XXX.XXX
            return substr($npwp, 0, 2) . '.' . 
                   substr($npwp, 2, 3) . '.' . 
                   substr($npwp, 5, 3) . '.' . 
                   substr($npwp, 8, 1) . '-' . 
                   substr($npwp, 9, 3) . '.' . 
                   substr($npwp, 12, 3);
        }

        return $this->client->NPWP;
    }

    /**
     * Get informasi kontrak perpajakan aktif
     */
    public function getActiveContracts()
    {
        $contracts = [];
        
        if ($this->client->ppn_contract) {
            $contracts[] = 'PPN';
        }
        
        if ($this->client->pph_contract) {
            $contracts[] = 'PPh';
        }
        
        if ($this->client->bupot_contract) {
            $contracts[] = 'Bukti Potong';
        }

        return $contracts;
    }

    /**
     * Check apakah client memiliki EFIN
     */
    public function hasEfin()
    {
        return !empty($this->client->EFIN);
    }

    public function render()
    {
        return view('livewire.client.management.perpajakan-tab');
    }
}