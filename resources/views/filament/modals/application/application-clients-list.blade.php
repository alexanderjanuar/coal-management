{{-- resources/views/filament/modals/application-clients-list.blade.php --}}
<div class="space-y-4">
    @php
        $clients = $record->applicationClients()->with('client')->where('is_active', true)->get();
    @endphp

    @if($clients->isEmpty())
        <div class="text-center py-8">
            <x-heroicon-o-users class="w-16 h-16 mx-auto text-gray-400" />
            <p class="mt-4 text-gray-500">Belum ada klien yang menggunakan aplikasi ini</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($clients as $appClient)
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold">{{ $appClient->client->name }}</div>
                            <div class="text-sm text-gray-500">Username: {{ $appClient->username }}</div>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            @if($appClient->last_used_at)
                                Terakhir digunakan:<br>
                                {{ $appClient->last_used_at->diffForHumans() }}
                            @else
                                <span class="text-gray-400">Belum pernah digunakan</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="text-sm text-gray-500 text-center pt-2">
            Total: {{ $clients->count() }} klien aktif
        </div>
    @endif
</div>