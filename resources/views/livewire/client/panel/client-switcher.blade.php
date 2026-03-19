{{-- Top Navigation: Logo + Client Switcher --}}
{{-- Renders at panels::topbar.start. Hides Filament's own brand logo via CSS to avoid duplication. --}}
<div class="fi-client-brand-switcher flex items-center gap-3">

    {{-- Brand Logo --}}
    <a href="{{ filament()->getHomeUrl() ?? '#' }}" class="flex items-center flex-shrink-0 me-2 lg:me-6">
        <img
            src="{{ asset('images/Logo/OnlyLogo.png') }}"
            alt="Logo"
            style="height: 3rem;"
            class="block"
        >
    </a>

    {{-- Client Switcher — only shown when user has 2+ clients --}}
    @if($clients->count() > 1)
        <div class="relative flex items-center" x-data="{ open: false }">

            {{-- Trigger button --}}
            <button
                @click="open = !open"
                type="button"
                class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-white/10 shadow-sm transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
            >
                <span class="max-w-[160px] truncate hidden sm:block">{{ $selectedClient?->name ?? 'Pilih Klien' }}</span>
                <x-heroicon-o-chevron-up-down class="flex-shrink-0 h-3.5 w-3.5 text-gray-400" />
            </button>

            {{-- Dropdown --}}
            <div
                x-show="open"
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute top-full left-0 mt-2 min-w-[220px] rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 shadow-xl ring-1 ring-black/5 overflow-hidden z-50"
                x-cloak
            >
                <div class="px-3 py-2 border-b border-gray-100 dark:border-white/5">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">Ganti Klien</p>
                </div>
                <ul class="py-1">
                    @foreach($clients as $client)
                        <li>
                            <button
                                wire:click="switchClient({{ $client->id }})"
                                @click="open = false"
                                type="button"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm text-left transition-colors duration-100 focus:outline-none
                                    {{ $client->id === $selectedClientId
                                        ? 'bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 font-semibold'
                                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5' }}"
                            >
                                <span class="flex-shrink-0 h-6 w-6 rounded overflow-hidden border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    @if($client->logo)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($client->logo) }}" alt="{{ $client->name }}" class="h-full w-full object-contain">
                                    @else
                                        <x-heroicon-o-building-office-2 class="h-3.5 w-3.5 text-gray-400" />
                                    @endif
                                </span>
                                <span class="flex-1 truncate">{{ $client->name }}</span>
                                @if($client->id === $selectedClientId)
                                    <x-heroicon-s-check class="flex-shrink-0 h-4 w-4 text-primary-600 dark:text-primary-400" />
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Hide Filament's own brand logo (the .me-6 div) to avoid showing the logo twice --}}
    <style>
        .fi-topbar-with-navigation nav > .me-6 { display: none !important; }
    </style>
</div>