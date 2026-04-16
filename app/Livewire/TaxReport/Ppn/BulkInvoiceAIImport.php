<?php

namespace App\Livewire\TaxReport\Ppn;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TaxReport;
use App\Models\Invoice;
use App\Services\InvoiceAIService;
use App\Services\TaxCalculationService;
use App\Services\FileManagementService;
use App\Services\ClientTypeService;
use Illuminate\Support\Str;

class BulkInvoiceAIImport extends Component
{
    use WithFileUploads;

    public int $taxReportId;

    /** @var array<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploadedFiles = [];

    /**
     * Each item:
     * [
     *   'filename'   => string,
     *   'temp_path'  => string,        // getRealPath() at upload time
     *   'status'     => idle|processing|completed|error|saved|skipped
     *   'error'      => string|null,
     *   'selected'   => bool,
     *   'form'       => [...invoice fields...]
     * ]
     */
    public array $items = [];

    public string $step = 'upload'; // upload | review

    public bool $isProcessingAll = false;

    public ?string $flashSuccess = null;

    public array $flashErrors = [];

    public function mount(int $taxReportId): void
    {
        $this->taxReportId = $taxReportId;
    }

    public function rules(): array
    {
        return [
            'uploadedFiles.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ];
    }

    /**
     * Triggered when uploadedFiles changes.
     * Builds the items array capping at 10 files.
     */
    public function updatedUploadedFiles(): void
    {
        if (count($this->uploadedFiles) > 10) {
            $this->uploadedFiles = array_slice($this->uploadedFiles, 0, 10);
            $this->addError('uploadedFiles', 'Maksimal 10 file sekaligus.');
        }

        $existing = $this->items;
        $this->items = [];

        foreach ($this->uploadedFiles as $index => $file) {
            // Preserve existing item data if file name matches
            $prev = collect($existing)->firstWhere('filename', $file->getClientOriginalName());

            $this->items[] = [
                'filename'  => $file->getClientOriginalName(),
                'temp_path' => $file->getRealPath(),
                'status'    => $prev['status'] ?? 'idle',
                'error'     => $prev['error'] ?? null,
                'selected'  => $prev['selected'] ?? true,
                'form'      => $prev['form'] ?? $this->defaultForm(),
            ];
        }
    }

    private function defaultForm(): array
    {
        return [
            'invoice_number'    => '',
            'invoice_date'      => now()->format('Y-m-d'),
            'company_name'      => '',
            'npwp'              => '',
            'type'              => 'Faktur Keluaran',
            'client_type'       => '',
            'has_ppn'           => true,
            'ppn_percentage'    => '11',
            'dpp'               => 0,
            'dpp_nilai_lainnya' => 0,
            'ppn'               => 0,
            'is_business_related' => true,
            'notes'             => '',
        ];
    }

    /**
     * Process all idle items sequentially.
     */
    public function processAll(): void
    {
        @set_time_limit(300);

        $this->isProcessingAll = true;

        foreach ($this->items as $index => $item) {
            if (in_array($item['status'], ['idle', 'error'])) {
                $this->processItem($index);
            }
        }

        $this->isProcessingAll = false;
        $this->step = 'review';
    }

    /**
     * Process a single item by index.
     */
    public function processItem(int $index): void
    {
        if (!isset($this->uploadedFiles[$index])) {
            $this->items[$index]['status'] = 'error';
            $this->items[$index]['error']  = 'File tidak ditemukan di server.';
            return;
        }

        $this->items[$index]['status'] = 'processing';

        try {
            $taxReport  = TaxReport::with('client')->find($this->taxReportId);
            $clientName = $taxReport?->client ? Str::slug($taxReport->client->name) : 'unknown-client';
            $monthName  = $taxReport ? FileManagementService::convertToIndonesianMonth($taxReport->month) : 'unknown-month';

            $file     = $this->uploadedFiles[$index];
            $fullPath = $file->getRealPath();

            if (!file_exists($fullPath)) {
                throw new \Exception('File tidak ditemukan di temporary storage.');
            }

            $aiService = new InvoiceAIService();

            $result = method_exists($aiService, 'processInvoiceFromPath')
                ? $aiService->processInvoiceFromPath($fullPath, $clientName, $monthName)
                : $aiService->processInvoice($file, $clientName, $monthName);

            if ($result['success'] && !($result['debug'] ?? false)) {
                $this->items[$index]['status'] = 'completed';
                $this->applyAIDataToItem($index, $result['data']);
            } else {
                $this->items[$index]['status'] = 'error';
                $this->items[$index]['error']  = $result['error'] ?? 'AI tidak berhasil mengekstrak data.';
            }
        } catch (\Exception $e) {
            $this->items[$index]['status'] = 'error';
            $this->items[$index]['error']  = $e->getMessage();
        }
    }

    private function applyAIDataToItem(int $index, array $data): void
    {
        $form = $this->items[$index]['form'];

        // Basic fields
        foreach (['invoice_number', 'invoice_date', 'company_name', 'npwp', 'type', 'ppn_percentage'] as $field) {
            if (!empty($data[$field])) {
                $form[$field] = $data[$field];
            }
        }

        // Detect client type from invoice number
        if (!empty($form['invoice_number']) && strlen($form['invoice_number']) >= 2) {
            $ct = ClientTypeService::getClientTypeFromInvoiceNumber($form['invoice_number']);
            $form['client_type'] = $ct['type'];
            $form['has_ppn']     = $ct['has_ppn'];
        }

        // DPP / PPN calculations
        $ppnPct = $data['ppn_percentage'] ?? '11';
        $dpp    = floatval($data['dpp'] ?? 0);

        if ($ppnPct === '12') {
            $form['dpp_nilai_lainnya'] = $dpp;
            $calc = TaxCalculationService::calculateFromDppNilaiLainnya($dpp);
            $form['dpp'] = $calc['dpp'];
            $form['ppn'] = $calc['ppn'];
        } else {
            $form['dpp']               = $dpp;
            $form['dpp_nilai_lainnya'] = 0;
            $calc = TaxCalculationService::calculatePPNFromDpp($dpp);
            $form['ppn'] = $calc['ppn'];
        }

        $this->items[$index]['form'] = $form;
    }

    // ─── Inline editing helpers ───────────────────────────────────────────────

    public function updateField(int $index, string $field, mixed $value): void
    {
        // Cast boolean-like fields that come as strings from HTML selects
        if ($field === 'is_business_related' || $field === 'has_ppn') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
        }

        $this->items[$index]['form'][$field] = $value;

        // Recalculate when financial fields change
        $form   = $this->items[$index]['form'];
        $ppnPct = $form['ppn_percentage'] ?? '11';

        if ($field === 'invoice_number' && strlen((string) $value) >= 2) {
            $ct = ClientTypeService::getClientTypeFromInvoiceNumber((string) $value);
            $this->items[$index]['form']['client_type'] = $ct['type'];
            $this->items[$index]['form']['has_ppn']     = $ct['has_ppn'];
        }

        if ($field === 'ppn_percentage') {
            if ($value === '11') {
                $this->items[$index]['form']['dpp_nilai_lainnya'] = 0;
                $calc = TaxCalculationService::calculatePPNFromDpp(floatval($form['dpp']));
                $this->items[$index]['form']['ppn'] = $calc['ppn'];
            } else {
                $this->items[$index]['form']['dpp'] = 0;
                $this->items[$index]['form']['ppn'] = 0;
            }
        }

        if ($field === 'dpp' && $ppnPct === '11') {
            $calc = TaxCalculationService::calculatePPNFromDpp(floatval($value));
            $this->items[$index]['form']['ppn'] = $calc['ppn'];
        }

        if ($field === 'dpp_nilai_lainnya' && $ppnPct === '12') {
            $calc = TaxCalculationService::calculateFromDppNilaiLainnya(floatval($value));
            $this->items[$index]['form']['dpp'] = $calc['dpp'];
            $this->items[$index]['form']['ppn'] = $calc['ppn'];
        }
    }

