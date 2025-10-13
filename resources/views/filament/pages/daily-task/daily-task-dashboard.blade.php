<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters Widget --}}
        @livewire('daily-task.dashboard.filters')
        
        {{-- Stats Cards Widget --}}
        @livewire('daily-task.dashboard.stats-overview')

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                @livewire('daily-task.dashboard.daily-task-timeline')
            </div>
            <div>
                @livewire('daily-task.dashboard.daily-task-status')
            </div>
        </div>

        {{-- NEW: Tasks by Status Component --}}
        @livewire('daily-task.dashboard.tasks-by-status')
    </div>
</x-filament-panels::page>