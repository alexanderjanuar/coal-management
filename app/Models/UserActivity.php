<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'actionable_type',
        'actionable_id',
        'old_values',
        'new_values',
        'client_id',
        'project_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    // Helper Methods
    public static function log(array $data): self
    {
        $activity = self::create(array_merge($data, [
            'user_id' => auth()->id(),
        ]));

        // Auto-create daily task untuk user
        $activity->createDailyTask();

        return $activity;
    }

    /**
     * Create daily task based on activity
     */
    public function createDailyTask(): void
    {
        // Define actions yang perlu generate daily task
        $taskGeneratingActions = [
            'tax_report_created' => 'Review laporan pajak',
            'invoice_created' => 'Validasi faktur',
            'document_submitted' => 'Review dokumen',
            'document_status_changed' => 'Follow up dokumen',
            'project_created' => 'Setup proyek',
            'bupot_created' => 'Verifikasi Bupot',
            'income_tax_created' => 'Verifikasi PPh 21',
            'client_created' => 'Setup klien baru',
            // TAMBAHAN BARU untuk legal documents
            'legal_document_uploaded' => 'Review dokumen legal',
            'client_document_uploaded' => 'Verifikasi dokumen klien',
            'document_uploaded' => 'Review dokumen',
        ];

        // Cek apakah action ini perlu daily task
        if (!array_key_exists($this->action, $taskGeneratingActions)) {
            return;
        }

        // Generate task berdasarkan activity
        $taskTitle = $this->generateTaskTitle($taskGeneratingActions[$this->action]);
        $taskDescription = $this->generateTaskDescription();
        $priority = $this->determineTaskPriority();

        // Determine project_id dan client_id berdasarkan actionable
        $projectId = $this->determineTaskProjectId();
        $clientId = $this->determineTaskClientId();

        // Create daily task
        $dailyTask = \App\Models\DailyTask::create([
            'title' => $taskTitle,
            'description' => $taskDescription,
            'project_id' => $projectId,
            'created_by' => $this->user_id,
            'priority' => $priority,
            'status' => 'pending',
            'task_date' => now()->format('Y-m-d'),
            'start_task_date' => now()->format('Y-m-d'),
        ]);

        // Auto-assign ke user yang membuat activity
        $dailyTask->assignToUser($this->user);
    }

    /**
     * Determine project_id untuk daily task
     */
    private function determineTaskProjectId(): ?int
    {
        // Prioritas 1: Dari activity langsung
        if ($this->project_id) {
            return $this->project_id;
        }

        // Prioritas 2: Dari actionable object
        if ($this->actionable) {
            switch ($this->actionable_type) {
                case 'App\Models\SubmittedDocument':
                    return $this->actionable->requiredDocument?->projectStep?->project?->id;
                    
                case 'App\Models\Project':
                    return $this->actionable->id;
                    
                case 'App\Models\Invoice':
                    $client = $this->actionable->taxReport?->client;
                    if ($client) {
                        return $client->projects()
                                     ->whereIn('status', ['draft', 'in_progress', 'review'])
                                     ->latest()
                                     ->first()?->id;
                    }
                    break;
                    
                case 'App\Models\TaxReport':
                    $client = $this->actionable->client;
                    if ($client) {
                        return $client->projects()
                                     ->whereIn('status', ['draft', 'in_progress', 'review'])
                                     ->latest()
                                     ->first()?->id;
                    }
                    break;
                    
                case 'App\Models\Bupot':
                case 'App\Models\IncomeTax':
                    $client = $this->actionable->taxReport?->client;
                    if ($client) {
                        return $client->projects()
                                     ->whereIn('status', ['draft', 'in_progress', 'review'])
                                     ->latest()
                                     ->first()?->id;
                    }
                    break;

                case 'App\Models\ClientDocument':
                    $client = $this->actionable->client;
                    if ($client) {
                        return $client->projects()
                                     ->whereIn('status', ['draft', 'in_progress', 'review'])
                                     ->latest()
                                     ->first()?->id;
                    }
                    break;
            }
        }

        return null;
    }

    /**
     * Determine client_id untuk daily task
     */
    private function determineTaskClientId(): ?int
    {
        // Prioritas 1: Dari activity langsung
        if ($this->client_id) {
            return $this->client_id;
        }

        // Prioritas 2: Dari actionable object
        if ($this->actionable) {
            switch ($this->actionable_type) {
                case 'App\Models\SubmittedDocument':
                    return $this->actionable->requiredDocument?->projectStep?->project?->client?->id;
                    
                case 'App\Models\Project':
                    return $this->actionable->client_id;
                    
                case 'App\Models\Client':
                    return $this->actionable->id;
                    
                case 'App\Models\Invoice':
                    return $this->actionable->taxReport?->client?->id;
                    
                case 'App\Models\TaxReport':
                    return $this->actionable->client_id;
                    
                case 'App\Models\Bupot':
                case 'App\Models\IncomeTax':
                    return $this->actionable->taxReport?->client?->id;

                case 'App\Models\ClientDocument':
                    return $this->actionable->client_id;
            }
        }

        return null;
    }

    /**
     * Generate task title berdasarkan activity
     */
    private function generateTaskTitle(string $baseTitle): string
    {
        return $this->summarizeDescription();
    }

    /**
     * Summarize activity description menjadi task title
     */
    private function summarizeDescription(): string
    {
        $description = $this->description;
        
        // Remove "oleh [user]" dari akhir
        $description = preg_replace('/ oleh .+$/', '', $description);
        
        // Pattern untuk berbagai jenis activity dengan kata kerja yang tepat
        $patterns = [
            // Faktur patterns - MEMBUAT
            '/Faktur (.+?) \((.+?)\) telah dibuat untuk (.+)/' => 'Membuat faktur $1 untuk $3',
            '/Faktur (.+?) telah diperbarui/' => 'Memperbarui faktur $1',
            '/Faktur (.+?) telah dihapus/' => 'Menghapus faktur $1',
            
            // Laporan pajak patterns - MEMBUAT/MENGUPDATE
            '/Laporan pajak (.+?) untuk (.+?) telah dibuat/' => 'Membuat laporan pajak $1 untuk $2',
            '/Status laporan (.+?) (.+?) untuk (.+?) diubah menjadi: (.+)/' => 'Mengupdate status $1 $2 untuk $3',
            
            // Project patterns - MEMBUAT/MENGUPDATE
            '/Proyek \'(.+?)\' telah dibuat untuk klien (.+)/' => 'Membuat proyek $1 untuk $2',
            '/Status proyek \'(.+?)\' diubah dari (.+?) menjadi (.+)/' => 'Mengupdate status proyek $1 menjadi $3',
            '/Prioritas proyek \'(.+?)\' diubah dari (.+?) menjadi (.+)/' => 'Mengupdate prioritas proyek $1 menjadi $3',
            
            // Document patterns - MENGUPLOAD
            '/Dokumen \'(.+?)\' .+ diunggah untuk klien (.+)/' => 'Mengupload dokumen $1 untuk $2',
            '/Dokumen \'(.+?)\' .+ telah DISETUJUI - (.+)/' => 'Menyetujui dokumen $1 untuk $2',
            '/Dokumen \'(.+?)\' .+ DITOLAK - (.+)/' => 'Menolak dokumen $1 untuk $2',
            
            // Submitted Document patterns - MENGUPLOAD/SUBMIT
            '/Dokumen \'(.+?)\' untuk persyaratan \'(.+?)\' telah diunggah untuk (.+) - (.+)/' => 'Mengupload dokumen $1 untuk $3',
            '/Dokumen \'(.+?)\' untuk persyaratan \'(.+?)\' telah DISETUJUI - (.+)/' => 'Menyetujui dokumen $1 untuk $3',
            '/Dokumen \'(.+?)\' untuk persyaratan \'(.+?)\' telah DITOLAK - (.+)/' => 'Menolak dokumen $1 untuk $3',
            '/Dokumen \'(.+?)\' untuk persyaratan \'(.+?)\' sedang DIPERIKSA - (.+)/' => 'Memeriksa dokumen $1 untuk $3',
            
            // Bupot patterns - MEMBUAT/MENGUPDATE
            '/Bupot (.+?) (.+?) untuk (.+?) telah dibuat \(Periode: (.+?)\)/' => 'Membuat Bupot $1 $2 untuk $3',
            '/Jumlah bupot (.+?) diubah menjadi (.+)/' => 'Mengupdate jumlah Bupot $1',
            '/Bukti setor bupot (.+?) telah diunggah/' => 'Mengupload bukti setor Bupot $1',
            
            // PPh patterns - MEMBUAT/MENGUPDATE
            '/PPh 21 untuk (.+?) telah dibuat \(TER: (.+?), PPh 21: (.+?)\)/' => 'Membuat PPh 21 untuk $1',
            '/Jumlah PPh 21 untuk (.+?) telah diperbarui/' => 'Mengupdate PPh 21 untuk $1',
            '/Bukti setor PPh 21 untuk (.+?) telah diunggah/' => 'Mengupload bukti setor PPh 21 untuk $1',
            
            // Client patterns - MEMBUAT/MENGUPDATE
            '/Klien baru \'(.+?)\' telah ditambahkan ke sistem/' => 'Membuat klien baru $1',
            '/Status klien \'(.+?)\' diubah menjadi: (.+)/' => 'Mengupdate status klien $1 menjadi $2',
            '/PIC klien \'(.+?)\' diubah menjadi: (.+)/' => 'Mengupdate PIC klien $1 menjadi $2',
            '/Account Representative klien \'(.+?)\' diubah menjadi: (.+)/' => 'Mengupdate AR klien $1 menjadi $2',
            '/Kontrak (.+?) klien \'(.+?)\' diubah menjadi: (.+)/' => 'Mengupdate kontrak $1 klien $2',
            
            // Task patterns - MEMBUAT/MENYELESAIKAN
            '/Tugas harian \'(.+?)\' telah dibuat/' => 'Membuat tugas $1',
            '/Tugas \'(.+?)\' telah diselesaikan/' => 'Menyelesaikan tugas $1',
            '/Mulai mengerjakan tugas \'(.+?)\'/' => 'Memulai tugas $1',

            // TAMBAHAN BARU - Legal Document patterns
            '/Dokumen legal \'(.+?)\' telah diunggah untuk klien (.+)/' => 'Mengupload dokumen legal $1 untuk $2',
            '/Dokumen legal \'(.+?)\' telah disetujui untuk klien (.+)/' => 'Menyetujui dokumen legal $1 untuk $2',
            '/Dokumen legal \'(.+?)\' telah ditolak untuk klien (.+)/' => 'Menolak dokumen legal $1 untuk $2',
            
            // Client Document patterns
            '/Dokumen klien \'(.+?)\' telah diunggah untuk (.+)/' => 'Mengupload dokumen $1 untuk $2',
            '/Dokumen \'(.+?)\' \((.+?)\) diunggah untuk klien (.+)/' => 'Mengupload dokumen $1 untuk $3',
        ];
        
        // Coba match dengan patterns
        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $description, $matches)) {
                $summary = $replacement;
                // Replace placeholders dengan matches
                for ($i = 1; $i < count($matches); $i++) {
                    $summary = str_replace('$' . $i, $matches[$i], $summary);
                }
                return $summary;
            }
        }
        
        // Fallback: buat summary dengan kata kerja yang tepat
        return $this->createActionBasedSummary($description);
    }

    /**
     * Create summary berdasarkan kata kerja yang tepat
     */
    private function createActionBasedSummary(string $description): string
    {
        // Deteksi kata kerja berdasarkan action
        if (str_contains($description, 'dibuat') || str_contains($description, 'ditambahkan')) {
            $verb = 'Membuat';
        } elseif (str_contains($description, 'diunggah') || str_contains($description, 'diupload')) {
            $verb = 'Mengupload';
        } elseif (str_contains($description, 'diubah') || str_contains($description, 'diperbarui')) {
            $verb = 'Mengupdate';
        } elseif (str_contains($description, 'dihapus')) {
            $verb = 'Menghapus';
        } elseif (str_contains($description, 'diselesaikan')) {
            $verb = 'Menyelesaikan';
        } elseif (str_contains($description, 'disetujui')) {
            $verb = 'Menyetujui';
        } elseif (str_contains($description, 'ditolak')) {
            $verb = 'Menolak';
        } else {
            $verb = 'Memproses';
        }
        
        // Ambil objek utama dari description
        $object = $this->extractMainObject($description);
        
        return "{$verb} {$object}";
    }

    /**
     * Extract objek utama dari description
     */
    private function extractMainObject(string $description): string
    {
        // Pattern untuk mengambil objek utama
        $patterns = [
            '/Faktur ([^\s]+)/' => 'faktur $1',
            '/Proyek \'(.+?)\'/' => 'proyek $1',
            '/Dokumen \'(.+?)\'/' => 'dokumen $1',
            '/Klien \'(.+?)\'/' => 'klien $1',
            '/Laporan pajak ([^\s]+)/' => 'laporan pajak $1',
            '/Bupot ([^\s]+)/' => 'Bupot $1',
            '/PPh 21/' => 'PPh 21',
            // TAMBAHAN BARU
            '/Dokumen legal \'(.+?)\'/' => 'dokumen legal $1',
            '/Dokumen klien \'(.+?)\'/' => 'dokumen klien $1',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $description, $matches)) {
                $object = $replacement;
                if (isset($matches[1])) {
                    $object = str_replace('$1', $matches[1], $object);
                }
                return $object;
            }
        }
        
        // Fallback
        $words = explode(' ', $description);
        return implode(' ', array_slice($words, 0, 3)) . '...';
    }

    /**
     * Generate task description
     */
    private function generateTaskDescription(): string
    {   
        return "{$this->description}";
    }

    /**
     * Determine task priority
     */
    private function determineTaskPriority(): string
    {
        // High priority actions
        $highPriorityActions = [
            'tax_report_created',
            'document_submitted',
            'document_status_changed',
            // TAMBAHAN BARU
            'legal_document_uploaded', // Legal documents are high priority
        ];

        // Urgent priority actions  
        $urgentPriorityActions = [
            'project_created',
        ];

        // Normal priority actions - TAMBAHAN BARU
        $normalPriorityActions = [
            'client_document_uploaded',
            'document_uploaded',
        ];

        if (in_array($this->action, $urgentPriorityActions)) {
            return 'urgent';
        }

        if (in_array($this->action, $highPriorityActions)) {
            return 'high';
        }

        return 'normal';
    }

    // Static helper methods untuk berbagai jenis aktivitas
    public static function logDocumentUpload(Client $client, string $filename): self
    {
        $userName = auth()->user()?->name ?? 'System';
        
        return self::log([
            'action' => 'document_upload',
            'description' => "Dokumen '{$filename}' diunggah untuk klien {$client->name} oleh {$userName}",
            'actionable_type' => Client::class,
            'actionable_id' => $client->id,
            'client_id' => $client->id,
        ]);
    }

    // TAMBAHAN BARU - Static helper khusus untuk legal documents
    public static function logLegalDocumentUpload(Client $client, string $filename, ClientDocument $clientDocument = null): self
    {
        $userName = auth()->user()?->name ?? 'System';
        
        $data = [
            'action' => 'legal_document_uploaded',
            'description' => "Dokumen legal '{$filename}' telah diunggah untuk klien {$client->name} oleh {$userName}",
            'client_id' => $client->id,
        ];

        if ($clientDocument) {
            $data['actionable_type'] = ClientDocument::class;
            $data['actionable_id'] = $clientDocument->id;
        } else {
            $data['actionable_type'] = Client::class;
            $data['actionable_id'] = $client->id;
        }
        
        return self::log($data);
    }

    // TAMBAHAN BARU - Static helper untuk client document upload
    public static function logClientDocumentUpload(Client $client, string $filename, string $documentType = 'document'): self
    {
        $userName = auth()->user()?->name ?? 'System';
        
        return self::log([
            'action' => 'client_document_uploaded',
            'description' => "Dokumen '{$filename}' ({$documentType}) diunggah untuk klien {$client->name} oleh {$userName}",
            'actionable_type' => Client::class,
            'actionable_id' => $client->id,
            'client_id' => $client->id,
        ]);
    }

    // TAMBAHAN BARU - Helper untuk document status changes
    public static function logDocumentStatusChange(ClientDocument $document, string $oldStatus, string $newStatus): self
    {
        $userName = auth()->user()?->name ?? 'System';
        $filename = $document->original_filename ?? basename($document->file_path);
        $clientName = $document->client->name;
        
        $statusText = match($newStatus) {
            'approved' => 'disetujui',
            'rejected' => 'ditolak',
            'pending_review' => 'sedang direview',
            'uploaded' => 'diunggah ulang',
            default => "diubah status menjadi {$newStatus}"
        };
        
        return self::log([
            'action' => 'document_status_changed',
            'description' => "Dokumen '{$filename}' untuk klien {$clientName} telah {$statusText} oleh {$userName}",
            'actionable_type' => ClientDocument::class,
            'actionable_id' => $document->id,
            'client_id' => $document->client_id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
        ]);
    }

    public static function logTaxReportAction(TaxReport $taxReport, string $action): self
    {
        $client = $taxReport->client;
        $userName = auth()->user()?->name ?? 'System';
        
        return self::log([
            'action' => "tax_report_{$action}",
            'description' => "Laporan pajak {$taxReport->month} untuk {$client->name} telah {$action} oleh {$userName}",
            'actionable_type' => TaxReport::class,
            'actionable_id' => $taxReport->id,
            'client_id' => $client->id,
        ]);
    }

    public static function logInvoiceChange(Invoice $invoice, string $action, array $oldValues = [], array $newValues = []): self
    {
        $client = $invoice->taxReport->client;
        $userName = auth()->user()?->name ?? 'System';
        
        return self::log([
            'action' => "invoice_{$action}",
            'description' => "Faktur {$invoice->invoice_number} ({$invoice->type}) telah {$action} oleh {$userName}",
            'actionable_type' => Invoice::class,
            'actionable_id' => $invoice->id,
            'client_id' => $client->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public static function logProjectAction(Project $project, string $action, array $oldValues = [], array $newValues = []): self
    {
        $userName = auth()->user()?->name ?? 'System';
        
        return self::log([
            'action' => "project_{$action}",
            'description' => "Proyek '{$project->name}' telah {$action} oleh {$userName}",
            'actionable_type' => Project::class,
            'actionable_id' => $project->id,
            'client_id' => $project->client_id,
            'project_id' => $project->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public static function logCriticalAction(string $action, string $description, $model = null): self
    {
        $userName = auth()->user()?->name ?? 'System';
        $descriptionWithUser = $description . " oleh {$userName}";
        
        $data = [
            'action' => $action,
            'description' => $descriptionWithUser,
        ];

        if ($model) {
            $data['actionable_type'] = get_class($model);
            $data['actionable_id'] = $model->id;

            // Auto-detect client and project if available
            if (method_exists($model, 'client') && $model->client) {
                $data['client_id'] = $model->client->id;
            }
            if (method_exists($model, 'project') && $model->project) {
                $data['project_id'] = $model->project->id;
            }
        }

        return self::log($data);
    }

    // Accessor untuk formatting
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getActionIconAttribute(): string
    {
        return match(true) {
            str_contains($this->action, 'upload') => '📤',
            str_contains($this->action, 'created') => '✨',
            str_contains($this->action, 'updated') => '📝',
            str_contains($this->action, 'deleted') => '🗑️',
            str_contains($this->action, 'submitted') => '📋',
            str_contains($this->action, 'approved') => '✅',
            str_contains($this->action, 'rejected') => '❌',
            default => '📊'
        };
    }

    public function getDisplayMessageAttribute(): string
    {
        $icon = $this->action_icon;
        $userName = $this->user->name;
        
        return "{$icon} {$userName} - {$this->description}";
    }
}