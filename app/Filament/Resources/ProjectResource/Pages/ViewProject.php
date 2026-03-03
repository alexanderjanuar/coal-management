<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Attributes\Computed;
use App\Models\Comment;
use App\Models\Task;
use App\Models\RequiredDocument;
use Filament\Notifications\Notification;
use Nben\FilamentRecordNav\Concerns\WithRecordNavigation;
use Nben\FilamentRecordNav\Actions\NextRecordAction;
use Nben\FilamentRecordNav\Actions\PreviousRecordAction;

class ViewProject extends ViewRecord
{
    use WithRecordNavigation;
    protected static string $resource = ProjectResource::class;
    protected static string $view = 'filament.pages.projects.show';

    public $newTaskStatus = '';
    public $selectedTaskId = null;



    protected $listeners = [
        'refresh' => '$refresh',
        'documentStatusChanged' => 'handleDocumentStatusChange',
        'documentUploaded' => 'handleDocumentUploaded',
        'documentApprovedWithoutUpload' => 'handleDocumentApprovedWithoutUpload',
        'documentRejected' => 'handleDocumentRejected',
        'documentDeleted' => 'handleDocumentDeleted',
        'requirementStatusUpdated' => 'refreshProjectStatus',
    ];

    /**
     * Add a cache property to track if notification has been sent
     */
    public $completionNotificationSent = false;

