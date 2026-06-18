<?php

namespace App\Livewire\Client\Management;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\ClientContact;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class IdentitasTab extends Component
{
    public Client $client;

    protected ?ClientContact $primaryContactCache = null;
    protected bool $primaryContactResolved = false;

    public function mount(Client $client)
    {
        $this->client = $client->load(['accountRepresentative', 'pic', 'contacts']);
    }

    /** Logo tersimpan di disk `public` (folder avatars); jatuh balik ke monogram bila kosong. */
    public function getLogoUrl(): ?string
    {
        $logo = $this->client->logo;
        if (! $logo) {
            return null;
        }
        if (str_starts_with($logo, 'http')) {
            return $logo;
        }
        return Storage::disk('public')->url($logo);
    }

    /** Kontak person utama (type `primary`, jika tidak ada ambil yang pertama). */
    public function primaryContact(): ?ClientContact
    {
        if (! $this->primaryContactResolved) {
            $contacts = $this->client->contacts;
            $this->primaryContactCache = $contacts->firstWhere('type', 'primary') ?? $contacts->first();
            $this->primaryContactResolved = true;
        }
        return $this->primaryContactCache;
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
     * Laporan kelengkapan data klien — dasar untuk ring & breakdown.
     * Hanya menghitung field yang BENAR-BENAR ada di skema. Field yang tidak
     * relevan (mis. Sub Tipe / PIC untuk klien Pribadi) di-skip, bukan dihitung kosong.
     *
     * @return array{groups: array<string,array>, filled:int, total:int, pct:int, missing:array<int,string>}
     */
    public function completeness(): array
    {
        $c        = $this->client;
        $isBadan  = $c->client_type === 'Badan';
        $contact  = $this->primaryContact();
        $hasPhone = $contact && (filled($contact->phone) || filled($contact->mobile));

        // AR (pejabat KPP) baru relevan kalau klien punya kontrak pajak aktif.
        $hasActiveContract = (bool) ($c->ppn_contract || $c->pph_contract
            || $c->bupot_contract || $c->pph_badan_contract);

        // null = tidak relevan untuk klien ini → tidak ikut dihitung.
        $defs = [
            'identitas' => [
                'label'  => 'Identitas Legal',
                'icon'   => 'building',
                'fields' => [
                    'Nama'       => filled($c->name),
                    'Tipe Klien' => filled($c->client_type),
                    'Sub Tipe'   => $isBadan ? filled($c->client_subtype) : null,
                    'Logo'       => filled($c->logo),
                ],
            ],
            'pajak' => [
                'label'  => 'Perpajakan',
                'icon'   => 'receipt',
                'fields' => [
                    // EFIN sengaja TIDAK dihitung — bersifat opsional.
                    'NPWP'       => filled($c->NPWP),
                    'Status PKP' => filled($c->pkp_status),
                ],
            ],
            'kontak' => [
                'label'  => 'Kontak & Alamat',
                'icon'   => 'mail',
                'fields' => [
                    'Email'   => filled($c->email),
                    'Telepon' => (bool) $hasPhone,
                    'Alamat'  => filled($c->adress),
                ],
            ],
            'pj' => [
                'label'  => 'Penanggung Jawab',
                'icon'   => 'user',
                'fields' => [
                    // AR wajib hanya saat ada kontrak pajak aktif; PIC hanya untuk Badan.
                    'Account Rep.' => $hasActiveContract ? filled($c->ar_id) : null,
                    'PIC'          => $isBadan ? filled($c->pic_id) : null,
                ],
            ],
        ];

        $groups = [];
        $sumFilled = 0;
        $sumTotal  = 0;

        foreach ($defs as $key => $def) {
            // Buang field yang null (tidak relevan) sebelum menghitung.
            $fields = array_filter($def['fields'], fn ($v) => $v !== null);
            $total  = \count($fields);
            $filled = \count(array_filter($fields));

            // Grup yang semua field-nya tidak relevan (mis. Penanggung Jawab untuk
            // klien Pribadi tanpa kontrak) di-skip — jangan tampil "0/0".
            if ($total === 0) {
                continue;
            }

            $sumFilled += $filled;
            $sumTotal  += $total;

            $groups[$key] = [
                'label'   => $def['label'],
                'icon'    => $def['icon'],
                'filled'  => $filled,
                'total'   => $total,
                'pct'     => $total > 0 ? (int) round(($filled / $total) * 100) : 0,
                'missing' => array_keys(array_filter($fields, fn ($v) => $v === false)),
            ];
        }

        return [
            'groups'  => $groups,
            'filled'  => $sumFilled,
            'total'   => $sumTotal,
            'pct'     => $sumTotal > 0 ? (int) round(($sumFilled / $sumTotal) * 100) : 0,
            'missing' => array_merge(...array_values(array_map(fn ($g) => $g['missing'], $groups)) ?: [[]]),
        ];
    }

    public function render()
    {
        return view('livewire.client.management.identitas-tab');
    }
}
