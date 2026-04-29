@auth
<div
    x-data="{
        open: false,
        toggleChat() {
            this.open = !this.open;
        }
    }"
    class="fixed bottom-6 right-6 z-[60] flex flex-col items-end gap-3 print:hidden"
>
    {{-- ── Chat popup ───────────────────────────────────────── --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-95"
        style="display:none; transform-origin: bottom right;"
        class="flex w-[380px] flex-col overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-2xl shadow-gray-900/15 dark:border-gray-700/80 dark:bg-gray-950"
        :style="{ height: '580px' }"
    >
        @livewire('client.panel.chat-panel', ['compact' => true])
    </div>

    {{-- ── FAB button ───────────────────────────────────────── --}}
    <button
        @click="toggleChat()"
        :class="open ? 'scale-110' : 'hover:scale-110'"
        aria-label="Chat"
        class="group relative flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-cyan-700 text-white shadow-lg shadow-cyan-600/30 transition-all duration-300 hover:shadow-xl hover:shadow-cyan-600/40 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2"
    >
        {{-- Chat icon (shown when closed) --}}
        <x-heroicon-o-chat-bubble-left-right
            x-show="!open"
            class="h-6 w-6 transition-all duration-200"
        />

        {{-- X icon (shown when open) --}}
        <x-heroicon-o-x-mark
            x-show="open"
            class="h-6 w-6 transition-all duration-200"
            style="display:none;"
        />

        {{-- Pulse ring (always visible, subtle) --}}
        <span
            x-show="!open"
            class="absolute inset-0 -z-10 animate-ping rounded-full bg-cyan-500 opacity-20"
            style="animation-duration: 2.5s;"
        ></span>
    </button>
</div>
@endauth
