@props([
    'label',
    'wire' => null,
    'value' => null,
    'type' => 'text',
    'mode' => 'view',
    'placeholder' => '',
    'error' => null,
])

@php
    $isPassword = $type === 'password';
    $isEdit = $mode === 'edit';
@endphp

<div class="space-y-1.5" x-data="{ show: false, copied: false, val: @js((string) $value) }">
    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $label }}</label>

    @if ($isEdit)
        {{-- ============ EDIT ============ --}}
        <div class="relative flex items-center">
            <input
                @if ($isPassword) :type="show ? 'text' : 'password'" @else type="{{ $type }}" @endif
                wire:model="{{ $wire }}"
                placeholder="{{ $placeholder }}"
                autocomplete="{{ $isPassword ? 'new-password' : 'off' }}"
                data-1p-ignore data-lpignore="true" data-form-type="other"
                @class([
                    'w-full rounded-lg border bg-white px-3 py-2 text-sm font-mono text-gray-900 shadow-sm transition focus:ring-1 dark:bg-gray-900 dark:text-white',
                    'pr-9' => $isPassword,
                    'border-gray-300 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600' => ! $error,
                    'border-danger-400 focus:border-danger-500 focus:ring-danger-500 dark:border-danger-500/60' => $error,
                ])
            />
            @if ($isPassword)
                <button type="button" x-on:click="show = !show" tabindex="-1"
                        class="absolute right-2.5 text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200">
                    <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            @endif
        </div>
        @if ($error)
            <p class="text-xs text-danger-600 dark:text-danger-400">{{ $error }}</p>
        @endif
    @else
        {{-- ============ VIEW ============ --}}
        @if (filled($value))
            <div class="group flex items-center gap-1.5 rounded-lg bg-gray-50 px-3 py-2 ring-1 ring-inset ring-gray-200/70 transition hover:ring-gray-300 dark:bg-gray-800/50 dark:ring-gray-700/60 dark:hover:ring-gray-600">
                @if ($isPassword)
                    <span class="flex-1 truncate font-mono text-sm tracking-wide text-gray-900 dark:text-gray-100"
                          x-text="show ? val : '•••••••••••'"></span>
                @else
                    <span class="flex-1 truncate font-mono text-sm text-gray-900 dark:text-gray-100">{{ $value }}</span>
                @endif

                @if ($isPassword)
                    <button type="button" x-on:click="show = !show"
                            class="shrink-0 rounded-md p-1 text-gray-400 transition hover:bg-white hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            x-bind:title="show ? 'Sembunyikan' : 'Tampilkan'">
                        <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                @endif

                <button type="button"
                        x-on:click="navigator.clipboard.writeText(val); copied = true; setTimeout(() => copied = false, 1500)"
                        class="shrink-0 rounded-md p-1 text-gray-400 transition hover:bg-white hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                        x-bind:title="copied ? 'Tersalin!' : 'Salin'">
                    <svg x-show="!copied" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <svg x-show="copied" x-cloak class="h-4 w-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
            </div>
        @else
            <div class="flex items-center gap-1.5 rounded-lg border border-dashed border-gray-200 px-3 py-2 dark:border-gray-700">
                <span class="text-sm italic text-gray-400 dark:text-gray-600">Belum diisi</span>
            </div>
        @endif
    @endif
</div>