    public function toggleSelection(int $index): void
    {
        $this->items[$index]['selected'] = !($this->items[$index]['selected'] ?? true);
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        array_splice($this->uploadedFiles, $index, 1);
        $this->items         = array_values($this->items);
        $this->uploadedFiles = array_values($this->uploadedFiles);
    }

    public function goBackToUpload(): void
    {
        $this->step = 'upload';
    }

    // ─── Save ─────────────────────────────────────────────────────────────────

    public function saveAll(): void
    {
        $taxReport   = TaxReport::with('client')->findOrFail($this->taxReportId);
        $savedCount  = 0;
        $this->flashErrors = [];

        foreach ($this->items as $index => $item) {
            if (!($item['selected'] ?? true)) {
                continue;
            }
            if ($item['status'] !== 'completed') {
                continue;
            }

            try {
                $this->saveItem($index, $taxReport);
                $this->items[$index]['status'] = 'saved';
                $savedCount++;
            } catch (\Exception $e) {
                $this->flashErrors[] = [
                    'index'    => $index + 1,
                    'filename' => $item['filename'],
                    'number'   => $item['form']['invoice_number'] ?? '-',
                    'message'  => $this->friendlyErrorMessage($e),
                ];
            }
        }

        if ($savedCount > 0) {
            $this->flashSuccess = "{$savedCount} faktur berhasil disimpan ke laporan pajak.";
            // Notify parent table to refresh
            $this->dispatch('faktur-bulk-saved');
        }
    }

