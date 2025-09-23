<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Suggestion extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Admin who handled the suggestion
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    // Helper methods untuk context types
    public static function getContextTypes(): array
    {
        return [
            'general' => 'Umum',
            'project' => 'Manajemen Proyek',
            'daily_task' => 'Tugas Harian',
            'tax_report' => 'Laporan Pajak',
            'invoice' => 'Faktur & Invoice',
            'client' => 'Manajemen Klien',
            'employee' => 'Manajemen Karyawan',
            'user_interface' => 'Tampilan Interface',
            'system_performance' => 'Performa Sistem',
            'reporting' => 'Sistem Pelaporan',
            'workflow' => 'Alur Kerja',
            'notification' => 'Sistem Notifikasi',
            'dashboard' => 'Dashboard',
            'security' => 'Keamanan Sistem',
            'integration' => 'Integrasi Sistem',
            'mobile' => 'Aplikasi Mobile',
            'api' => 'API & Web Service',
            'database' => 'Database',
            'backup' => 'Backup & Recovery',
            'automation' => 'Otomatisasi'
        ];
    }

    public function getContextTypeLabel(): string
    {
        $contextTypes = self::getContextTypes();
        return $contextTypes[$this->context_type] ?? ucfirst($this->context_type);
    }

    public function getContextBadgeColor(): string
    {
        return match($this->context_type) {
            'project' => 'blue',
            'daily_task' => 'green',
            'tax_report' => 'yellow',
            'invoice' => 'orange',
            'client' => 'purple',
            'employee' => 'pink',
            'user_interface' => 'indigo',
            'system_performance' => 'red',
            'reporting' => 'amber',
            'workflow' => 'teal',
            'notification' => 'lime',
            'dashboard' => 'cyan',
            'security' => 'red',
            'integration' => 'violet',
            'mobile' => 'emerald',
            'api' => 'slate',
            'database' => 'stone',
            'backup' => 'neutral',
            'automation' => 'sky',
            default => 'gray'
        };
    }

    public function scopeByContext($query, $contextType)
    {
        return $query->where('context_type', $contextType);
    }
}
