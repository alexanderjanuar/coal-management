<x-filament-panels::page class="w-full">
    {{-- Process Steps --}}
    <div class="mb-8">
        <div class="flex items-center justify-between max-w-4xl mx-auto">
            @foreach ($processSteps as $index => $step)
                <div class="flex flex-col items-center text-center">
                    <div @class([
                        'w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold',
                        'bg-primary-500' => $index < 4,
                        'bg-gray-200' => $index >= 4,
                    ])>
                        {{ $step['number'] }}
                    </div>
                    <div class="mt-2">
                        <p class="font-medium text-sm">{{ $step['title'] }}</p>
                        <p class="text-xs text-gray-500">{{ $step['subtitle'] }}</p>
                    </div>
                </div>
                @if ($index < count($processSteps) - 1)
                    <div class="flex-1 h-0.5 bg-primary-500 mx-4"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Clients and Tasks List --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h2 class="text-lg font-medium">Shipping Progress</h2>
        </div>
        
        @foreach ($clients as $client)
            <div class="border-b last:border-0">
                <div class="p-4 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center">
                            {{ substr($client->name, 0, 1) }}
                        </div>
                        <span class="font-medium">{{ $client->name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($client->logo)
                            <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->name }}" class="w-6 h-6 object-contain">
                        @endif
                        <span class="text-sm text-gray-500">{{ $client->email }}</span>
                    </div>
                </div>

                @foreach ($client->progress as $progress)
                    <div class="pl-12 pr-4 py-3 border-t">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <x-filament::badge
                                    :color="match($progress->status) {
                                        'done' => 'success',
                                        'in progress' => 'warning',
                                        'delayed' => 'danger',
                                        'draft' => 'secondary',
                                        default => 'secondary'
                                    }"
                                >
                                    {{ ucfirst($progress->status) }}
                                </x-filament::badge>
                                <span class="text-sm text-gray-600">
                                    {{ $progress->start_time ? "Started: ".date('H:i', strtotime($progress->start_time)) : 'Not started' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="text-primary-500 hover:text-primary-600">
                                    <x-heroicon-o-document class="w-5 h-5" />
                                </button>
                                <button class="text-primary-500 hover:text-primary-600">
                                    <x-heroicon-o-pencil class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        @foreach ($progress->tasks as $task)
                            <div class="ml-4 mb-2 last:mb-0 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <x-filament::badge
                                        :color="match($task->status) {
                                            'done' => 'success',
                                            'in progress' => 'warning',
                                            'delayed' => 'danger',
                                            'draft' => 'secondary',
                                            default => 'secondary'
                                        }"
                                        size="sm"
                                    >
                                        {{ ucfirst($task->status) }}
                                    </x-filament::badge>
                                    <span class="text-sm">{{ $task->title }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    @if ($task->documents->count() > 0)
                                        <span class="text-xs text-gray-500">
                                            {{ $task->documents->count() }} document(s)
                                        </span>
                                    @endif
                                    <button class="text-gray-400 hover:text-primary-500">
                                        <x-heroicon-o-paper-clip class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</x-filament-panels::page>