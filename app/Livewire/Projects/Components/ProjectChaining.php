<?php

namespace App\Livewire\Projects\Components;

use App\Filament\Resources\ProjectResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Sop;
use App\Models\SubmittedDocument;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class ProjectChaining extends Component implements HasForms
{
    use InteractsWithForms;

    public Project $project;

    public ?array $data = [];

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->form->fill($this->defaultFormState());
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function getIsEligibleProperty(): bool
    {
        return $this->project->statusRecord?->category === 'done';
    }

    public function getHasChildProperty(): bool
    {
        return $this->project->childProjects()->exists();
    }

    public function getChildProperty(): ?Project
    {
        return $this->project->childProjects()
            ->with(['client', 'statusRecord', 'pic'])
            ->latest()
            ->first();
    }

    public function getTransferredFileCountProperty(): int
    {
        $child = $this->child;
        if (!$child) return 0;

        return SubmittedDocument::whereHas(
            'requiredDocument.projectStep',
            fn ($q) => $q->where('project_id', $child->id)
        )
            ->where('notes', 'like', "Disalin dari proyek '%")
            ->count();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Wizard::make([
                    Step::make('Detail Proyek')
                        ->description('Nama, SOP, dan tenggat')
                        ->icon('heroicon-o-document-plus')
                        ->columns(2)
                        ->schema([
                            TextInput::make('name')
                                ->label('Nama Proyek')
                                ->required()
                                ->minLength(3)
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Select::make('sop_id')
                                ->label('SOP Template')
                                ->options(fn () => Sop::orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->live()
                                ->native(false)
                                ->helperText('SOP menentukan langkah-langkah, tugas, dan daftar dokumen yang harus disiapkan di proyek baru.')
                                ->afterStateUpdated(function (Set $set) {
                                    // Reset semua file_assignments saat SOP berubah —
                                    // req doc IDs dari SOP lama tidak valid untuk SOP baru.
                                    $set('file_assignments', $this->emptyFileAssignments());
                                })
                                ->columnSpanFull(),

                            Select::make('type')
                                ->label('Tipe Proyek')
                                ->options([
                                    'single'  => 'Single',
                                    'monthly' => 'Bulanan',
                                    'yearly'  => 'Tahunan',
                                ])
                                ->required()
                                ->native(false),

                            Select::make('priority')
                                ->label('Prioritas')
                                ->options([
                                    'low'    => 'Rendah',
                                    'normal' => 'Normal',
                                    'urgent' => 'Mendesak',
                                ])
                                ->required()
                                ->native(false),

                            DatePicker::make('due_date')
                                ->label('Tenggat')
                                ->required()
                                ->minDate(now()->toDateString())
                                ->columnSpanFull(),
                        ]),

                    Step::make('Oper File')
                        ->description('Pilih file untuk dioper (opsional)')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->schema(fn (Get $get) => $this->buildFileTransferSchema((int) ($get('sop_id') ?? 0))),

                    Step::make('Konfirmasi')
                        ->description('Review sebelum dibuat')
                        ->icon('heroicon-o-check-badge')
                        ->schema([
                            Placeholder::make('summary')
                                ->hiddenLabel()
                                ->content(fn (Get $get) => $this->buildSummaryHtml($get))
                                ->columnSpanFull(),
                        ]),
                ])
                    ->skippable(false)
                    ->submitAction(new HtmlString(\Illuminate\Support\Facades\Blade::render(
                        '<x-filament::button type="submit" color="primary" icon="heroicon-o-plus" wire:loading.attr="disabled" wire:target="create">
                            <span wire:loading.remove wire:target="create">Buat Proyek Lanjutan</span>
                            <span wire:loading wire:target="create">Membuat...</span>
                        </x-filament::button>'
                    ))),
            ]);
    }

    /**
     * Schema untuk step 2 — card-per-file layout.
     *
     * Setiap file dari proyek ini di-render sebagai kartu mandiri yang memuat
     * info file di atas + dropdown tujuan di bawah, supaya hubungan
     * "file ini → ke mana" jelas dalam satu unit visual.
     */
    protected function buildFileTransferSchema(int $sopId): array
    {
        if ($sopId <= 0) {
            return [
                Placeholder::make('no_sop')
                    ->hiddenLabel()
                    ->content('Pilih SOP terlebih dahulu di langkah sebelumnya.')
                    ->columnSpanFull(),
            ];
        }

        $sop = Sop::with(['steps' => fn ($q) => $q->orderBy('order'), 'steps.requiredDocuments'])->find($sopId);
        if (!$sop) {
            return [];
        }

        $sourceFiles = $this->collectSourceFiles();
        $reqDocOptions = $this->buildReqDocOptions($sop);
        $reqDocCount = count($reqDocOptions);

        if (empty($sourceFiles)) {
            return [
                Placeholder::make('empty_source')
                    ->hiddenLabel()
                    ->content(new HtmlString(
                        '<div class="flex items-start gap-2 p-3 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p>Tidak ada file yang bisa dioper dari proyek ini. Anda tetap bisa lanjut ke langkah berikutnya — proyek baru akan dimulai dengan slot dokumen kosong.</p>
                        </div>'
                    ))
                    ->columnSpanFull(),
            ];
        }

        if ($reqDocCount === 0) {
            return [
                Placeholder::make('empty_reqdocs')
                    ->hiddenLabel()
                    ->content(new HtmlString(
                        '<div class="flex items-start gap-2 p-3 rounded-md bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p>SOP yang dipilih belum punya slot dokumen untuk menerima file. Tidak ada tempat untuk mengoper file — silakan lanjut.</p>
                        </div>'
                    ))
                    ->columnSpanFull(),
            ];
        }

        $sourceCount = count($sourceFiles);
        $groupCounts = collect($sourceFiles)->countBy('group')->all();

        $schema = [
            // Info box di atas — penjelasan singkat dalam bahasa sederhana
            Placeholder::make('intro')
                ->hiddenLabel()
                ->content(new HtmlString(
                    "<div class=\"flex items-start gap-3 p-4 rounded-md bg-primary-50 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-900\">
                        <svg class=\"w-5 h-5 text-primary-600 dark:text-primary-400 flex-shrink-0 mt-0.5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\"/></svg>
                        <div class=\"flex-1 text-sm text-primary-900 dark:text-primary-100\">
                            <p class=\"font-medium mb-1\">Cara mengoper file:</p>
                            <p class=\"text-primary-800 dark:text-primary-200\">Di bawah ini ada <strong>{$sourceCount}</strong> file (status disetujui) dari proyek ini, dikelompokkan per dokumen. Untuk setiap file, pilih <strong>file mau dioper ke mana</strong> di proyek baru, atau kosongkan kalau tidak perlu dioper.</p>
                        </div>
                    </div>"
                ))
                ->columnSpanFull(),
        ];

        // Render group header sebelum kelompok file, lalu kartu per file
        $currentGroup = null;
        foreach ($sourceFiles as $file) {
            if ($file['group'] !== $currentGroup) {
                $currentGroup = $file['group'];
                $groupEsc = e($currentGroup);
                $contextEsc = e($file['group_context'] ?? '');
                $count = $groupCounts[$currentGroup] ?? 0;
                $fileWord = $count === 1 ? 'file' : 'file';

                $schema[] = Placeholder::make("group_header_" . md5($currentGroup))
                    ->hiddenLabel()
                    ->content(new HtmlString(
                        "<div class=\"pt-2 pb-1\">
                            <div class=\"flex items-baseline gap-2 flex-wrap\">
                                <div class=\"flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-gray-100\">
                                    <svg class=\"w-4 h-4 text-primary-600 dark:text-primary-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z\"/></svg>
                                    <span>Dokumen: {$groupEsc}</span>
                                </div>
                                <span class=\"text-xs text-gray-500 dark:text-gray-400\">·  {$contextEsc}</span>
                                <span class=\"text-xs text-gray-500 dark:text-gray-400\">·  {$count} {$fileWord}</span>
                            </div>
                        </div>"
                    ))
                    ->columnSpanFull();
            }

            $nameEsc = e($file['name']);
            $hintEsc = $file['status_hint'] ? e($file['status_hint']) : null;
            $hintHtml = $hintEsc
                ? "<span class=\"text-xs text-gray-500 dark:text-gray-400\">Diunggah {$hintEsc}</span>"
                : '';

            $schema[] = \Filament\Forms\Components\Section::make()
                ->schema([
                    // BAGIAN ATAS — info file (DARI)
                    Placeholder::make("source_{$file['key']}")
                        ->hiddenLabel()
                        ->content(new HtmlString(
                            "<div class=\"flex items-start gap-3\">
                                <div class=\"flex-shrink-0 w-10 h-10 rounded-md bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center\">
                                    <svg class=\"w-5 h-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\"/></svg>
                                </div>
                                <div class=\"flex-1 min-w-0\">
                                    <div class=\"text-sm font-semibold text-gray-900 dark:text-gray-100 break-words\">{$nameEsc}</div>
                                    <div class=\"mt-0.5\">{$hintHtml}</div>
                                </div>
                            </div>"
                        ))
                        ->columnSpanFull(),

                    // PANAH PEMISAH — visual cue "↓"
                    Placeholder::make("arrow_{$file['key']}")
                        ->hiddenLabel()
                        ->content(new HtmlString(
                            '<div class="flex items-center gap-2 py-0.5">
                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                            </div>'
                        ))
                        ->columnSpanFull(),

                    // BAGIAN BAWAH — dropdown tujuan (KE)
                    Select::make("file_assignments.{$file['key']}")
                        ->label(new HtmlString(
                            '<span class="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                Dioper ke mana?
                            </span>'
                        ))
                        ->options($reqDocOptions)
                        ->placeholder('— Tidak dioper —')
                        ->searchable()
                        ->native(false)
                        ->columnSpanFull(),
                ])
                ->compact();
        }

        return $schema;
    }

    /**
     * Build HTML summary for step 3 (Konfirmasi).
     */
    protected function buildSummaryHtml(Get $get): HtmlString
    {
        $name = e($get('name') ?? '—');
        $sopId = (int) ($get('sop_id') ?? 0);
        $sopName = $sopId > 0 ? (Sop::find($sopId)?->name ?? '—') : '—';
        $sopName = e($sopName);
        $type = match ($get('type')) {
            'single'  => 'Single',
            'monthly' => 'Bulanan',
            'yearly'  => 'Tahunan',
            default   => '—',
        };
        $priority = match ($get('priority')) {
            'low'    => 'Rendah',
            'normal' => 'Normal',
            'urgent' => 'Mendesak',
            default  => '—',
        };
        $dueDate = $get('due_date')
            ? \Carbon\Carbon::parse($get('due_date'))->translatedFormat('d M Y')
            : '—';

        $assignments = collect($get('file_assignments') ?? [])->filter()->all();
        $transferCount = count($assignments);
        $targetCount = collect($assignments)->unique()->count();

        $clientName = e($this->project->client?->name ?? '—');

        $rows = [
            ['label' => 'Nama proyek', 'value' => $name],
            ['label' => 'Klien',       'value' => $clientName],
            ['label' => 'SOP template', 'value' => $sopName],
            ['label' => 'Tipe',        'value' => $type],
            ['label' => 'Prioritas',   'value' => $priority],
            ['label' => 'Tenggat',     'value' => $dueDate],
        ];

        $rowsHtml = '';
        foreach ($rows as $row) {
            $rowsHtml .= "
                <div class=\"flex items-baseline gap-3 py-1.5 border-b border-gray-100 dark:border-gray-800 last:border-b-0\">
                    <dt class=\"text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide w-32 flex-shrink-0\">{$row['label']}</dt>
                    <dd class=\"text-sm text-gray-900 dark:text-gray-100\">{$row['value']}</dd>
                </div>";
        }

        $transferBadge = $transferCount > 0
            ? "<div class=\"flex items-center gap-2 px-3 py-2 rounded-md bg-success-50 dark:bg-success-950/40 text-success-700 dark:text-success-300 text-sm border border-success-200 dark:border-success-900\">
                <svg class=\"w-4 h-4 flex-shrink-0\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\"/></svg>
                <span><strong>{$transferCount}</strong> file akan dioper ke <strong>{$targetCount}</strong> slot dokumen di proyek baru</span>
              </div>"
            : "<div class=\"px-3 py-2 rounded-md bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 text-sm border border-gray-200 dark:border-gray-700\">
                Tidak ada file yang akan dioper. Proyek baru dibuat dengan slot dokumen kosong.
              </div>";

        $html = "
            <div class=\"space-y-4\">
                <div>
                    <h4 class=\"text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2\">Detail proyek baru</h4>
                    <dl>{$rowsHtml}</dl>
                </div>

                <div>
                    <h4 class=\"text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2\">File yang dioper</h4>
                    {$transferBadge}
                </div>
            </div>
        ";

        return new HtmlString($html);
    }

    protected function buildReqDocOptions(Sop $sop): array
    {
        $options = [];
        foreach ($sop->steps as $step) {
            foreach ($step->requiredDocuments as $reqDoc) {
                $options[$reqDoc->id] = "{$reqDoc->name}  ·  Langkah {$step->order}: {$step->name}";
            }
        }
        return $options;
    }

    /**
     * Kumpulkan file yang bisa dioper.
     *
     * Aturan:
     * - Hanya SubmittedDocument dengan status='approved' (sesuai requirement user)
     * - Dikelompokkan berdasarkan RequiredDocument induk-nya
     * - Dalam 1 RequiredDocument bisa ada banyak SubmittedDocument (versi/revisi)
     */
    protected function collectSourceFiles(): array
    {
        $files = [];

        $this->project->loadMissing('steps.requiredDocuments.submittedDocuments');
        foreach ($this->project->steps as $step) {
            foreach ($step->requiredDocuments as $reqDoc) {
                foreach ($reqDoc->submittedDocuments as $submitted) {
                    if ($submitted->status !== 'approved') {
                        continue;
                    }
                    if (empty($submitted->file_path)) {
                        continue;
                    }

                    $files[] = [
                        'key'           => "submitted_{$submitted->id}",
                        'name'          => basename($submitted->file_path),
                        'group'         => $reqDoc->name,
                        'group_context' => "Langkah {$step->order}: {$step->name}",
                        'status_hint'   => $submitted->created_at?->translatedFormat('d M Y') ?? null,
                    ];
                }
            }
        }

        return $files;
    }

    protected function emptyFileAssignments(): array
    {
        $assignments = [];
        foreach ($this->collectSourceFiles() as $file) {
            $assignments[$file['key']] = null;
        }
        return $assignments;
    }

    public function create(): void
    {
        if (!$this->isEligible) {
            Notification::make()
                ->title('Tidak dapat membuat proyek lanjutan')
                ->body('Proyek ini belum selesai.')
                ->warning()
                ->send();
            return;
        }

        if ($this->hasChild) {
            Notification::make()
                ->title('Proyek lanjutan sudah ada')
                ->body('Satu proyek hanya boleh punya satu lanjutan.')
                ->warning()
                ->send();
            return;
        }

        $data = $this->form->getState();

        $sop = Sop::with(['steps.tasks', 'steps.requiredDocuments'])->find($data['sop_id']);
        if (!$sop) {
            Notification::make()->title('SOP tidak ditemukan')->danger()->send();
            return;
        }

        $newProject = null;
        $copiedFileCount = 0;

        try {
            DB::transaction(function () use ($sop, $data, &$newProject, &$copiedFileCount) {
                $newProject = Project::create([
                    'client_id'         => $this->project->client_id,
                    'parent_project_id' => $this->project->id,
                    'department_id'     => $this->project->department_id,
                    'sop_id'            => $sop->id,
                    'pic_id'            => $this->project->pic_id,
                    'name'              => $data['name'],
                    'description'       => 'Proyek lanjutan dari: ' . $this->project->name,
                    'type'              => $data['type'],
                    'priority'          => $data['priority'],
                    'status'            => 'draft',
                    'due_date'          => $data['due_date'],
                ]);

                $sopReqDocToNew = [];

                foreach ($sop->steps as $sopStep) {
                    $projectStep = $newProject->steps()->create([
                        'name'        => $sopStep->name,
                        'description' => $sopStep->description,
                        'order'       => $sopStep->order,
                        'status'      => 'pending',
                    ]);

                    foreach ($sopStep->tasks as $sopTask) {
                        $projectStep->tasks()->create([
                            'title'       => $sopTask->title,
                            'description' => $sopTask->description,
                            'status'      => 'pending',
                        ]);
                    }

                    foreach ($sopStep->requiredDocuments as $sopDoc) {
                        $newReqDoc = $projectStep->requiredDocuments()->create([
                            'name'        => $sopDoc->name,
                            'description' => $sopDoc->description,
                            'is_required' => $sopDoc->is_required ?? true,
                            'status'      => 'draft',
                        ]);

                        $sopReqDocToNew[(int) $sopDoc->id] = $newReqDoc;
                    }
                }

                $assignments = $data['file_assignments'] ?? [];
                $touchedReqDocs = [];

                foreach ($assignments as $sourceKey => $sopReqDocId) {
                    $sopReqDocId = (int) $sopReqDocId;
                    if ($sopReqDocId <= 0) {
                        continue;
                    }

                    $newReqDoc = $sopReqDocToNew[$sopReqDocId] ?? null;
                    if (!$newReqDoc) {
                        continue;
                    }

                    $resolved = $this->resolveSourcePath((string) $sourceKey);
                    if (!$resolved) {
                        continue;
                    }

                    SubmittedDocument::create([
                        'required_document_id' => $newReqDoc->id,
                        'user_id'              => auth()->id(),
                        'file_path'            => $resolved['path'],
                        'status'               => 'approved',
                        'notes'                => "Disalin dari proyek '{$this->project->name}' ({$resolved['origin']})",
                    ]);

                    $copiedFileCount++;
                    $touchedReqDocs[$newReqDoc->id] = $newReqDoc;
                }

                foreach ($touchedReqDocs as $newReqDoc) {
                    $newReqDoc->status = 'approved';
                    $newReqDoc->save();
                }

                Comment::create([
                    'user_id'          => auth()->id(),
                    'commentable_id'   => $this->project->id,
                    'commentable_type' => Project::class,
                    'content'          => "Proyek lanjutan '{$newProject->name}' dibuat dari proyek ini dengan {$copiedFileCount} file dioper.",
                ]);

                Comment::create([
                    'user_id'          => auth()->id(),
                    'commentable_id'   => $newProject->id,
                    'commentable_type' => Project::class,
                    'content'          => "Proyek ini dibuat sebagai lanjutan dari '{$this->project->name}'.",
                ]);
            });

            $url = ProjectResource::getUrl('view', ['record' => $newProject]);

            Notification::make()
                ->title('Proyek lanjutan berhasil dibuat')
                ->body("'{$newProject->name}' siap dikerjakan. {$copiedFileCount} file dioper dari proyek ini.")
                ->success()
                ->send();

            $this->redirect($url);
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Gagal membuat proyek lanjutan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function resolveSourcePath(string $sourceKey): ?array
    {
        if (str_starts_with($sourceKey, 'deliverable_')) {
            $idx = (int) substr($sourceKey, strlen('deliverable_'));
            $files = is_array($this->project->deliverable_files) ? $this->project->deliverable_files : [];
            if (!isset($files[$idx])) {
                return null;
            }
            $path = $files[$idx]['path'] ?? null;
            if (!$path) {
                return null;
            }
            $name = $files[$idx]['name'] ?? basename($path);
            return ['path' => $path, 'origin' => "deliverable: {$name}"];
        }

        if (str_starts_with($sourceKey, 'submitted_')) {
            $id = (int) substr($sourceKey, strlen('submitted_'));
            $submitted = SubmittedDocument::with('requiredDocument.projectStep')->find($id);
            if (!$submitted || empty($submitted->file_path)) {
                return null;
            }
            $stepName = $submitted->requiredDocument?->projectStep?->name ?? 'step';
            $reqName  = $submitted->requiredDocument?->name ?? 'persyaratan';
            return [
                'path'   => $submitted->file_path,
                'origin' => "{$stepName} › {$reqName}",
            ];
        }

        return null;
    }

    protected function defaultFormState(): array
    {
        return [
            'name'             => 'Lanjutan: ' . $this->project->name,
            'sop_id'           => null,
            'type'             => 'single',
            'priority'         => 'normal',
            'due_date'         => now()->addMonth()->toDateString(),
            'file_assignments' => $this->emptyFileAssignments(),
        ];
    }

    public function render()
    {
        return view('livewire.projects.components.project-chaining');
    }
}
