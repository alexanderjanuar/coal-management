<?php

namespace App\Livewire\Client\Management;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Livewire\Component;

class IdentitasTab extends Component
{
    public Client $client;

    public function mount(Client $client)
    {
        $this->client = $client->load(['accountRepresentative']);
    }

    public function getPkpStatusLabel(): string
    {
        return match ($this->client->pkp_status) {
            'PKP'     => 'Pengusaha Kena Pajak',
            'Non-PKP' => 'Non Pengusaha Kena Pajak',
            default   => 'Belum Ditentukan',
        };
    }

    public function getFormattedNpwp(): ?string
    {
        if (! $this->client->NPWP) {
            return null;
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

    /** Up-to-2-letter monogram from the company name for the hero avatar. */
    public function getInitials(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->client->name));
        $letters = collect($words)
            ->filter()
            ->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('');

        return $letters ?: '?';
    }

    /** Normalize an Indonesian phone number for `wa.me/...` links. */
    public function whatsAppUrl(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if ($clean === '') {
            return null;
        }
        // Local 0xxx → 62xxx for international wa.me format
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }
        return "https://wa.me/{$clean}";
    }

    public function getEditUrl(): string
    {
        return ClientResource::getUrl('edit', ['record' => $this->client]);
    }

    /**
     * Compute filled vs total for a section so the header can show "4/5 terisi"
     * — the building block for the upcoming completeness % feature.
     */
    public function completeness(array $values): array
    {
        $total  = count($values);
        $filled = count(array_filter($values, fn ($v) => filled($v)));
        return [
            'filled' => $filled,
            'total'  => $total,
            'pct'    => $total > 0 ? (int) round(($filled / $total) * 100) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.client.management.identitas-tab');
    }
}
