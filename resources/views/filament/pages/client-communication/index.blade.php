<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Jadwal Komunikasi Klien
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola dan pantau semua jadwal komunikasi dengan klien Anda
                </p>
            </div>

            {{-- Create Button --}}
            <a href="{{ route('filament.admin.pages.client-communication.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Komunikasi Baru</span>
            </a>
        </div>

        {{-- Filters and Tabs Section --}}
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            {{-- Date Range Filter --}}
            <div class="flex items-center gap-3">
                <div class="w-80">
                    {{ $this->form }}
                </div>

                @if($event_period && is_array($event_period) && (isset($event_period['start']) ||
                isset($event_period['end'])))
                <button wire:click="clearDateRange"
                    class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                    Clear Filter
                </button>
                @endif
            </div>

            {{-- Status Tabs with Sliding Animation --}}
            <div class="relative inline-flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                {{-- Sliding Background Indicator --}}
                @php
                $tabPositions = [
                'all' => 0,
                'scheduled' => 1,
                'completed' => 2,
                'cancelled' => 3,
                'rescheduled' => 4
                ];
                $currentPosition = $tabPositions[$activeTab] ?? 0;
                @endphp
                <div class="absolute h-[calc(100%-8px)] bg-white dark:bg-gray-900 rounded-md shadow-sm transition-all duration-300 ease-in-out"
                    style="
                        width: {{ $activeTab === 'all' ? '44px' : ($activeTab === 'scheduled' ? '84px' : ($activeTab === 'completed' ? '86px' : ($activeTab === 'cancelled' ? '80px' : '96px'))) }};
                        left: {{ 
                            match($activeTab) {
                                'all' => '4px',
                                'scheduled' => '52px',
                                'completed' => '144px',
                                'cancelled' => '238px',
                                'rescheduled' => '326px',
                                default => '4px'
                            }
                        }};
                    "></div>

                <button wire:click="setActiveTab('all')"
                    class="relative z-10 px-4 py-1.5 text-xs font-medium rounded-md transition-colors duration-200 {{ $activeTab === 'all' ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    All
                </button>
                <button wire:click="setActiveTab('scheduled')"
                    class="relative z-10 px-4 py-1.5 text-xs font-medium rounded-md transition-colors duration-200 {{ $activeTab === 'scheduled' ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Scheduled
                </button>
                <button wire:click="setActiveTab('completed')"
                    class="relative z-10 px-4 py-1.5 text-xs font-medium rounded-md transition-colors duration-200 {{ $activeTab === 'completed' ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Completed
                </button>
                <button wire:click="setActiveTab('cancelled')"
                    class="relative z-10 px-4 py-1.5 text-xs font-medium rounded-md transition-colors duration-200 {{ $activeTab === 'cancelled' ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Cancelled
                </button>
                <button wire:click="setActiveTab('rescheduled')"
                    class="relative z-10 px-4 py-1.5 text-xs font-medium rounded-md transition-colors duration-200 {{ $activeTab === 'rescheduled' ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Rescheduled
                </button>
            </div>
        </div>

        {{-- Communications List --}}
        <div class="space-y-6">
            @forelse($this->communicationsByMonth as $monthYear => $monthCommunications)
            {{-- Month Header --}}
            <div class="space-y-4">
                <div class="sticky top-0 z-2 bg-gray-50/95 dark:bg-gray-900/95 backdrop-blur-md py-5 rounded-xl">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $monthYear }}
                    </h3>
                </div>

                {{-- Communications for this month --}}
                <div class="space-y-4">
                    @foreach($monthCommunications as $date => $items)
                    @foreach($items as $communication)
                    @php
                    $isPast = $communication->communication_date->isPast() &&
                    !$communication->communication_date->isToday();
                    $isToday = $communication->communication_date->isToday();

                    // Determine card background and border based ONLY on date
                    $cardClasses = match(true) {
                    $isPast => 'bg-gray-100/50 dark:bg-gray-800/50 border-gray-300 dark:border-gray-700',
                    $isToday => 'bg-orange-50/50 dark:bg-orange-900/10 border-orange-200 dark:border-orange-800/30',
                    default => 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700'
                    };

                    $hoverClasses = match(true) {
                    $isPast => 'hover:bg-gray-200/70 dark:hover:bg-gray-700/70',
                    $isToday => 'hover:bg-orange-100/70 dark:hover:bg-orange-900/20',
                    default => 'hover:bg-gray-50 dark:hover:bg-gray-700/50'
                    };

                    $textOpacity = $isPast ? 'opacity-60' : 'opacity-100';
                    @endphp
                    <div
                        class="{{ $cardClasses }} {{ $hoverClasses }} {{ $textOpacity }} rounded-xl border hover:shadow-lg transition-all duration-200">
                        <div class="p-4">
                            <div class="flex items-center gap-4">
                                {{-- Date Badge --}}
                                <div class="flex-shrink-0 text-center w-12">
                                    <div class="text-xs font-medium {{ 
                                                    $isToday ? 'text-orange-600 dark:text-orange-400' : 
                                                    ($isPast ? 'text-gray-400 dark:text-gray-500' : 'text-gray-500 dark:text-gray-400') 
                                                }}">
                                        @php
                                        $dayMap = [
                                        'Mon' => 'Sen',
                                        'Tue' => 'Sel',
                                        'Wed' => 'Rab',
                                        'Thu' => 'Kam',
                                        'Fri' => 'Jum',
                                        'Sat' => 'Sab',
                                        'Sun' => 'Min'
                                        ];
                                        $englishDay = $communication->communication_date->format('D');
                                        @endphp
                                        {{ $dayMap[$englishDay] ?? $englishDay }}
                                    </div>
                                    <div class="text-3xl font-bold {{ 
                                                    $isToday ? 'text-orange-600 dark:text-orange-400' : 
                                                    ($isPast ? 'text-gray-400 dark:text-gray-500' : 'text-gray-900 dark:text-white') 
                                                }}">
                                        {{ $communication->communication_date->format('d') }}
                                    </div>
                                </div>

                                {{-- Vertical Divider --}}
                                <div class="h-12 w-px bg-gray-300 dark:bg-gray-600 flex-shrink-0"></div>

                                {{-- Column 1: Time and Location (Fixed Width) --}}
                                <div class="flex-shrink-0 flex flex-col justify-center" style="width: 160px;">
                                    {{-- Time Range --}}
                                    <div class="flex items-center gap-2 text-sm text-gray-900 dark:text-white mb-1.5">
                                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>
                                            {{ $communication->communication_time_start ?
                                            \Carbon\Carbon::parse($communication->communication_time_start)->format('H:i')
                                            : '00:00' }}
                                            -
                                            @if($communication->communication_time_end)
                                            {{
                                            \Carbon\Carbon::parse($communication->communication_time_end)->format('H:i')
                                            }}
                                            @else
                                            {{ $communication->communication_time_start ?
                                            \Carbon\Carbon::parse($communication->communication_time_start)->addMinutes(30)->format('H:i')
                                            : '00:30' }}
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Location --}}
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="truncate">{{ $communication->location ?? 'Online' }}</span>
                                    </div>
                                </div>

                                {{-- Column 2: Title, Status Badge and Client (Fixed Width) --}}
                                <div class="flex-shrink-0 flex flex-col justify-center" style="width: 260px;">
                                    {{-- Title with Status Badge --}}
                                    <div class="flex items-start gap-2 mb-1.5">
                                        <div
                                            class="text-base font-bold text-gray-900 dark:text-white line-clamp-2 flex-1">
                                            {{ $communication->title }}
                                        </div>

                                        {{-- Status Badge (Compact) --}}
                                        <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md flex-shrink-0 {{ 
                                                        match($communication->status) {
                                                            'scheduled' => 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800',
                                                            'completed' => 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800',
                                                            'cancelled' => 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800',
                                                            'rescheduled' => 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800',
                                                            default => 'bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800'
                                                        }
                                                    }}">
                                            {{-- Status Icon (Small) --}}
                                            @if($communication->status === 'scheduled')
                                            <svg class="w-2.5 h-2.5 text-blue-600 dark:text-blue-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            @elseif($communication->status === 'completed')
                                            <svg class="w-2.5 h-2.5 text-green-600 dark:text-green-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            @elseif($communication->status === 'cancelled')
                                            <svg class="w-2.5 h-2.5 text-red-600 dark:text-red-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            @elseif($communication->status === 'rescheduled')
                                            <svg class="w-2.5 h-2.5 text-yellow-600 dark:text-yellow-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            @endif

                                            {{-- Status Label (Tiny) --}}
                                            <span class="text-[10px] font-medium leading-none {{ 
                                                            match($communication->status) {
                                                                'scheduled' => 'text-blue-700 dark:text-blue-300',
                                                                'completed' => 'text-green-700 dark:text-green-300',
                                                                'cancelled' => 'text-red-700 dark:text-red-300',
                                                                'rescheduled' => 'text-yellow-700 dark:text-yellow-300',
                                                                default => 'text-gray-700 dark:text-gray-300'
                                                            }
                                                        }}">
                                                {{
                                                match($communication->status) {
                                                'scheduled' => 'Terjadwal',
                                                'completed' => 'Selesai',
                                                'cancelled' => 'Dibatalkan',
                                                'rescheduled' => 'Dijadwalkan Ulang',
                                                default => $communication->status
                                                }
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Client Name --}}
                                    @if($communication->client)
                                    <div class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="truncate">{{ $communication->client->name }}</span>
                                    </div>
                                    @endif
                                </div>

                                {{-- Column 3: Description with Label (Flexible Width - Takes Remaining Space) --}}
                                <div class="flex-1 min-w-0 flex flex-col justify-center">
                                    {{-- Description Label --}}
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Description
                                    </div>

                                    {{-- Description Content --}}
                                    @if($communication->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $communication->description }}
                                    </p>
                                    @else
                                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">
                                        No description
                                    </p>
                                    @endif
                                </div>

                                {{-- Action Buttons - Enhanced Design --}}
                                <div class="flex-shrink-0 flex items-center gap-2 ml-10">
                                    {{-- View Button --}}
                                    <button wire:click="openViewModal({{ $communication->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-900/30 transition-all duration-200"
                                        title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Lihat</span>
                                    </button>

                                    {{-- Mark as Completed Button (only for scheduled/rescheduled) --}}
                                    @if(in_array($communication->status, ['scheduled', 'rescheduled']))
                                    <button wire:click="openCompleteModal({{ $communication->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800 dark:hover:bg-green-900/30 transition-all duration-200"
                                        title="Tandai Selesai">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Selesai</span>
                                    </button>
                                    @endif

                                    {{-- Edit Button --}}
                                    <a href="{{ route('filament.admin.pages.client-communication.{record}.edit', ['record' => $communication->id]) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700 transition-all duration-200"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Edit</span>
                                    </a>
                                </div>

                                {{-- Participants Avatars --}}
                                <div class="flex -space-x-2 flex-shrink-0 items-center">
                                    @php
                                    $participants = $communication->internalParticipantUsers ?? collect([]);
                                    $displayCount = min(2, $participants->count());
                                    $remaining = max(0, $participants->count() - 2);
                                    @endphp

                                    @foreach($participants->take(2) as $participant)
                                    <div
                                        class="relative inline-flex items-center justify-center w-8 h-8 overflow-hidden bg-gradient-to-br from-purple-500 to-pink-500 rounded-full border-2 border-white dark:border-gray-800">
                                        @if($participant->avatar_url)
                                        <img src="{{ $participant->avatar_url }}" alt="{{ $participant->name }}"
                                            class="w-full h-full object-cover">
                                        @else
                                        <span class="text-xs font-medium text-white">
                                            {{ strtoupper(substr($participant->name, 0, 2)) }}
                                        </span>
                                        @endif
                                    </div>
                                    @endforeach

                                    @if($remaining > 0)
                                    <div
                                        class="relative inline-flex items-center justify-center w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full border-2 border-white dark:border-gray-800">
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                            +{{ $remaining }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endforeach
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No communications found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Get started by creating a new communication event.
                </p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Mark as Completed Modal --}}
    <x-filament::modal id="complete-communication-modal" width="4xl">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Tandai Komunikasi Selesai</span>
            </div>
        </x-slot>

        @if($selectedCommunicationId)
        <form wire:submit="markAsCompleted">
            {{ $this->completeForm }}

            <div class="mt-6 flex justify-end gap-3">
                <x-filament::button color="gray" wire:click="closeCompleteModal" type="button">
                    Batal
                </x-filament::button>

                <x-filament::button type="submit" color="success">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Tandai Selesai
                </x-filament::button>
            </div>
        </form>
        @endif
    </x-filament::modal>

    {{-- View Detail Modal --}}
    <x-filament::modal id="view-communication-modal" width="5xl">
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $viewCommunication->title ??
                            'Detail Komunikasi' }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Informasi lengkap komunikasi dengan klien
                        </p>
                    </div>
                </div>
            </div>
        </x-slot>

        @if($viewCommunication)
        <div class="space-y-4">
            {{-- General Information Section --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Umum</h4>
                </div>
                <div class="px-4 py-3 space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium rounded-md {{ 
                                match($viewCommunication->status) {
                                    'scheduled' => 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300',
                                    'completed' => 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300',
                                    'cancelled' => 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300',
                                    'rescheduled' => 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300',
                                    default => 'bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300'
                                }
                            }}">
                            {{ $viewCommunication->status_label }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Klien:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                            $viewCommunication->client->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Penanggung Jawab:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                            $viewCommunication->user->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Jenis Komunikasi:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                            $viewCommunication->type_label }}</span>
                    </div>
                    @if($viewCommunication->project)
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Proyek Terkait:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                            $viewCommunication->project->name }}</span>
                    </div>
                    @else
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Proyek Terkait:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">-</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Schedule Section --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Jadwal & Lokasi</h4>
                </div>
                <div class="px-4 py-3 space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Tanggal:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                            $viewCommunication->communication_date->format('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Waktu Mulai:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                            $viewCommunication->communication_time_start ?
                            \Carbon\Carbon::parse($viewCommunication->communication_time_start)->format('H:i') : '-'
                            }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Waktu Selesai:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                            $viewCommunication->communication_time_end ?
                            \Carbon\Carbon::parse($viewCommunication->communication_time_end)->format('H:i') : '-'
                            }}</span>
                    </div>
                    <div
                        class="flex justify-between py-2 {{ $viewCommunication->meeting_platform ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Lokasi:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $viewCommunication->location
                            ?? '-' }}</span>
                    </div>
                    @if($viewCommunication->meeting_platform)
                    <div
                        class="flex justify-between py-2 {{ $viewCommunication->meeting_link ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Platform:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                            $viewCommunication->meeting_platform }}</span>
                    </div>
                    @endif
                    @if($viewCommunication->meeting_link)
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Link Meeting:</span>
                        <a href="{{ $viewCommunication->meeting_link }}" target="_blank"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 flex items-center gap-1">
                            <span>Buka Link</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Details Section --}}
            @if($viewCommunication->description || $viewCommunication->outcome || $viewCommunication->notes)
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Detail & Catatan</h4>
                </div>
                <div class="px-4 py-3 space-y-4">
                    @if($viewCommunication->description)
                    <div>
                        <label
                            class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Deskripsi</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $viewCommunication->description }}</p>
                    </div>
                    @endif

                    @if($viewCommunication->outcome)
                    <div>
                        <label
                            class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Hasil/Kesimpulan</label>
                        <div class="mt-1 prose prose-sm dark:prose-invert max-w-none text-sm">
                            {!! $viewCommunication->outcome !!}
                        </div>
                    </div>
                    @endif

                    @if($viewCommunication->notes)
                    <div>
                        <label
                            class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Catatan</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $viewCommunication->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Attachments Section --}}
            @if($viewCommunication->attachments && count($viewCommunication->attachments) > 0)
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Lampiran ({{
                        count($viewCommunication->attachments) }})</h4>
                </div>
                <div class="px-4 py-3 space-y-2">
                    @foreach($viewCommunication->attachments as $attachment)
                    <a href="{{ Storage::url($attachment) }}" target="_blank"
                        class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors group">
                        <div
                            class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </div>
                        <span
                            class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">{{
                            basename($attachment) }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="mt-6 flex justify-end">
            <x-filament::button color="gray" wire:click="closeViewModal">
                Tutup
            </x-filament::button>
        </div>
        @endif
    </x-filament::modal>
</x-filament-panels::page>