@props([
    'icon'     => 'document',
    'label',
    'value'    => null,
    'href'     => null,
    'mono'     => false,
    'ext'      => false,   // buka di tab baru
    'copyable' => false,
    'optional' => false,
])

<div {{ $attributes->class(['flex items-start gap-2.5 py-2.5']) }}>
    <span class="mt-px shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true">
        <x-client-management.field-icon :name="$icon" class="h-4 w-4" />
    </span>

    <div class="min-w-0 flex-1">
        <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-400 dark:text-gray-500">
            {{ $label }}
            @if ($optional)
                <span class="rounded bg-gray-100 px-1.5 py-px text-[10px] font-medium normal-case text-gray-500 dark:bg-gray-800 dark:text-gray-400">Opsional</span>
            @endif
        </dt>

        <dd class="mt-0.5 flex items-center gap-2">
            @if (filled($value))
                @if ($href)
                    <a href="{{ $href }}" @if ($ext) target="_blank" rel="noopener" @endif
                       @class([
                           'truncate text-sm font-medium text-indigo-600 underline-offset-2 transition hover:underline dark:text-indigo-400',
                           'font-mono' => $mono,
                       ])>{{ $value }}</a>
                @else
                    <span @class([
                        'truncate text-sm text-gray-900 dark:text-gray-100',
                        'font-mono' => $mono,
                    ])>{{ $value }}</span>
                @endif

                @if ($copyable)
                    <button type="button" x-data="{ copied: false }"
                        @click="navigator.clipboard.writeText(@js($value)); copied = true; setTimeout(() => copied = false, 1500)"
                        class="shrink-0 rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        aria-label="Salin {{ $label }}">
                        <template x-if="!copied">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                            </svg>
                        </template>
                        <template x-if="copied">
                            <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </template>
                    </button>
                @endif
            @else
                <span @class([
                    'text-sm',
                    'text-gray-300 dark:text-gray-600'        => $optional,
                    'italic text-gray-400 dark:text-gray-600' => ! $optional,
                ])>{{ $optional ? '—' : 'Belum diisi' }}</span>
            @endif
        </dd>
    </div>
</div>
