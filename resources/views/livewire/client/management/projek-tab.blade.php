<div>
    @php
        $projectCollection = collect($projects);
    @endphp

    @if($projectCollection->isNotEmpty())
    <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
        @foreach($projectCollection as $project)
        @php
            $statusConfig = [
                'draft' => ['label' => 'Draft', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300', 'rail' => 'bg-gray-400', 'icon' => 'heroicon-o-pencil-square'],
                'analysis' => ['label' => 'Analysis', 'class' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300', 'rail' => 'bg-violet-500', 'icon' => 'heroicon-o-magnifying-glass-circle'],
                'in_progress' => ['label' => 'In Progress', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300', 'rail' => 'bg-blue-500', 'icon' => 'heroicon-o-arrow-path'],
                'review' => ['label' => 'Review', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300', 'rail' => 'bg-amber-500', 'icon' => 'heroicon-o-eye'],
                'completed' => ['label' => 'Completed', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300', 'rail' => 'bg-green-500', 'icon' => 'heroicon-o-check-circle'],
                'completed (Not Payed Yet)' => ['label' => 'Not Paid', 'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300', 'rail' => 'bg-yellow-500', 'icon' => 'heroicon-o-banknotes'],
                'canceled' => ['label' => 'Canceled', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', 'rail' => 'bg-red-500', 'icon' => 'heroicon-o-x-circle'],
            ];

            $typeLabels = [
                'single' => 'On Spot',
                'monthly' => 'Monthly',
                'yearly' => 'Yearly',
            ];

            $priorityConfig = [
                'urgent' => ['label' => 'Urgent', 'class' => 'bg-red-50 text-red-700 ring-red-600/10 dark:bg-red-900/20 dark:text-red-400 dark:ring-red-400/20'],
                'normal' => ['label' => 'Normal', 'class' => 'bg-gray-50 text-gray-700 ring-gray-600/10 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-400/20'],
                'low' => ['label' => 'Low', 'class' => 'bg-slate-50 text-slate-600 ring-slate-600/10 dark:bg-slate-900/40 dark:text-slate-300 dark:ring-slate-400/20'],
            ];

            $statusConf = $statusConfig[$project->status] ?? $statusConfig['draft'];
            $priorityConf = $priorityConfig[$project->priority] ?? $priorityConfig['normal'];
            $typeLabel = $typeLabels[$project->type] ?? ucfirst($project->type ?? 'On Spot');
            $dueDate = $project->due_date ? \Carbon\Carbon::parse($project->due_date) : null;
            $diff = $dueDate ? now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false) : null;

            $progress = $project->steps_count > 0
                ? (int) round(($project->completed_steps_count / max($project->steps_count, 1)) * 100)
                : match ($project->status) {
                    'completed', 'completed (Not Payed Yet)' => 100,
                    'review' => 75,
                    'in_progress' => 50,
                    'analysis' => 25,
                    default => 0,
                };

            if ($diff === null) {
                $deadlineTone = 'text-gray-600 dark:text-gray-400';
                $deadlineLabel = 'Belum ada deadline';
                $deadlineIcon = 'heroicon-o-calendar';
            } elseif ($diff < 0) {
                $deadlineTone = 'text-red-600 dark:text-red-400';
                $deadlineLabel = 'Terlambat ' . abs($diff) . ' hari';
                $deadlineIcon = 'heroicon-o-exclamation-triangle';
            } elseif ($diff === 0) {
                $deadlineTone = 'text-amber-600 dark:text-amber-400';
                $deadlineLabel = 'Hari ini';
                $deadlineIcon = 'heroicon-o-clock';
            } elseif ($diff <= 7) {
                $deadlineTone = 'text-amber-600 dark:text-amber-400';
                $deadlineLabel = $diff . ' hari lagi';
                $deadlineIcon = 'heroicon-o-clock';
            } else {
                $deadlineTone = 'text-gray-600 dark:text-gray-400';
                $deadlineLabel = $diff . ' hari lagi';
                $deadlineIcon = 'heroicon-o-calendar';
            }

            $description = trim(strip_tags($project->description ?? ''));
            $resultNotes = trim(strip_tags($project->result_notes ?? ''));
        @endphp

        <article
            onclick="window.location.href='{{ route('filament.admin.resources.projects.view', ['record' => $project->id]) }}'"
            class="group relative cursor-pointer overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5 transition duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">

            <div class="p-6">
                <div class="flex min-w-0 items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium {{ $statusConf['class'] }}">
                                <x-filament::icon :icon="$statusConf['icon']" class="h-3.5 w-3.5" />
                                {{ $statusConf['label'] }}
                            </span>
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $priorityConf['class'] }}">
                                {{ $priorityConf['label'] }} Priority
                            </span>
                        </div>

                        <h3 class="text-lg font-semibold leading-7 text-gray-950 dark:text-white">
                            {{ $project->name }}
                        </h3>

                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-rectangle-stack" class="h-4 w-4" />
                                {{ $typeLabel }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-4 w-4" />
                                {{ $project->sop?->name ?? 'Tanpa SOP' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" />
                                Dibuat {{ $project->created_at?->format('d M Y') ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-400 transition group-hover:bg-gray-100 group-hover:text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:group-hover:bg-gray-700 dark:group-hover:text-gray-300">
                        <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-5 w-5" />
                    </div>
                </div>

                <p class="mt-5 min-h-[3rem] text-sm leading-6 text-gray-600 dark:text-gray-400">
                    {{ $description !== '' ? \Illuminate\Support\Str::limit($description, 180) : 'Belum ada deskripsi proyek.' }}
                </p>

                <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Progress</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $project->completed_steps_count }} / {{ $project->steps_count }} tahap selesai
                            </p>
                        </div>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $progress }}%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full rounded-full {{ $statusConf['rail'] }}" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="flex items-center gap-2 {{ $deadlineTone }}">
                            <x-filament::icon :icon="$deadlineIcon" class="h-4 w-4" />
                            <span class="text-xs font-medium uppercase tracking-wide">Deadline</span>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $dueDate ? $dueDate->format('d M Y') : '-' }}</p>
                        <p class="text-xs {{ $deadlineTone }}">{{ $deadlineLabel }}</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-user-circle" class="h-4 w-4" />
                            <span class="text-xs font-medium uppercase tracking-wide">PIC</span>
                        </div>
                        <p class="mt-2 truncate text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $project->pic?->name ?? 'Belum ditentukan' }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-users" class="h-4 w-4" />
                            <span class="text-xs font-medium uppercase tracking-wide">Tim</span>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $project->team_members_count }} anggota
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-paper-clip" class="h-4 w-4" />
                            <span class="text-xs font-medium uppercase tracking-wide">Output</span>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $project->deliverables_count }} file
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <span class="inline-flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                            {{ $project->completed_daily_tasks_count }} / {{ $project->daily_tasks_count }} task selesai
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-o-chat-bubble-left-ellipsis" class="h-4 w-4" />
                            {{ $project->notes_count }} catatan
                        </span>
                    </div>

                    @if($resultNotes !== '')
                    <span class="inline-flex max-w-full items-center gap-1.5 truncate rounded-md bg-gray-100 px-2 py-1 font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        <x-filament::icon icon="heroicon-o-document-check" class="h-4 w-4 shrink-0" />
                        {{ \Illuminate\Support\Str::limit($resultNotes, 52) }}
                    </span>
                    @endif
                </div>
            </div>
        </article>
        @endforeach
    </div>
    @else
    <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
            <x-filament::icon icon="heroicon-o-folder-open" class="h-8 w-8 text-gray-400 dark:text-gray-500" />
        </div>
        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">Belum ada proyek</h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Proyek untuk klien ini akan muncul di sini.</p>
    </div>
    @endif
</div>
