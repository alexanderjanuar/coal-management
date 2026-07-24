{{--
    Baris list Tugas Harian — HTML BIASA, bukan komponen Livewire.

    Sebelumnya tiap baris adalah <livewire:daily-task-item> (komponen Livewire +
    Filament-form penuh). Banyak komponen bersarang seperti itu merusak akun
    penanda blok morph Alpine di induk -> setiap re-render (switch view, filter,
    ubah status) hanya menyisakan grup PERTAMA ("hanya Pending yang tampil").

    Sebagai baris statis, morph induk stabil. Semua editing tetap tersedia lewat
    modal detail (klik baris) yang punya updateStatus/updatePriority/assignUser/
    subtask/tenggat/dll. Toggle "selesai" dipertahankan sebagai aksi cepat inline.

    Grid 12 kolom mengikuti header tabel: 1 (toggle) + 4 (judul) + 2 (status) +
    1 (prioritas) + 2 (assignee) + 1 (proyek) + 1 (tenggat).
--}}
@php
    $isOverdue = $task->task_date && $task->task_date->isPast() && $task->status !== 'completed';
@endphp
<div class="grid grid-cols-12 gap-2 xl:gap-4 items-center min-h-[52px] px-4 py-2.5
            border-l-2 border-l-transparent hover:border-l-cyan-500 dark:hover:border-l-cyan-400
            hover:bg-cyan-50/40 dark:hover:bg-cyan-900/10 transition-all duration-150 group">

    {{-- 1. Toggle selesai — tetap fungsional (metode induk) --}}
    <div class="col-span-1 flex items-center justify-center">
        <button type="button" wire:click="toggleTaskComplete({{ $task->id }})"
                class="flex-shrink-0 w-5 h-5 flex items-center justify-center hover:scale-110 transition-transform duration-200 group/btn"
                title="Tandai selesai / belum selesai">
            @if($task->status === 'completed')
                <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-500 dark:text-emerald-400" />
            @else
                <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600
                            group-hover/btn:border-cyan-500 dark:group-hover/btn:border-cyan-400
                            transition-all duration-150"></div>
            @endif
        </button>
    </div>

    {{-- 2. Judul + deskripsi (klik -> modal) --}}
    <div class="col-span-4 min-w-0 cursor-pointer" wire:click="openTaskDetail({{ $task->id }})">
        <p class="text-[.855rem] font-semibold leading-snug truncate transition-colors duration-150
                  text-gray-800 dark:text-gray-100 group-hover:text-cyan-700 dark:group-hover:text-cyan-300
                  {{ $task->status === 'completed' ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
            {{ Str::limit(strip_tags($task->title), 60) }}
        </p>
        @if($task->description)
            <p class="text-[.75rem] text-gray-400 dark:text-gray-500 truncate mt-0.5">
                {{ Str::limit(strip_tags($task->description), 55) }}
            </p>
        @endif
    </div>

    {{-- 3. Status (klik -> modal) --}}
    <div class="col-span-2 min-w-0 cursor-pointer" wire:click="openTaskDetail({{ $task->id }})">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 h-7 rounded-md text-[.74rem] font-medium w-full justify-center border
                     {{ match($task->status) {
                         'completed'   => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                         'in_progress' => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800',
                         'cancelled'   => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800',
                         default       => 'bg-gray-100 dark:bg-white/[.06] text-gray-600 dark:text-gray-400 border-black/[.08] dark:border-white/[.08]',
                     } }}">
            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ match($task->status) {
                'completed' => 'bg-emerald-500', 'in_progress' => 'bg-yellow-500 animate-pulse', 'cancelled' => 'bg-red-500', default => 'bg-gray-400',
            } }}"></span>
            <span class="truncate">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
        </span>
    </div>

    {{-- 4. Prioritas --}}
    <div class="col-span-1 min-w-0 cursor-pointer" wire:click="openTaskDetail({{ $task->id }})">
        @php $picon = match($task->priority) { 'urgent' => 'heroicon-s-exclamation-triangle', 'high' => 'heroicon-o-exclamation-triangle', 'low' => 'heroicon-o-arrow-down', default => 'heroicon-o-minus' }; @endphp
        <span class="inline-flex items-center gap-1 px-2 py-1.5 h-7 rounded-md text-[.74rem] font-bold w-full justify-center border
                     {{ match($task->priority) {
                        'urgent' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800',
                        'high'   => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
                        'normal' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                        default  => 'bg-gray-100 dark:bg-white/[.06] text-gray-500 dark:text-gray-400 border-black/[.08] dark:border-white/[.07]',
                     } }}">
            <x-dynamic-component :component="$picon" class="w-3 h-3 flex-shrink-0" />
            <span class="hidden 2xl:inline truncate">{{ ucfirst($task->priority) }}</span>
        </span>
    </div>

    {{-- 5. Assignee --}}
    <div class="col-span-2 min-w-0 cursor-pointer" wire:click="openTaskDetail({{ $task->id }})">
        <div class="flex items-center gap-2">
            @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                <div class="flex -space-x-1 flex-shrink-0">
                    @foreach($task->assignedUsers->take(2) as $user)
                        <div class="w-5 h-5 rounded-full bg-cyan-600 dark:bg-cyan-500 text-white flex items-center justify-center text-[.62rem] font-bold border border-white dark:border-gray-900" title="{{ $user->name }}">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endforeach
                    @if($task->assignedUsers->count() > 2)
                        <div class="w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 flex items-center justify-center text-[.62rem] font-bold border border-white dark:border-gray-900">
                            +{{ $task->assignedUsers->count() - 2 }}
                        </div>
                    @endif
                </div>
                <span class="hidden xl:inline text-[.75rem] text-gray-600 dark:text-gray-300 truncate">
                    {{ $task->assignedUsers->count() === 1 ? Str::limit($task->assignedUsers->first()->name, 12) : $task->assignedUsers->count() . ' orang' }}
                </span>
            @else
                <div class="w-5 h-5 rounded-full bg-gray-100 dark:bg-white/[.06] border border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-plus class="w-2.5 h-2.5 text-gray-400" />
                </div>
                <span class="hidden lg:inline text-[.75rem] text-gray-400 dark:text-gray-500 truncate">Unassigned</span>
            @endif
        </div>
    </div>

    {{-- 6. Proyek --}}
    <div class="col-span-1 min-w-0 cursor-pointer" wire:click="openTaskDetail({{ $task->id }})">
        <span class="flex items-center gap-1 px-2 py-1.5 h-7 rounded-md text-[.74rem] font-medium w-full justify-center border
                     {{ $task->project
                         ? 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 border-violet-200 dark:border-violet-800'
                         : 'bg-gray-100 dark:bg-white/[.05] border-dashed border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500' }}">
            @if($task->project)
                <x-heroicon-o-folder class="w-3 h-3 flex-shrink-0" />
                <span class="hidden 2xl:inline truncate" title="{{ $task->project->name }}">{{ Str::limit($task->project->name, 8) }}</span>
            @else
                <x-heroicon-o-folder-plus class="w-3 h-3 flex-shrink-0" />
                <span class="hidden 2xl:inline">—</span>
            @endif
        </span>
    </div>

    {{-- 7. Tenggat --}}
    <div class="col-span-1 min-w-0 cursor-pointer" wire:click="openTaskDetail({{ $task->id }})">
        @if($task->task_date)
            <span class="inline-flex items-center gap-1 text-[.75rem] font-medium whitespace-nowrap
                         {{ $isOverdue ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                <x-heroicon-o-calendar class="w-3.5 h-3.5 flex-shrink-0" />
                {{ $task->task_date->translatedFormat('d M') }}
            </span>
        @else
            <span class="text-[.75rem] text-gray-300 dark:text-gray-600">—</span>
        @endif
    </div>
</div>
