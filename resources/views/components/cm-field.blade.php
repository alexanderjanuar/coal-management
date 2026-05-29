@props([
    'label',
    'value' => null,
    'mono'  => false,
])

<div {{ $attributes->class(['py-3 sm:py-2']) }}>
    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        {{ $label }}
    </dt>
    <dd @class([
        'mt-1 text-sm',
        'font-mono'                          => $mono,
        'text-gray-900 dark:text-gray-100'   => filled($value),
        'italic text-gray-400 dark:text-gray-600' => ! filled($value),
    ])>
        {{ filled($value) ? $value : 'Belum diisi' }}
    </dd>
</div>
