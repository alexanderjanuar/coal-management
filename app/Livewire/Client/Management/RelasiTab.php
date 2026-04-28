<?php

namespace App\Livewire\Client\Management;

use App\Models\Client;
use App\Models\ClientAffiliate;
use Livewire\Component;

class RelasiTab extends Component
{
    public Client $client;

    public ?int $editingAffiliateId = null;
    public string $affiliateCompanyName = '';
    public string $affiliateRelationshipType = 'Afiliasi';
    public ?float $affiliateOwnershipPercentage = null;
    public string $affiliateNpwp = '';
    public ?int $affiliateAffiliatedClientId = null;
    public string $affiliateNotes = '';

    public function mount(Client $client): void
    {
        $this->client = $client->load([
            'affiliates' => function ($query) {
                $query->where('status', 'active')
                    ->orderBy('relationship_type')
                    ->orderBy('company_name');
            },
            'contacts' => function ($query) {
                $query->orderByRaw("CASE WHEN type = 'primary' THEN 0 ELSE 1 END")
                    ->orderBy('name');
            },
        ]);
    }

    public function openAffiliateModal(): void
    {
        $this->resetAffiliateForm();
        $this->dispatch('open-modal', id: 'affiliate-modal');
    }

    public function closeAffiliateModal(): void
    {
        $this->resetAffiliateForm();
        $this->dispatch('close-modal', id: 'affiliate-modal');
    }

    public function saveAffiliate(): void
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
            $affiliate = ClientAffiliate::findOrFail($this->editingAffiliateId);
            $affiliate->update([
                'company_name' => $this->affiliateCompanyName,
                'relationship_type' => $this->affiliateRelationshipType,
                'ownership_percentage' => $this->affiliateOwnershipPercentage,
                'npwp' => $this->affiliateNpwp,
                'affiliated_client_id' => $this->affiliateAffiliatedClientId,
                'notes' => $this->affiliateNotes,
            ]);

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Perusahaan afiliasi berhasil diperbarui'
            );
        } else {
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

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Perusahaan afiliasi berhasil ditambahkan'
            );
        }

        $this->closeAffiliateModal();
        $this->reloadAffiliates();
    }

    public function editAffiliate(int $affiliateId): void
    {
        $affiliate = ClientAffiliate::findOrFail($affiliateId);

        $this->editingAffiliateId = $affiliate->id;
        $this->affiliateCompanyName = $affiliate->company_name;
        $this->affiliateRelationshipType = $affiliate->relationship_type;
        $this->affiliateOwnershipPercentage = $affiliate->ownership_percentage;
        $this->affiliateNpwp = $affiliate->npwp ?? '';
        $this->affiliateAffiliatedClientId = $affiliate->affiliated_client_id;
        $this->affiliateNotes = $affiliate->notes ?? '';

        $this->dispatch('open-modal', id: 'affiliate-modal');
    }

    public function deleteAffiliate(int $affiliateId): void
    {
        ClientAffiliate::findOrFail($affiliateId)->delete();

        $this->dispatch(
            'notify',
            type: 'success',
            message: 'Perusahaan afiliasi berhasil dihapus'
        );

        $this->reloadAffiliates();
    }

    public function getAvailableClientsProperty(): array
    {
        return Client::where('id', '!=', $this->client->id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getRelationshipTypesProperty(): array
    {
        return ClientAffiliate::getRelationshipTypes();
    }

    private function reloadAffiliates(): void
    {
        $this->client->load(['affiliates' => function ($query) {
            $query->where('status', 'active')
                ->orderBy('relationship_type')
                ->orderBy('company_name');
        }]);
    }

    private function resetAffiliateForm(): void
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
        return view('livewire.client.management.relasi-tab');
    }
}
