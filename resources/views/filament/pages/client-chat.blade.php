<x-filament-panels::page>
    <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-800" style="height: calc(100vh - 10rem);">
        @livewire('client.panel.chat-panel', ['showAllClients' => true])
    </div>
</x-filament-panels::page>
