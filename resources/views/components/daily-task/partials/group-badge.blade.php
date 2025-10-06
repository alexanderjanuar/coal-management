@switch($groupBy)
@case('status')
<div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm border
                    {{ match($groupName) {
                        'Completed' => 'bg-green-50 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-300 dark:border-green-700',
                        'In Progress' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/40 dark:text-yellow-300 dark:border-yellow-700',
                        'Pending' => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                        'Cancelled' => 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700',
                        default => 'bg-gray-50 text-gray-800 border-gray-200'
                    } }}">
    <div class="w-2 h-2 rounded-full bg-current"></div>
    {{ $groupName }}
</div>
@break

@case('priority')
<div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm border
                    {{ match($groupName) {
                        'Urgent' => 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700',
                        'High' => 'bg-orange-50 text-orange-800 border-orange-200 dark:bg-orange-900/40 dark:text-orange-300 dark:border-orange-700',
                        'Normal' => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700',
                        'Low' => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                        default => 'bg-blue-50 text-blue-800 border-blue-200'
                    } }}">
    @php
    $priorityIcon = match($groupName) {
    'Urgent' => 'heroicon-s-exclamation-triangle',
    'High' => 'heroicon-o-exclamation-triangle',
    'Normal' => 'heroicon-o-minus',
    'Low' => 'heroicon-o-arrow-down',
    default => 'heroicon-o-minus'
    };
    @endphp
    <x-dynamic-component :component="$priorityIcon" class="w-3.5 h-3.5" />
    {{ $groupName }}
</div>
@break

@case('project')
<div
    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm border
                    bg-indigo-50 text-indigo-800 border-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:border-indigo-700">
    <x-heroicon-o-folder class="w-3.5 h-3.5" />
    {{ $groupName }}
</div>
@break

@case('assignee')
<div
    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm border
                    bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700">
    <x-heroicon-o-user class="w-3.5 h-3.5" />
    {{ $groupName }}
</div>
@break

@case('date')
<div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm border
                    {{ match($groupName) {
                        'Terlambat' => 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700',
                        'Mendatang' => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700',
                        'Tanpa Deadline' => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                        'Selesai' => 'bg-green-50 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-300 dark:border-green-700',
                        default => 'bg-purple-50 text-purple-800 border-purple-200'
                    } }}">
    @php
    $dateIcon = match($groupName) {
    'Terlambat' => 'heroicon-o-exclamation-circle',
    'Mendatang' => 'heroicon-o-calendar',
    'Tanpa Deadline' => 'heroicon-o-minus-circle',
    'Selesai' => 'heroicon-o-check-circle',
    default => 'heroicon-o-calendar-days'
    };
    @endphp
    <x-dynamic-component :component="$dateIcon" class="w-3.5 h-3.5" />
    {{ $groupName }}
</div>
@break

@default
<h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $groupName }}</h3>
@endswitch