{{-- resources/views/filament/pages/daily-task-list.blade.php --}}
<x-filament-panels::page>
    {{-- Main Task List Component --}}
    <livewire:daily-task.components.daily-task-list-component />
    @livewire('notifications')
    <x-filament-actions::modals />
</x-filament-panels::page>