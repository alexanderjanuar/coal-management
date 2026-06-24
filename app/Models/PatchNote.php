<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatchNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'title',
        'description',
        'changes',
        'is_published',
        'released_at',
        'created_by',
    ];

    protected $casts = [
        'changes'      => 'array',
        'is_published' => 'boolean',
        'released_at'  => 'date',
    ];

    /** Tipe perubahan + label (dipakai di form & banner). */
    public const CHANGE_TYPES = [
        'feature'     => 'Fitur Baru',
        'improvement' => 'Peningkatan',
        'fix'         => 'Perbaikan',
    ];

    /** Area/modul tempat perubahan berada (ditampilkan sebagai label di tiap item). */
    public const AREAS = [
        'Klien'        => 'Klien',
        'Proyek'       => 'Proyek',
        'Pajak'        => 'Pajak',
        'Dokumen'      => 'Dokumen',
        'Tugas Harian' => 'Tugas Harian',
        'Sistem'       => 'Sistem',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
