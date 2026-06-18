<?php

namespace App\Livewire\Client\Management;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class KontrakTab extends Component
{
    use WithFileUploads;

    public Client $client;

    /** File baru yang dipilih (TemporaryUploadedFile sebelum disimpan). */
    public $upload = null;

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function getContractUrlProperty(): ?string
    {
        $path = $this->client->contract_file;
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        return Storage::disk('public')->url($path);
    }

    public function getContractExtProperty(): ?string
    {
        $path = $this->client->contract_file;
        return $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
    }

    public function getIsPdfProperty(): bool
    {
        return $this->contractExt === 'pdf';
    }

    public function getIsImageProperty(): bool
    {
        return in_array($this->contractExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    public function getFileNameProperty(): ?string
    {
        $path = $this->client->contract_file;
        return $path ? basename($path) : null;
    }

    public function editUrl(): string
    {
        return ClientResource::getUrl('edit', ['record' => $this->client]);
    }

    public function save(): void
    {
        $this->validate(
            ['upload' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240'],
            [
                'upload.required' => 'Pilih dokumen kontrak terlebih dahulu.',
                'upload.mimes'    => 'Format harus PDF atau gambar (jpg, png, webp).',
                'upload.max'      => 'Ukuran maksimal 10 MB.',
            ],
            ['upload' => 'Dokumen kontrak'],
        );

        // Hapus file lama agar tidak menumpuk.
        $old = $this->client->contract_file;
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        $path = $this->upload->store('client-contracts', 'public');

        $this->client->forceFill(['contract_file' => $path])->save();
        $this->client->logActivity('contract_uploaded', "Dokumen kontrak klien '{$this->client->name}' diperbarui");

        $this->reset('upload');

        Notification::make()->success()->title('Dokumen kontrak tersimpan')->send();
    }

    public function remove(): void
    {
        $old = $this->client->contract_file;
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        $this->client->forceFill(['contract_file' => null])->save();
        $this->client->logActivity('contract_removed', "Dokumen kontrak klien '{$this->client->name}' dihapus");

        Notification::make()->success()->title('Dokumen kontrak dihapus')->send();
    }

    public function render()
    {
        return view('livewire.client.management.kontrak-tab');
    }
}
