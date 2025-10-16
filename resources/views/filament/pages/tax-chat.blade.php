<x-filament-panels::page>
    {{-- Use relative positioning instead of fixed to respect Filament's layout --}}
    <div class="flex flex-col h-[calc(100vh-12rem)] relative">

        {{-- Chat Header --}}
        <div class="px-6 py-4 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-teal-500 rounded-full flex items-center justify-center">
                        <x-heroicon-o-sparkles class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">TaxBot</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Asisten Perpajakan AI</p>
                    </div>
                </div>

                <x-filament::button color="gray" size="sm" outlined wire:click="clearChat" icon="heroicon-o-trash">
                    Clear
                </x-filament::button>
            </div>
        </div>

        {{-- Chat Messages Area with extra padding bottom for floating input --}}
        <div class="flex-1 overflow-y-auto px-6 py-6 pb-40 space-y-4 bg-gray-50 dark:bg-gray-950" id="chat-messages">
            <div class="w-full">
                @forelse($chatHistory as $message)
                <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }} mb-4">
                    <div
                        class="flex items-start space-x-2 max-w-[75%] {{ $message['role'] === 'user' ? 'flex-row-reverse space-x-reverse' : '' }}">

                        {{-- Avatar --}}
                        <div class="flex-shrink-0">
                            @if($message['role'] === 'assistant')
                            <div class="w-8 h-8 bg-teal-500 rounded-full flex items-center justify-center">
                                <x-heroicon-o-sparkles class="w-4 h-4 text-white" />
                            </div>
                            @else
                            <div
                                class="w-8 h-8 bg-gray-400 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                <x-heroicon-o-user class="w-4 h-4 text-white" />
                            </div>
                            @endif
                        </div>

                        {{-- Message Content --}}
                        <div>
                            <div
                                class="rounded-2xl px-4 py-2.5 {{ $message['role'] === 'user' 
                                    ? 'text-white bg-teal-500 ' 
                                    : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm border border-gray-200 dark:border-gray-700' }}">
                                <div
                                    class="prose prose-sm max-w-none {{ $message['role'] === 'user' ? 'prose-invert' : 'dark:prose-invert' }}">
                                    {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 px-2">{{ $message['timestamp'] }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex items-center justify-center h-full text-center">
                    <div>
                        <div
                            class="w-16 h-16 bg-teal-100 dark:bg-teal-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-teal-500" />
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">Belum ada percakapan</p>
                    </div>
                </div>
                @endforelse

                {{-- Loading Indicator --}}
                <div wire:loading wire:target="sendMessage" class="flex justify-start mb-4">
                    <div class="flex items-start space-x-2 max-w-[75%]">
                        <div class="w-8 h-8 bg-teal-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <x-heroicon-o-sparkles class="w-4 h-4 text-white" />
                        </div>
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl px-4 py-3 shadow-sm border border-gray-200 dark:border-gray-700">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-teal-500 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-teal-500 rounded-full animate-bounce"
                                    style="animation-delay: 0.15s"></div>
                                <div class="w-2 h-2 bg-teal-500 rounded-full animate-bounce"
                                    style="animation-delay: 0.3s"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Floating Input Section - Absolute positioned at bottom --}}
        <div class="absolute bottom-0 left-0 right-0 px-6 pb-6 pointer-events-none">
            <div class="max-w-5xl mx-auto pointer-events-auto">
                {{-- Quick Actions Pills --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    <button wire:click="$set('message', 'Tampilkan daftar semua klien')"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm hover:bg-white dark:hover:bg-gray-800 rounded-full transition-all shadow-sm border border-gray-200 dark:border-gray-700">
                        <x-heroicon-o-users class="w-3.5 h-3.5 mr-1.5" />
                        Lihat Klien
                    </button>
                    <button wire:click="$set('message', 'Status pajak bulan ini')"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm hover:bg-white dark:hover:bg-gray-800 rounded-full transition-all shadow-sm border border-gray-200 dark:border-gray-700">
                        <x-heroicon-o-chart-bar class="w-3.5 h-3.5 mr-1.5" />
                        Status Pajak
                    </button>
                    <button wire:click="$set('message', 'Apa yang bisa Anda lakukan?')"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm hover:bg-white dark:hover:bg-gray-800 rounded-full transition-all shadow-sm border border-gray-200 dark:border-gray-700">
                        <x-heroicon-o-question-mark-circle class="w-3.5 h-3.5 mr-1.5" />
                        Bantuan
                    </button>
                </div>

                {{-- Floating Input Pill with backdrop blur --}}
                <div
                    class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 p-2">
                    <form wire:submit="sendMessage" class="flex items-center space-x-2">
                        <div class="flex-1 relative">
                            <x-filament::input type="text" wire:model.live="message" placeholder="Ketik pesan Anda..."
                                class="!border-0 !rounded-full !bg-transparent !shadow-none !ring-0 focus:!ring-0 !pl-5 !pr-16 !py-3"
                                wire:keydown.enter="sendMessage" />
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-400">
                                {{ is_string($message) ? strlen($message) : 0 }}/1000
                            </span>
                        </div>

                        <x-filament::button type="submit" color="primary" :disabled="$this->isMessageEmpty"
                            class="!rounded-full !h-12 !w-12 !p-0 !bg-teal-500 hover:!bg-teal-600 disabled:!opacity-50 disabled:!cursor-not-allowed !shadow-lg hover:!shadow-xl !transition-all"
                            wire:loading.attr="disabled" wire:target="sendMessage">
                            <span wire:loading.remove wire:target="sendMessage">
                                <x-heroicon-o-paper-airplane class="w-5 h-5" />
                            </span>
                        </x-filament::button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Auto-scroll to bottom when new message added
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', () => {
                scrollToBottom();
            });
        });

        function scrollToBottom() {
            const chatMessages = document.getElementById('chat-messages');
            if (chatMessages) {
                setTimeout(() => {
                    chatMessages.scrollTo({
                        top: chatMessages.scrollHeight,
                        behavior: 'smooth'
                    });
                }, 100);
            }
        }

        // Initial scroll to bottom
        document.addEventListener('DOMContentLoaded', scrollToBottom);

        // Enter key handler
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                const messageInput = document.querySelector('input[wire\\:model\\.live="message"]');
                if (messageInput === document.activeElement && messageInput.value.trim()) {
                    e.preventDefault();
                }
            }
        });
    </script>

    <style>
        /* Custom scrollbar */
        #chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        #chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        #chat-messages::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        #chat-messages::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        .dark #chat-messages::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        .dark #chat-messages::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        /* Prose adjustments */
        .prose p:last-child {
            margin-bottom: 0;
        }

        .prose ul,
        .prose ol {
            margin-top: 0.5em;
            margin-bottom: 0.5em;
        }

        /* Smooth transitions */
        .prose * {
            transition: none;
        }
    </style>
</x-filament-panels::page>