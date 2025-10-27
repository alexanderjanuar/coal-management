<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tab Navigation - Pill Style --}}
        <div class="w-full rounded-3xl bg-gray-100 p-1 dark:bg-gray-800">
            <nav class="grid grid-cols-8 gap-1" aria-label="Tabs">
                @foreach($this->getTabs() as $tabKey => $tabLabel)
                <button wire:click="$set('activeTab', '{{ $tabKey }}')"
                    @class([ 'relative rounded-3xl px-3 py-2.5 text-sm font-medium'
                    , 'bg-white text-gray-900 shadow-sm dark:bg-gray-900 dark:text-white'=> $activeTab === $tabKey,
                    'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' => $activeTab !==
                    $tabKey,
                    ])
                    type="button"
                    >
                    {{ $tabLabel }}
                </button>
                @endforeach
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="relative">
            @switch($activeTab)
            @case('identitas')
            <div x-data x-init="$el.style.opacity = 0; setTimeout(() => $el.style.opacity = 1, 10)" class="">
                @livewire('client.components.identitas-tab', ['client' => $record])
            </div>
            @break

            @case('perpajakan')
            <div x-data x-init="$el.style.opacity = 0; setTimeout(() => $el.style.opacity = 1, 10)" class="">
                @livewire('client.components.perpajakan-tab', ['client' => $record])
            </div>
            @break

            @case('kontrak')
            <div x-data class="rounded-3xl bg-white p-6 shadow transition-opacity duration-300 dark:bg-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Kontrak</h3>
                <p class="text-gray-600 dark:text-gray-400">Konten untuk tab Kontrak akan ditampilkan di sini.</p>
                {{-- @livewire('client.kontrak-tab', ['client' => $record]) --}}
            </div>
            @break

            @case('dokumen')
            <div x-data x-init="$el.style.opacity = 0; setTimeout(() => $el.style.opacity = 1, 10)"
                @livewire('client.components.dokumen-tab', ['client'=> $record])
            </div>
            @break

            @case('komunikasi')
            <div x-data x-init="$el.style.opacity = 0; setTimeout(() => $el.style.opacity = 1, 10)" class="">
                @livewire('client.components.komunikasi-tab', ['client' => $record])
            </div>
            @break

            @case('compliance')
            <div x-data x-init="$el.style.opacity = 0; setTimeout(() => $el.style.opacity = 1, 10)" class="">
                @livewire('client.components.compliance-tab', ['client' => $record], key('compliance-tab-'.$record->id))
            </div>
            @break

            @case('karyawan')
            <div x-data x-init="$el.style.opacity = 0; setTimeout(() => $el.style.opacity = 1, 10)"
                class="">
                @livewire('client.components.karyawan-tab', ['client' => $record], key('karyawan-tab-'.$record->id))
            </div>
            @break

            @case('tim')
            <div x-data x-init="$el.style.opacity = 0; setTimeout(() => $el.style.opacity = 1, 10)" class="">
                @livewire('client.components.tim-tab', ['client' => $record], key('tim-tab-'.$record->id))
            </div>
            @break
            @endswitch
        </div>
    </div>
</x-filament-panels::page>