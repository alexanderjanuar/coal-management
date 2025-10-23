<?php

namespace App\Livewire\Client\Components;

use Livewire\Component;
use App\Models\Client;
use App\Models\ClientAffiliate;

class IdentitasTab extends Component
{
    public Client $client;
    
    // Properties untuk form affiliate
    public bool $showAffiliateModal = false;
    public ?int $editingAffiliateId = null;
    public string $affiliateCompanyName = '';
    public string $affiliateRelationshipType = 'Afiliasi';
    public ?float $affiliateOwnershipPercentage = null;
    public string $affiliateNpwp = '';
    public ?int $affiliateAffiliatedClientId = null;
    public string $affiliateNotes = '';

    public function mount(Client $client)
    {
        // Load relasi affiliates dan contacts
        $this->client = $client->load([
            'affiliates' => function($query) {
                $query->where('status', 'active')
                      ->orderBy('relationship_type')
                      ->orderBy('company_name');
            },
            'contacts' => function($query) {
                $query->orderByRaw("CASE WHEN type = 'primary' THEN 0 ELSE 1 END")
                      ->orderBy('name');
            }
        ]);
    }

    public function openAffiliateModal()
    {
        $this->resetAffiliateForm();
        $this->showAffiliateModal = true;
    }

    public function closeAffiliateModal()
    {
        $this->showAffiliateModal = false;
        $this->resetAffiliateForm();
    }

    public function saveAffiliate()
    {
        $this->validate([
            'affiliateCompanyName' => 'required|string|max:255',
            'affiliateRelationshipType' => 'required|string',
            'affiliateOwnershipPercentage' => 'nullable|numeric|min:0|max:100',
            'affiliateNpwp' => 'nullable|string|max:20',
            'affiliateNotes' => 'nullable|string',
        ], [
            'affiliateCompanyName.required' => 'Nama perusahaan wajib diisi',
            'affiliateRelationshipType.required' => 'Hubungan perusahaan wajib dipilih',
            'affiliateOwnershipPercentage.numeric' => 'Kepemilikan harus berupa angka',
            'affiliateOwnershipPercentage.min' => 'Kepemilikan minimal 0%',
            'affiliateOwnershipPercentage.max' => 'Kepemilikan maksimal 100%',
        ]);

        if ($this->editingAffiliateId) {
            // Update existing affiliate
            $affiliate = ClientAffiliate::findOrFail($this->editingAffiliateId);
            $affiliate->update([
                'company_name' => $this->affiliateCompanyName,
                'relationship_type' => $this->affiliateRelationshipType,
                'ownership_percentage' => $this->affiliateOwnershipPercentage,
                'npwp' => $this->affiliateNpwp,
                'affiliated_client_id' => $this->affiliateAffiliatedClientId,
                'notes' => $this->affiliateNotes,
            ]);

            session()->flash('message', 'Perusahaan afiliasi berhasil diperbarui');
        } else {
            // Create new affiliate
            ClientAffiliate::create([
                'client_id' => $this->client->id,
                'company_name' => $this->affiliateCompanyName,
                'relationship_type' => $this->affiliateRelationshipType,
                'ownership_percentage' => $this->affiliateOwnershipPercentage,
                'npwp' => $this->affiliateNpwp,
                'affiliated_client_id' => $this->affiliateAffiliatedClientId,
                'notes' => $this->affiliateNotes,
                'status' => 'active',
            ]);

            session()->flash('message', 'Perusahaan afiliasi berhasil ditambahkan');
        }

        $this->closeAffiliateModal();
        
        // Reload affiliates
        $this->client->load(['affiliates' => function($query) {
            $query->where('status', 'active')
                  ->orderBy('relationship_type')
                  ->orderBy('company_name');
        }]);
    }

    public function editAffiliate($affiliateId)
    {
        $affiliate = ClientAffiliate::findOrFail($affiliateId);
        
        $this->editingAffiliateId = $affiliate->id;
        $this->affiliateCompanyName = $affiliate->company_name;
        $this->affiliateRelationshipType = $affiliate->relationship_type;
        $this->affiliateOwnershipPercentage = $affiliate->ownership_percentage;
        $this->affiliateNpwp = $affiliate->npwp ?? '';
        $this->affiliateAffiliatedClientId = $affiliate->affiliated_client_id;
        $this->affiliateNotes = $affiliate->notes ?? '';
        
        $this->showAffiliateModal = true;
    }

    public function deleteAffiliate($affiliateId)
    {
        $affiliate = ClientAffiliate::findOrFail($affiliateId);
        $affiliate->delete();

        session()->flash('message', 'Perusahaan afiliasi berhasil dihapus');
        
        // Reload affiliates
        $this->client->load(['affiliates' => function($query) {
            $query->where('status', 'active')
                  ->orderBy('relationship_type')
                  ->orderBy('company_name');
        }]);
    }

    private function resetAffiliateForm()
    {
        $this->editingAffiliateId = null;
        $this->affiliateCompanyName = '';
        $this->affiliateRelationshipType = 'Afiliasi';
        $this->affiliateOwnershipPercentage = null;
        $this->affiliateNpwp = '';
        $this->affiliateAffiliatedClientId = null;
        $this->affiliateNotes = '';
    }

    public function render()
    {
        return view('livewire.client.components.identitas-tab');
    }
}