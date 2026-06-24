<?php

namespace App\Livewire;

use App\Models\PatchNote;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Banner patch notes — container yang bisa ditutup, ditampilkan di atas Dashboard.
 *
 * - Muncul bila ada patch terbit yang belum dilihat user (id > last_seen_patch_id).
 * - Tombol tutup (x) menandai semua patch terbit sebagai sudah dilihat → tak muncul
 *   lagi sampai ada patch baru.
 * - Hanya untuk user non-client.
 */
class PatchNotesBanner extends Component
{
    public bool $visible = false;

    /** Data patch siap render (array, bukan model). */
    public array $patches = [];

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user || $user->hasAnyRole(['client'])) {
            return;
        }

        $unseen = PatchNote::published()
            ->when($user->last_seen_patch_id, fn ($q) => $q->where('id', '>', $user->last_seen_patch_id))
            ->orderByDesc('released_at')
            ->orderByDesc('id')
            ->get();

        if ($unseen->isNotEmpty()) {
            $this->patches = $this->mapPatches($unseen);
            $this->visible = true;
        }
    }

    public function dismiss(): void
    {
        $user = auth()->user();

        if ($user && ($latestId = PatchNote::published()->max('id'))) {
            $user->forceFill(['last_seen_patch_id' => $latestId])->save();
        }

        $this->visible = false;
        $this->patches = [];
    }

    protected function mapPatches(Collection $collection): array
    {
        return $collection->map(fn (PatchNote $p) => [
            'version'     => $p->version,
            'title'       => $p->title,
            'description' => $p->description,
            'released_at' => optional($p->released_at)->translatedFormat('d M Y'),
            'changes'     => collect($p->changes ?? [])
                ->map(fn ($c) => [
                    'type' => $c['type'] ?? 'improvement',
                    'area' => $c['area'] ?? null,
                    'text' => $c['text'] ?? '',
                ])
                ->filter(fn ($c) => $c['text'] !== '')
                ->values()
                ->all(),
        ])->all();
    }

    public function render()
    {
        return view('livewire.patch-notes-banner');
    }
}