    private function saveItem(int $index, TaxReport $taxReport): void
    {
        if (!isset($this->uploadedFiles[$index])) {
            throw new \Exception('File upload tidak ditemukan di server. Coba upload ulang.');
        }

        $form         = $this->items[$index]['form'];
        $file         = $this->uploadedFiles[$index];
        $invoiceNumber = trim($form['invoice_number'] ?? '');

        // ── Pre-checks (friendly, before touching storage) ─────────────────
        if ($invoiceNumber === '') {
            throw new \Exception('Nomor faktur tidak boleh kosong.');
        }

        if (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
            throw new \Exception("Nomor faktur '{$invoiceNumber}' sudah terdaftar di sistem. Periksa apakah faktur ini sudah diinput sebelumnya.");
        }

        // ── Store file to designated folder ────────────────────────────────
        $invoiceType = $form['type'] ?? 'Faktur Keluaran';
        $directory   = FileManagementService::generateInvoiceDirectoryPath($taxReport, $invoiceType);
        $fileName    = FileManagementService::generateInvoiceFileName(
            $invoiceType,
            $invoiceNumber,
            $file->getClientOriginalName()
        );

        $filePath = $file->storeAs($directory, $fileName, 'public');

        if (!$filePath) {
            throw new \Exception('Gagal menyimpan file ke storage. Pastikan direktori storage dapat ditulis.');
        }

        // ── Save DB record; rollback file if it fails ───────────────────────
        try {
            $invoice = new Invoice();
            $invoice->tax_report_id       = $this->taxReportId;
            $invoice->invoice_number      = $invoiceNumber;
            $invoice->invoice_date        = $form['invoice_date'];
            $invoice->company_name        = $form['company_name'];
            $invoice->npwp                = $form['npwp'];
            $invoice->type                = $invoiceType;
            $invoice->client_type         = $form['client_type'] ?? null;
            $invoice->has_ppn             = $form['has_ppn'] ?? true;
            $invoice->ppn_percentage      = $form['ppn_percentage'] ?? '11';
            $invoice->dpp                 = floatval($form['dpp'] ?? 0);
            $invoice->dpp_nilai_lainnya   = floatval($form['dpp_nilai_lainnya'] ?? 0);
            $invoice->ppn                 = floatval($form['ppn'] ?? 0);
            $invoice->is_business_related = $form['is_business_related'] ?? true;
            $invoice->notes               = $form['notes'] ?? null;
            $invoice->file_path           = $filePath;
            $invoice->created_by          = auth()->id();
            $invoice->save();
        } catch (\Exception $e) {
            // Remove the just-uploaded file so we don't leave orphaned files
            \Storage::disk('public')->delete($filePath);
            throw $e;
        }
    }

    // ─── Error translation ────────────────────────────────────────────────────

    private function friendlyErrorMessage(\Exception $e): string
    {
        $msg = $e->getMessage();

        // Duplicate invoice number (unique constraint)
        if ($e instanceof \Illuminate\Database\QueryException || str_contains($msg, 'SQLSTATE')) {
            if (str_contains($msg, '1062') || str_contains($msg, 'Duplicate entry')) {
                // Extract the duplicate value from the error
                if (preg_match("/Duplicate entry '([^']+)'/", $msg, $matches)) {
                    return "Nomor faktur '{$matches[1]}' sudah terdaftar di sistem. Hapus atau ganti nomor faktur ini sebelum menyimpan.";
                }
                return 'Nomor faktur sudah terdaftar di sistem. Pastikan nomor faktur tidak duplikat.';
            }

            // Foreign key / relation not found
            if (str_contains($msg, '1452') || str_contains($msg, 'foreign key constraint')) {
                return 'Data referensi tidak valid. Pastikan laporan pajak masih aktif.';
            }

            // Column too long / data too large
            if (str_contains($msg, '1406') || str_contains($msg, 'Data too long')) {
                return 'Salah satu data terlalu panjang. Periksa kembali nomor faktur atau nama perusahaan.';
            }

            // Generic DB error — hide raw SQL
            return 'Terjadi kesalahan database saat menyimpan. Coba lagi atau hubungi admin.';
        }

        // File not found / storage error
        if (str_contains($msg, 'File upload tidak ditemukan') || str_contains($msg, 'storeAs')) {
            return 'File tidak dapat disimpan ke server. Coba upload ulang file ini.';
        }

        // Return original message for non-DB exceptions (already user-friendly)
        return $msg;
    }

    // ─── Computed helpers ─────────────────────────────────────────────────────

    public function getCompletedCount(): int
    {
        return collect($this->items)->where('status', 'completed')->count();
    }

    public function getErrorCount(): int
    {
        return collect($this->items)->where('status', 'error')->count();
    }

    public function getSavedCount(): int
    {
        return collect($this->items)->where('status', 'saved')->count();
    }

    public function getSelectedCompletedCount(): int
    {
        return collect($this->items)
            ->filter(fn ($i) => ($i['selected'] ?? true) && $i['status'] === 'completed')
            ->count();
    }

    public function render()
    {
        return view('livewire.tax-report.ppn.bulk-invoice-ai-import');
    }
}