    /**
     * Initialize component and update project statuses when the page loads
     */
    public function mount($record): void
    {
        parent::mount($record);

        // Update required document statuses first
        $this->updateRequiredDocumentStatuses();

        // Update project and step statuses when page loads
        $this->updateProjectStepStatus();
        $this->updateProjectStatus();
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
            'client' => $this->record->client,
            'steps' => $this->record->steps,
            'progressPercentage' => $this->calculateProgress(),
        ];
    }

    /**
     * Check if the client is inactive (which locks the project)
     */
    public function isClientInactive(): bool
    {
        return $this->record->client->status === 'Inactive';
    }

    /**
     * Check if the project is locked (completed or client inactive)
     */
    public function isProjectLocked(): bool
    {
        return $this->record->status === 'completed' || $this->isClientInactive();
    }

    /**
     * Update required document statuses based on submitted documents
     */
    private function updateRequiredDocumentStatuses(): void
    {
        foreach ($this->record->steps as $step) {
            foreach ($step->requiredDocuments as $requiredDocument) {
                $submittedDocs = $requiredDocument->submittedDocuments;

                if ($submittedDocs->isEmpty()) {
                    // Check if status is approved_without_document, if so keep it
                    if ($requiredDocument->status === 'approved_without_document') {
                        continue;
                    }

                    if ($requiredDocument->status !== 'draft') {
                        $requiredDocument->status = 'draft';
                        $requiredDocument->save();
                    }
                    continue;
                }

                // Get all submitted document statuses
                $submittedStatuses = $submittedDocs->pluck('status')->toArray();

                // Determine the required document status based on submitted documents
                $newStatus = $this->determineRequiredDocumentStatus($submittedStatuses);

                if ($requiredDocument->status !== $newStatus) {
                    $requiredDocument->status = $newStatus;
                    $requiredDocument->save();
                }
            }
        }
    }

    private function determineRequiredDocumentStatus(array $submittedStatuses): string
    {
        if (empty($submittedStatuses)) {
            return 'draft';
        }

        if (in_array('pending_review', $submittedStatuses)) {
            return 'pending_review';
        }

        if (in_array('approved', $submittedStatuses)) {
            return 'approved';
        }

        // Cek apakah SEMUA status adalah rejected
        $onlyRejected = !array_diff($submittedStatuses, ['rejected']);
        if ($onlyRejected && !empty($submittedStatuses)) {
            return 'rejected';
        }

        if (in_array('uploaded', $submittedStatuses)) {
            return 'uploaded';
        }

        return 'draft';
    }

    private function updateProjectStatus(): void
    {
        $steps = $this->record->steps;

        if ($steps->isEmpty()) {
            return;
        }

        // Only update to in_progress automatically
        if ($steps->where('status', 'in_progress')->count() > 0) {
            $this->record->status = 'in_progress';
            $this->record->save();
        }
    }

    private function updateProjectStepStatus(): void
    {
        foreach ($this->record->steps as $step) {
            $tasks = $step->tasks;
            $documents = $step->requiredDocuments;

            if ($tasks->isEmpty() && $documents->isEmpty()) {
                continue;
            }

            $tasksCompleted = $tasks->every(fn($task) => $task->status === 'completed');

            // Updated logic: step is completed only if all required documents are approved or approved_without_document
            $documentsCompleted = $documents->every(
                fn($doc) =>
                in_array($doc->status, ['approved', 'approved_without_document'])
            );

            // Check for documents that are still in progress
            $hasDocumentsInProgress = $documents->whereIn('status', ['uploaded', 'pending_review'])->count() > 0;

            if (
                $tasks->where('status', 'in_progress')->count() > 0 ||
                $hasDocumentsInProgress
            ) {
                $step->status = 'in_progress';
            } elseif ($tasksCompleted && $documentsCompleted) {
                $step->status = 'completed';
            }

            $step->save();
        }
    }

    private function calculateProgress(): int
    {
        $totalSteps = $this->record->steps->count();
        $completedSteps = $this->record->steps->where('status', 'completed')->count();

        return $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
    }

    public function toggleTaskStatus(Task $task): void
    {
        if ($this->isClientInactive()) {
            Notification::make()
                ->title('Client is inactive')
                ->body('This client is inactive and its projects are locked from modifications.')
                ->warning()
                ->send();
            return;
        }

        if ($this->record->status === 'completed') {
            Notification::make()
                ->title('Project is completed')
                ->body('This project is completed and its tasks can no longer be modified.')
                ->warning()
                ->send();
            return;
        }

        $task->status = $task->status === 'completed' ? 'pending' : 'completed';
        $task->save();

        Notification::make()
            ->title('Task status updated successfully')
            ->success()
            ->send();
    }

    public function updateDocumentStatus(RequiredDocument $document, string $status): void
    {
        if ($this->isClientInactive()) {
            Notification::make()
                ->title('Client is inactive')
                ->body('This client is inactive and its projects are locked from modifications.')
                ->warning()
                ->send();
            return;
        }

        if ($this->record->status === 'completed') {
            Notification::make()
                ->title('Project is completed')
                ->body('This project is completed and its documents can no longer be modified.')
                ->warning()
                ->send();
            return;
        }

        $document->status = $status;
        $document->save();

        Notification::make()
            ->title("Document status updated to " . ucfirst($status))
            ->success()
            ->send();
    }

    // Step Status Management
    public function updateStepStatus(string $status): void
    {
        $this->record->status = $status;
        $this->record->save();

        Notification::make()
            ->title("Project status updated to " . ucfirst($status))
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        // Check if current user has the required role
        $hasRequiredRole = auth()->user()->hasAnyRole(['direktur', 'project-manager', 'super-admin', 'verificator']);

        // Check if client is inactive
        $clientInactive = $this->isClientInactive();

        if (!$hasRequiredRole) {
            return [
                // Only include the other actions if user doesn't have required role
                Actions\Action::make('edit')
                    ->url(static::getResource()::getUrl('edit', ['record' => $this->record]))
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn() => !$this->isProjectLocked())
                    ->button(),

                Actions\Action::make('viewActivity')
                    ->label('View Activity Log')
                    ->icon('heroicon-o-clock')
                    ->url(fn() => ProjectResource::getUrl('activity', ['record' => $this->record])),
            ];
        }

        // Update the document completion check logic
        $allDocumentsResolved = true;
        $unfinishedItems = [];

        foreach ($this->record->steps as $step) {
            foreach ($step->requiredDocuments as $document) {
                // Check if document is not approved or approved_without_document
                if (!in_array($document->status, ['approved', 'approved_without_document'])) {
                    $statusLabel = match ($document->status) {
                        'approved_without_document' => 'Disetujui Tanpa Dokumen',
                        'approved' => 'Disetujui',
                        'pending_review' => 'Menunggu Review',
                        'uploaded' => 'Diunggah',
                        'rejected' => 'Ditolak',
                        'draft' => 'Draft',
                        default => ucfirst(str_replace('_', ' ', $document->status))
                    };
                    $unfinishedItems[] = "Document: {$document->name} ({$statusLabel})";
                    $allDocumentsResolved = false;
                }
            }
        }

        // All documents resolved is the only requirement now
        $requirementsMet = $allDocumentsResolved && !$clientInactive;

        // Determine tooltip message if requirements are not met
        $tooltipMessage = "";
        if (!$requirementsMet) {
            if ($clientInactive) {
                $tooltipMessage = "This project cannot be completed because the client is inactive. ";
            } else {
                $tooltipMessage = "This project cannot be completed yet. ";

                if (!$allDocumentsResolved) {
                    if (count($unfinishedItems) > 0) {
                        $tooltipMessage .= "Unfinished documents: " . implode(", ", array_slice($unfinishedItems, 0, 3));
                        if (count($unfinishedItems) > 3) {
                            $tooltipMessage .= " and " . (count($unfinishedItems) - 3) . " more.";
                        }
                    }
                }
            }
        }

        // Check if project is already completed
        $isCompleted = $this->record->status === 'completed';

        return [
            Actions\Action::make('completeProject')
                ->label($isCompleted ? 'Perbarui Deliverable' : 'Selesaikan Proyek')
                ->icon($isCompleted ? 'heroicon-o-arrow-up-tray' : 'heroicon-o-check-circle')
                ->color($isCompleted ? 'warning' : 'success')
                ->tooltip($isCompleted ? 'Tambah file atau perbarui catatan penyelesaian' : $tooltipMessage)
                ->disabled(!$isCompleted && !$requirementsMet)
                ->requiresConfirmation()
                ->modalHeading($isCompleted ? 'Perbarui Deliverable Proyek' : 'Selesaikan Proyek')
                ->modalDescription($isCompleted ? 'Tambahkan file deliverable baru atau perbarui catatan penyelesaian proyek ini.' : 'Unggah file deliverable dan tambahkan catatan penyelesaian untuk proyek ini.')
                ->modalSubmitActionLabel($isCompleted ? 'Simpan Perubahan' : 'Selesaikan Proyek')
                ->modalCancelActionLabel('Batal')
                ->modalWidth('3xl')
                ->visible(!$clientInactive)
                ->fillForm(function () use ($isCompleted) {
                    if (!$isCompleted) {
                        return [
                            'result_notes' => $this->record->result_notes,
                        ];
                    }

                    // For completed projects, show existing files
                    $existingFiles = $this->record->deliverable_files ?? [];

                    return [
                        'existing_files' => $existingFiles,
                        'result_notes' => $this->record->result_notes,
                    ];
                })
                ->form([
                    // File Deliverable yang sudah ada (selalu baca fresh dari record)
                    Section::make('File Deliverable Saat Ini')
                        ->description('File deliverable yang sudah tersimpan — klik untuk mengunduh atau menghapus')
                        ->icon('heroicon-o-archive-box')
                        ->schema([
                            Placeholder::make('existing_files_list')
                                ->label('')
                                ->content(function () {
                                    // Selalu refresh agar data terbaru dari DB terbaca
                                    $this->record->refresh();

                                    if (empty($this->record->deliverable_files)) {
                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="flex items-center gap-2 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-600">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada file deliverable yang tersimpan.</p>
                                            </div>'
                                        );
                                    }

                                    $files = $this->record->deliverable_files;
                                    $html = '<div class="space-y-2">';
                                    foreach ($files as $index => $file) {
                                        $fileName = $file['name'] ?? basename($file['path'] ?? '');
                                        $fileSize = isset($file['size']) ? number_format($file['size'] / 1024, 2) . ' KB' : 'Ukuran tidak diketahui';
                                        $uploadedAt = isset($file['uploaded_at']) ? \Carbon\Carbon::parse($file['uploaded_at'])->format('d M Y, H:i') : 'Tanggal tidak diketahui';
                                        $fileUrl = \Storage::disk('public')->url($file['path']);
                                        $isCopied = ($file['source'] ?? null) === 'project_document';
                                        $sourceStep = $file['source_step'] ?? null;
                                        $sourceReq = $file['source_req'] ?? null;

                                        $badge = $isCopied
                                            ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">📋 Disalin dari Dokumen Proyek</span>'
                                            : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 border border-blue-200 dark:border-blue-700">📤 Diunggah</span>';

                                        $sourceLine = ($isCopied && $sourceStep)
                                            ? '<p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">Dari: ' . e($sourceStep) . ($sourceReq ? ' › ' . e($sourceReq) : '') . '</p>'
                                            : '';

                                        $html .= '
                                            <div class="flex items-center justify-between p-3 ' . ($isCopied ? 'bg-emerald-50 dark:bg-emerald-950 border-emerald-200 dark:border-emerald-800' : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700') . ' rounded-lg border">
                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                    <div class="flex-shrink-0 w-9 h-9 rounded-lg ' . ($isCopied ? 'bg-emerald-100 dark:bg-emerald-900' : 'bg-gray-100 dark:bg-gray-700') . ' flex items-center justify-center">
                                                        <svg class="w-5 h-5 ' . ($isCopied ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400') . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">' . e($fileName) . '</p>
                                                            ' . $badge . '
                                                        </div>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">' . $fileSize . ' &bull; Ditambahkan ' . $uploadedAt . '</p>
                                                        ' . $sourceLine . '
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 ml-3">
                                                    <a href="' . $fileUrl . '" 
                                                       download
                                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                        Unduh
                                                    </a>
                                                    <button type="button"
                                                            wire:click="removeDeliverableFile(' . $index . ')"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 dark:bg-red-900 dark:text-red-300 dark:hover:bg-red-800 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        ';
                                    }

                                    $html .= '</div>';

                                    return new \Illuminate\Support\HtmlString($html);
                                }),
                        ])
                        // Tampilkan kapan saja ada deliverable, termasuk setelah baru saja disimpan
                        ->visible(fn() => !empty($this->record->deliverable_files))
                        ->collapsible(),

                    // Salin dari Dokumen Proyek
                    Section::make('Salin dari Dokumen Proyek')
                        ->description('Pilih dokumen yang sudah diunggah atau disetujui dari langkah-langkah proyek ini untuk langsung dijadikan deliverable — tanpa perlu unggah ulang.')
                        ->icon('heroicon-o-document-duplicate')
                        ->collapsible()
                        ->collapsed()
                        ->schema(function () {
                            $docs = $this->getAvailableProjectDocuments();
                            $isEmpty = $docs->isEmpty();

                            // Opsi untuk CheckboxList (selalu di-register meskipun kosong)
                            $options = $docs->pluck('label', 'id')->toArray();
                            $descriptions = $docs->pluck('status_label', 'id')->toArray();

                            // Selalu kembalikan schema yang SAMA (tidak branching) agar Filament
                            // selalu mendaftarkan copy_document_ids ke dalam $data saat submit.
                            return [
                                // --- Pesan kosong (tampil jika tidak ada dok) ---
                                Placeholder::make('no_docs_notice')
                                    ->label('')
                                    ->hidden(!$isEmpty)
                                    ->content(new \Illuminate\Support\HtmlString(
                                        '<div class="flex items-center gap-2 p-3 rounded-lg bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800">
                                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <p class="text-sm text-amber-700 dark:text-amber-300">Tidak ada dokumen yang disetujui atau diunggah pada langkah-langkah proyek ini.</p>
                                        </div>'
                                    )),

                                // --- Header info jumlah dokumen ---
                                Placeholder::make('copy_docs_header')
                                    ->label('')
                                    ->hidden($isEmpty)
                                    ->content(new \Illuminate\Support\HtmlString(
                                        '<div class="flex items-center gap-2 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-800">
                                            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <p class="text-sm text-emerald-700 dark:text-emerald-300">Ditemukan <strong>' . $docs->count() . '</strong> dokumen tersedia. Dokumen yang dipilih akan ditandai <strong>"Disalin dari Dokumen Proyek"</strong>.</p>
                                        </div>'
                                    )),

                                // --- CheckboxList — SELALU di-register agar $data['copy_document_ids'] tersedia ---
                                \Filament\Forms\Components\CheckboxList::make('copy_document_ids')
                                    ->label('Dokumen Proyek yang Tersedia')
                                    ->options($options)
                                    ->descriptions($descriptions)
                                    ->columns(1)
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->live()
                                    ->hidden($isEmpty)
                                    ->helperText('Centang dokumen yang ingin disalin sebagai deliverable. File yang sudah ada akan dilewati secara otomatis.'),

                                // --- Pratinjau real-time dokumen yang dipilih ---
                                Placeholder::make('selected_docs_preview')
                                    ->label('Pratinjau File yang Akan Ditambahkan')
                                    ->hidden($isEmpty)
                                    ->content(function (\Filament\Forms\Get $get) use ($docs) {
                                $selectedIds = $get('copy_document_ids') ?? [];

                                if (empty($selectedIds)) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="flex items-center gap-2 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-600">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">Pilih dokumen di atas untuk melihat pratinjau di sini.</p>
                                                </div>'
                                    );
                                }

                                $docsById = $docs->keyBy('id');
                                $html = '<div class="space-y-2">';

                                foreach ($selectedIds as $docId) {
                                    $doc = $docsById->get($docId);
                                    if (!$doc)
                                        continue;

                                    $fileName = basename($doc['path']);
                                    $statusLabel = $doc['status_label'];
                                    $statusColor = match ($doc['status']) {
                                        'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300 border-emerald-200 dark:border-emerald-700',
                                        'pending_review' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300 border-amber-200 dark:border-amber-700',
                                        'uploaded' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 border-blue-200 dark:border-blue-700',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                                    };

                                    $html .= '
                                                <div class="flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-950 rounded-lg border border-emerald-200 dark:border-emerald-800 border-l-4 border-l-emerald-500">
                                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">' . e($fileName) . '</p>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ' . $statusColor . '">' . e($statusLabel) . '</span>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">📋 Akan disalin</span>
                                                        </div>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">' . e($doc['step_name']) . ' › ' . e($doc['req_name']) . '</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            ';
                                }

                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                            ];
                        }),

                    FileUpload::make('deliverable_files')
                        ->label($isCompleted ? 'Unggah File Baru' : 'File Deliverable')
                        ->multiple()
                        ->directory(function () {
                            $clientName = \Illuminate\Support\Str::slug($this->record->client->name);
                            $projectName = strtoupper(\Illuminate\Support\Str::slug($this->record->name));
                            return "clients/{$clientName}/KEGIATAN PERUSAHAAN/{$projectName}/deliverables";
                        })
                        ->maxSize(10240)
                        ->maxFiles(10)
                        ->helperText($isCompleted ? 'Unggah file tambahan untuk diserahkan ke klien (Maks 10MB per file, hingga 10 file)' : 'Unggah file yang akan diserahkan ke klien (Maks 10MB per file, hingga 10 file)')
                        ->preserveFilenames()
                        ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/*', 'application/zip'])
                        ->downloadable()
                        ->openable()
                        ->reorderable()
                        ->storeFileNamesIn('deliverable_file_names'),

                    Textarea::make('result_notes')
                        ->label('Catatan Penyelesaian')
                        ->placeholder('Jelaskan hasil dan deliverable proyek ini...')
                        ->rows(5)
                        ->helperText($isCompleted ? 'Perbarui catatan mengenai pekerjaan yang telah diselesaikan dan informasi penting lainnya untuk klien' : 'Tambahkan catatan mengenai hasil pekerjaan dan informasi penting untuk klien')
                        ->maxLength(1000)
                        ->default(fn() => $this->record->result_notes),
                ])
                ->action(function (array $data) use ($isCompleted) {
                    // Get existing deliverable files
                    $existingFiles = $this->record->deliverable_files ?? [];

                    // Track existing paths to prevent duplicates
                    $existingPaths = collect($existingFiles)->pluck('path')->filter()->values()->all();

                    // Process newly uploaded files and create structured data
                    $newDeliverableFiles = [];

                    if (!empty($data['deliverable_files'])) {
                        $fileNames = $data['deliverable_file_names'] ?? [];

                        foreach ($data['deliverable_files'] as $index => $filePath) {
                            if (in_array($filePath, $existingPaths)) {
                                continue; // skip duplicate
                            }
                            $fileName = $fileNames[$index] ?? basename($filePath);
                            $fileSize = \Storage::disk('public')->size($filePath);
                            $mimeType = \Storage::disk('public')->mimeType($filePath);

                            $newDeliverableFiles[] = [
                                'name' => $fileName,
                                'path' => $filePath,
                                'size' => $fileSize,
                                'type' => $mimeType,
                                'uploaded_at' => now()->toDateTimeString(),
                            ];
                            $existingPaths[] = $filePath;
                        }
                    }

                    // Proses dokumen yang dipilih dari langkah proyek
                    $copiedDocumentFiles = [];
                    if (!empty($data['copy_document_ids'])) {
                        $submittedDocs = \App\Models\SubmittedDocument::with([
                            'requiredDocument.projectStep',
                        ])->whereIn('id', $data['copy_document_ids'])->get();

                        foreach ($submittedDocs as $doc) {
                            $filePath = $doc->file_path;

                            // Lewati jika path kosong
                            if (empty($filePath)) {
                                continue;
                            }

                            // Lewati jika sudah ada di deliverable (duplicate check)
                            if (in_array($filePath, $existingPaths)) {
                                continue;
                            }

                            $stepName = $doc->requiredDocument?->projectStep?->name ?? 'Step';
                            $reqDocName = $doc->requiredDocument?->name ?? 'Document';
                            $fileName = basename($filePath);

                            // Coba baca ukuran file; fallback 0 jika tidak ada di disk ini
                            try {
                                $fileSize = \Storage::disk('public')->size($filePath);
                            } catch (\Exception $e) {
                                $fileSize = 0;
                            }

                            // Coba deteksi MIME; fallback berdasarkan ekstensi
                            try {
                                $mimeType = \Storage::disk('public')->mimeType($filePath);
                            } catch (\Exception $e) {
                                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $mimeType = match ($ext) {
                                    'pdf' => 'application/pdf',
                                    'doc' => 'application/msword',
                                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'xls' => 'application/vnd.ms-excel',
                                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'jpg', 'jpeg' => 'image/jpeg',
                                    'png' => 'image/png',
                                    'zip' => 'application/zip',
                                    default => 'application/octet-stream',
                                };
                            }

                            $copiedDocumentFiles[] = [
                                'name' => $fileName,
                                'path' => $filePath,
                                'size' => $fileSize,
                                'type' => $mimeType,
                                'uploaded_at' => now()->toDateTimeString(),
                                'source' => 'project_document',
                                'source_step' => $stepName,
                                'source_req' => $reqDocName,
                            ];
                            $existingPaths[] = $filePath;
                        }
                    }

                    // Merge: existing + new uploads + copied documents
                    $allDeliverableFiles = array_merge($existingFiles, $newDeliverableFiles, $copiedDocumentFiles);

                    // Simpan deliverable dan status proyek
                    $this->record->update([
                        'deliverable_files' => $allDeliverableFiles,
                        'result_notes' => $data['result_notes'] ?? $this->record->result_notes,
                        'status' => 'completed',
                    ]);

                    // Refresh agar Livewire membaca data terbaru dari DB
                    $this->record->refresh();

                    // Only mark all steps/tasks/documents as completed if project wasn't already completed
                    if (!$isCompleted) {
                        // Mark all steps as completed
                        foreach ($this->record->steps as $step) {
                            $step->status = 'completed';
                            $step->save();

                            // Mark all tasks as completed
                            foreach ($step->tasks as $task) {
                                if ($task->status !== 'completed') {
                                    $task->status = 'completed';
                                    $task->save();
                                }
                            }

                            // Mark all documents as approved/completed
                            foreach ($step->requiredDocuments as $document) {
                                if (!in_array($document->status, ['approved', 'approved_without_document'])) {
                                    $document->status = 'approved';
                                    $document->save();
                                }
                            }
                        }
                    }

                    // Create record in activity log
                    if ($isCompleted) {
                        $activityMessage = "Project deliverables updated.";

                        if (!empty($newDeliverableFiles)) {
                            $activityMessage .= " " . count($newDeliverableFiles) . " additional deliverable file(s) uploaded.";
                        }

                        if (!empty($copiedDocumentFiles)) {
                            $activityMessage .= " " . count($copiedDocumentFiles) . " document(s) copied from project steps.";
                        }

                        if ($data['result_notes'] !== $this->record->getOriginal('result_notes')) {
                            $activityMessage .= " Completion notes updated.";
                        }
                    } else {
                        $activityMessage = "Project marked as completed and locked. All steps, tasks, and documents were finalized.";

                        if (!empty($newDeliverableFiles)) {
                            $activityMessage .= " " . count($newDeliverableFiles) . " deliverable file(s) uploaded.";
                        }

                        if (!empty($copiedDocumentFiles)) {
                            $activityMessage .= " " . count($copiedDocumentFiles) . " document(s) copied from project steps.";
                        }

                        if (!empty($data['result_notes'])) {
                            $activityMessage .= " Completion notes added.";
                        }
                    }

                    Comment::create([
                        'user_id' => auth()->id(),
                        'commentable_id' => $this->record->id,
                        'commentable_type' => get_class($this->record),
                        'content' => $activityMessage
                    ]);

                    $copiedCount = count($copiedDocumentFiles);
                    $uploadedCount = count($newDeliverableFiles);
                    $bodyParts = [];
                    if ($uploadedCount > 0)
                        $bodyParts[] = "{$uploadedCount} file berhasil diunggah.";
                    if ($copiedCount > 0)
                        $bodyParts[] = "{$copiedCount} dokumen berhasil disalin dari langkah proyek.";

                    Notification::make()
                        ->title($isCompleted ? 'Deliverable berhasil diperbarui' : 'Proyek berhasil diselesaikan')
                        ->body($isCompleted
                            ? (empty($bodyParts) ? 'Deliverable proyek telah diperbarui.' : implode(' ', $bodyParts))
                            : 'Proyek telah ditandai selesai dengan deliverable dan catatan.')
                        ->success()
                        ->send();
                }),

            // Update deliverables notification sub-title
            // (blank intentional to keep action block clean)

            // Modify edit action to be disabled when project is completed or client inactive
            Actions\Action::make('edit')
                ->url(static::getResource()::getUrl('edit', ['record' => $this->record]))
                ->icon('heroicon-o-pencil-square')
                ->visible(fn() => !$this->isProjectLocked())
                ->button(),

            Actions\Action::make('viewActivity')
                ->label('View Activity Log')
                ->icon('heroicon-o-clock')
                ->url(fn() => ProjectResource::getUrl('activity', ['record' => $this->record])),

            PreviousRecordAction::make(),
            NextRecordAction::make(),
        ];
    }

    /**
     * Send notification to project managers and directors
     */
    protected function notifyProjectReadyForCompletion(): void
    {
        // Get users assigned to this project who are directors or project managers
        $projectManagers = \App\Models\User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['director', 'project-manager', 'verificator']);
        })
            ->whereHas('userProjects', function ($query) {
                $query->where('project_id', $this->record->id);
            })
            ->get();

        // If no project managers are assigned, get all project managers/directors in the system
        if ($projectManagers->isEmpty()) {
            $projectManagers = \App\Models\User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['director', 'project-manager', 'super-admin', 'verificator']);
            })->get();
        }


        // Also create an activity log entry for the project
        Comment::create([
            'user_id' => auth()->id(),
            'commentable_id' => $this->record->id,
            'commentable_type' => get_class($this->record),
            'content' => "Project is now ready for completion."
        ]);
    }

    public function updateTaskStatus($taskId, $status): void
    {
        $this->selectedTaskId = $taskId;
        $this->newTaskStatus = $status;
    }

    public function confirmStatusChange(): void
    {
        $task = Task::find($this->selectedTaskId);
        $oldStatus = $task->status;

        $task->status = $this->newTaskStatus;
        $task->save();

        Comment::create([
            'user_id' => auth()->id(),
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'content' => "Status changed from " . ucfirst($oldStatus) . " to " . ucfirst($this->newTaskStatus)
        ]);

        $this->dispatch('close-modal', ['id' => "confirm-status-modal-{$this->selectedTaskId}"]);

        // Update statuses in the correct order
        $this->updateRequiredDocumentStatuses();
        $this->updateProjectStepStatus();
        $this->updateProjectStatus();
    }

    /**
     * Remove a deliverable file from the project
     */
    public function removeDeliverableFile(int $index): void
    {
        if ($this->isClientInactive()) {
            Notification::make()
                ->title('Client is inactive')
                ->body('This client is inactive and its projects are locked from modifications.')
                ->warning()
                ->send();
            return;
        }

        $deliverableFiles = $this->record->deliverable_files ?? [];

        if (!isset($deliverableFiles[$index])) {
            Notification::make()
                ->title('File not found')
                ->body('The file you are trying to remove does not exist.')
                ->warning()
                ->send();
            return;
        }

        $fileToRemove = $deliverableFiles[$index];
        $fileName = $fileToRemove['name'] ?? basename($fileToRemove['path'] ?? 'Unknown file');

        // Delete the actual file from storage
        if (isset($fileToRemove['path']) && \Storage::disk('public')->exists($fileToRemove['path'])) {
            \Storage::disk('public')->delete($fileToRemove['path']);
        }

        // Remove from array
        unset($deliverableFiles[$index]);

        // Re-index array to avoid gaps
        $deliverableFiles = array_values($deliverableFiles);

        // Update the record
        $this->record->update([
            'deliverable_files' => $deliverableFiles
        ]);

        // Log the activity
        Comment::create([
            'user_id' => auth()->id(),
            'commentable_id' => $this->record->id,
            'commentable_type' => get_class($this->record),
            'content' => "Deliverable file '{$fileName}' was removed from the project."
        ]);

        Notification::make()
            ->title('File removed successfully')
            ->body("The file '{$fileName}' has been removed from deliverables.")
            ->success()
            ->send();

        // Refresh the component to show updated file list
        $this->record->refresh();
    }

    /**
     * Get all submitted documents in this project that have a real file and can be copied to deliverables.
     * Returns a flat collection with id, label, path, status_label, status, step_name, req_name.
     */
    protected function getAvailableProjectDocuments(): \Illuminate\Support\Collection
    {
        $results = collect();

        foreach ($this->record->steps as $step) {
            foreach ($step->requiredDocuments as $reqDoc) {
                foreach ($reqDoc->submittedDocuments as $doc) {
                    if (!in_array($doc->status, ['approved', 'uploaded', 'pending_review'])) {
                        continue;
                    }

                    if (empty($doc->file_path)) {
                        continue;
                    }

                    $statusLabel = match ($doc->status) {
                        'approved' => '✅ Approved',
                        'pending_review' => '👁️ Pending Review',
                        'uploaded' => '📤 Uploaded',
                        default => ucfirst($doc->status),
                    };

                    $results->push([
                        'id' => $doc->id,
                        'label' => "[{$step->name}] {$reqDoc->name} — " . basename($doc->file_path),
                        'path' => $doc->file_path,
                        'status' => $doc->status,
                        'status_label' => $statusLabel,
                        'step_name' => $step->name,
                        'req_name' => $reqDoc->name,
                    ]);
                }
            }
        }

        return $results;
    }
}