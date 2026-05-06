@php
    $activeCount   = $clients->where('status', 'Active')->count();
    $inactiveCount = $clients->count() - $activeCount;
@endphp

<div class="flex flex-col gap-5">

    {{-- Stats pill row --}}
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 ring-1 ring-primary-100 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/20">
            <x-heroicon-m-users class="h-3.5 w-3.5" />
            {{ $clients->count() }} total
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-3 py-1 text-xs font-semibold text-success-700 ring-1 ring-success-100 dark:bg-success-500/10 dark:text-success-400 dark:ring-success-500/20">
            <x-heroicon-m-check-circle class="h-3.5 w-3.5" />
            {{ $activeCount }} aktif
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-danger-50 px-3 py-1 text-xs font-semibold text-danger-700 ring-1 ring-danger-100 dark:bg-danger-500/10 dark:text-danger-400 dark:ring-danger-500/20">
            <x-heroicon-m-x-circle class="h-3.5 w-3.5" />
            {{ $inactiveCount }} tidak aktif
        </span>
    </div>

    {{-- Client cards --}}
    @if ($clients->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 py-16 dark:border-gray-700">
            <x-heroicon-o-users class="h-12 w-12 text-gray-300 dark:text-gray-600" />
            <p class="mt-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Belum ada client</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Tambahkan client ke grup ini melalui menu aksi.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($clients as $client)
                @php
                    $isActive = $client->status === 'Active';
                    $logoUrl  = $client->logo
                        ? \Illuminate\Support\Facades\Storage::url($client->logo)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($client->name) . '&color=7F9CF5&background=EBF4FF&size=64';
                    $viewUrl  = route('filament.admin.resources.clients.view', $client);
                @endphp

                <a href="{{ $viewUrl }}"
                   class="group flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all duration-150
                          hover:border-primary-300 hover:shadow-md
                          dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-600">

                    <img src="{{ $logoUrl }}"
                         alt="{{ $client->name }}"
                         class="h-11 w-11 shrink-0 rounded-full object-cover ring-2 ring-white dark:ring-gray-800">

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="truncate text-sm font-semibold text-gray-900 transition-colors
                                      group-hover:text-primary-600
                                      dark:text-white dark:group-hover:text-primary-400">
                                {{ $client->name }}
                            </p>
                            <span @class([
                                'shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $isActive,
                                'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400'   => ! $isActive,
                            ])>
                                {{ $isActive ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>

                        @if ($client->email)
                            <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                {{ $client->email }}
                            </p>
                        @endif

                        @if ($client->NPWP)
                            <p class="mt-0.5 truncate font-mono text-xs text-gray-400 dark:text-gray-500">
                                {{ $client->NPWP }}
                            </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
