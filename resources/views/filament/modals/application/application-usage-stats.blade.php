{{-- resources/views/filament/components/application-usage-stats.blade.php --}}
<div class="grid grid-cols-3 gap-4">
    <div class="text-center">
        <div class="text-2xl font-bold text-blue-600">{{ $totalClients }}</div>
        <div class="text-sm text-gray-500">Total Klien</div>
    </div>

    <div class="text-center">
        <div class="text-2xl font-bold text-green-600">{{ $activeClients }}</div>
        <div class="text-sm text-gray-500">Klien Aktif</div>
    </div>

    <div class="text-center">
        @if($lastUsed)
        <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($lastUsed)->diffForHumans() }}</div>
        <div class="text-xs text-gray-500">Terakhir Digunakan</div>
        @else
        <div class="text-sm text-gray-400">Belum Pernah</div>
        <div class="text-xs text-gray-500">Digunakan</div>
        @endif
    </div>
</div>