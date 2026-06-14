@props([
    'icon'     => 'document',
    'title',
    'subtitle' => null,
    'color'    => 'indigo',
    'id'       => null,
    'filled'   => null,
    'total'    => null,
])

@php
    // Class literal (bukan dinamis) supaya aman dari purge Tailwind.
    $badge = [
        'indigo'  => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'sky'     => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'amber'   => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    ][$color] ?? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400';

    $hasMeter = ! is_null($total) && $total > 0;
    $done     = $hasMeter && $filled >= $total;
@endphp

<section @if ($id) id="{{ $id }}" @endif
    {{ $attributes->class([
        'group scroll-mt-24 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200',
        'hover:border-gray-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700',
    ]) }}>

    <header class="mb-5 flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $badge }} transition group-hover:scale-105">
                <x-client-management.field-icon :name="$icon" style="color: currentColor; width: 18px; height: 18px;" />
            </span>
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if ($hasMeter)
            <span @class([
                'inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold tabular-nums',
                'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30' => $done,
                'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30' => ! $done,
            ])>
                <span @class([
                    'h-1.5 w-1.5 rounded-full',
                    'bg-emerald-500' => $done,
                    'bg-amber-500'   => ! $done,
                ])></span>
                {{ $filled }}/{{ $total }}
            </span>
        @endif
    </header>

    {{ $slot }}
</section>
