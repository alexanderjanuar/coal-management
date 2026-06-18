@props(['status' => null])

@if($status === 'Active')
    <span {{ $attributes->class(['inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/25']) }}>Aktif</span>
@elseif($status === 'Inactive')
    <span {{ $attributes->class(['inline-flex shrink-0 items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400']) }}>Nonaktif</span>
@endif
