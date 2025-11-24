<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Blob;
use Gemini\Enums\ResponseMimeType;
use Gemini\Enums\MimeType;

class InvoiceAIService
{
    /**
     * Process invoice with AI using Laravel Gemini package
     */
    public function processInvoice($file, $clientName = 'unknown-client', $monthName = 'unknown-month')
    {
        try {
            $filePath = $this->resolveFilePath($file);
            
            if (!file_exists($filePath)) {
                throw new \Exception('File tidak ditemukan: ' . $filePath);
            }
            
            $fileContent = file_get_contents($filePath);
            $base64Content = base64_encode($fileContent);
            $mimeType = $this->getMimeType($filePath);
            
            // Pass client name to prompt
            $prompt = $this->getInvoiceExtractionPrompt($clientName);

            $result = Gemini::generativeModel(model: 'gemini-2.0-flash')
                ->withGenerationConfig(
                    generationConfig: new GenerationConfig(
                        responseMimeType: ResponseMimeType::APPLICATION_JSON,
                        temperature: 0.1,
                        maxOutputTokens: 1500, // Increase token limit
                    )
                )
                ->generateContent([
                    $prompt,
                    new Blob(
                        mimeType: MimeType::from($mimeType),
                        data: $base64Content
                    )
                ]);

            $responseData = $this->extractResponseData($result);
            $extractedData = $this->parseAndValidateResponse($responseData, $clientName);
            
            if (!$extractedData) {
                throw new \Exception('Gagal mengekstrak data dari dokumen. Pastikan dokumen adalah faktur pajak yang valid.');
            }
            
            return [
                'success' => true,
                'data' => $extractedData,
                'debug' => false
            ];
            
        } catch (\Exception $e) {
            Log::error('AI Invoice Processing Error: ' . $e->getMessage(), [
                'file' => $this->getFileDebugInfo($file),
                'client' => $clientName ?? 'unknown',
                'month' => $monthName ?? 'unknown'
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => false
            ];
        }
    }
        
    /**
     * Resolve file path from different input types
     */
    private function resolveFilePath($file)
    {
        // Handle array with TemporaryUploadedFile objects
        if (is_array($file)) {
            // Get the first file from the array
            $uploadedFile = reset($file);
            
            if ($uploadedFile instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                // Get the real path from TemporaryUploadedFile
                return $uploadedFile->getRealPath();
            }
            
            throw new \Exception('Invalid file array format');
        }
        
        // Handle TemporaryUploadedFile object directly
        if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            return $file->getRealPath();
        }
        
        // Handle string path (for backward compatibility)
        if (is_string($file)) {
            // If it's already an absolute path
            if (file_exists($file)) {
                return $file;
            }
            
            // Try to construct path from Laravel storage
            $storagePath = storage_path('app/public/' . $file);
            if (file_exists($storagePath)) {
                return $storagePath;
            }
            
            throw new \Exception('File not found at: ' . $file);
        }
        
        throw new \Exception('Unsupported file type: ' . gettype($file));
    }
    
    /**
     * Get debug info about the file parameter
     */
    private function getFileDebugInfo($file)
    {
        if (is_array($file)) {
            $firstFile = reset($file);
            return [
                'type' => 'array',
                'count' => count($file),
                'first_item_type' => gettype($firstFile),
                'first_item_class' => is_object($firstFile) ? get_class($firstFile) : null
            ];
        }
        
        if (is_object($file)) {
            return [
                'type' => 'object',
                'class' => get_class($file)
            ];
        }
        
        return [
            'type' => gettype($file),
            'value' => is_string($file) ? $file : 'non-string'
        ];
    }
    
    /**
     * Debug response structure
     */
    private function debugResponse($result)
    {
        return [
            'result_class' => get_class($result),
            'result_methods' => get_class_methods($result),
            'available_methods' => array_filter(get_class_methods($result), function($method) {
                return in_array($method, ['text', 'json', 'candidates', 'parts']);
            })
        ];
    }
    
    /**
     * Extract response data from Gemini result
     */
    private function extractResponseData($result)
    {
        $responseData = null;
        $method = '';
        $errors = [];
        
        // Try different methods to get response
        $methods = ['json', 'text', 'candidates'];
        
        foreach ($methods as $methodName) {
            try {
                if (method_exists($result, $methodName)) {
                    $responseData = $result->{$methodName}();
                    $method = $methodName;
                    break;
                }
            } catch (\Exception $e) {
                $errors[$methodName] = $e->getMessage();
            }
        }
        
        if ($responseData === null) {
            throw new \Exception('Cannot extract response data. Errors: ' . json_encode($errors));
        }
        
        return $responseData;
    }

    private function calculateActualPpnPercentage($dpp, $ppn)
    {
        if ($dpp == 0) {
            return '11'; // Default
        }
        
        // Calculate percentage
        $percentage = ($ppn / $dpp) * 100;
        
        // Round to 2 decimal places for comparison
        $percentage = round($percentage, 2);
        
        // Determine closest valid percentage (11% or 12%)
        if ($percentage >= 11.5) {
            return '12';
        } else {
            return '11';
        }
    }
    
    /**
     * Get the prompt for invoice extraction
     */
    private function getInvoiceExtractionPrompt($clientName = 'unknown-client')
    {
        return "Analisis dokumen faktur pajak Indonesia ini dan ekstrak informasi berikut dalam format JSON yang tepat:
        {
            \"invoice_number\": \"nomor faktur pajak lengkap\",
            \"invoice_date\": \"tanggal faktur dalam format YYYY-MM-DD\",
            \"pengusaha_kena_pajak\": {
                \"nama\": \"nama dari section Pengusaha Kena Pajak\",
                \"npwp\": \"NPWP dari section Pengusaha Kena Pajak\"
            },
            \"pembeli\": {
                \"nama\": \"nama dari section Pembeli Barang Kena Pajak/Penerima Jasa Kena Pajak\",
                \"npwp\": \"NPWP dari section Pembeli\"
            },
            \"dpp\": \"Dasar Pengenaan Pajak dalam angka saja (tanpa titik, koma, atau simbol)\",
            \"ppn\": \"Jumlah PPN dalam angka saja (tanpa titik, koma, atau simbol)\"
        }

        INSTRUKSI PENTING:
        - Ekstrak SEMUA data: Pengusaha Kena Pajak dan Pembeli
        - Nomor faktur harus lengkap dengan format Indonesia
        - Tanggal format YYYY-MM-DD
        - NPWP format lengkap dengan titik dan strip
        - DPP dan PPN hanya angka murni tanpa pemisah
        - JANGAN tentukan type faktur, sistem akan menentukan berdasarkan nama client
        - Client name saat ini: {$clientName}

        Berikan hanya JSON, tanpa penjelasan tambahan.";
    }
    
    /**
     * Parse and validate AI response
     */

    private function parseAndValidateResponse($responseData, $clientName = 'unknown-client')
    {
        try {
            $data = null;
            
            // Handle different response data types
            if (is_array($responseData)) {
                // If it's an array of objects, take the first one
                if (isset($responseData[0])) {
                    $firstItem = $responseData[0];
                    
                    // If it's an object, convert to array
                    if (is_object($firstItem)) {
                        $data = (array) $firstItem;
                    } elseif (is_array($firstItem)) {
                        $data = $firstItem;
                    } else {
                        throw new \Exception('Invalid first item type: ' . gettype($firstItem));
                    }
                } else {
                    // Response is already the data array
                    $data = $responseData;
                }
            } elseif (is_object($responseData)) {
                // Convert object to array
                $data = json_decode(json_encode($responseData), true);
            } elseif (is_string($responseData)) {
                // Try to decode JSON string
                $data = json_decode($responseData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // If not valid JSON, try to extract JSON from the response
                    $jsonStart = strpos($responseData, '{');
                    $jsonEnd = strrpos($responseData, '}');
                    
                    if ($jsonStart !== false && $jsonEnd !== false) {
                        $jsonString = substr($responseData, $jsonStart, $jsonEnd - $jsonStart + 1);
                        $data = json_decode($jsonString, true);
                    }
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
                    }
                }
            } else {
                throw new \Exception('Unexpected response type: ' . gettype($responseData));
            }
            
            if (!is_array($data)) {
                throw new \Exception('Response is not an array after conversion. Type: ' . gettype($data));
            }
            
            // Log the processed data for debugging
            Log::info('Processed data for validation: ' . json_encode($data));
            
            // Extract pengusaha_kena_pajak and pembeli data
            $pengusahaData = $data['pengusaha_kena_pajak'] ?? [];
            $pembeliData = $data['pembeli'] ?? [];
            
            // Handle if pengusaha/pembeli is an object instead of array
            if (is_object($pengusahaData)) {
                $pengusahaData = (array) $pengusahaData;
            }
            if (is_object($pembeliData)) {
                $pembeliData = (array) $pembeliData;
            }
            
            // Normalize names for comparison (remove extra spaces, convert to lowercase)
            $normalizedClientName = $this->normalizeName($clientName);
            $normalizedPembeliName = $this->normalizeName($pembeliData['nama'] ?? '');
            
            // Determine invoice type by comparing client name with pembeli name
            $isFakturMasukan = false;
            $matchScore = 0;
            
            if (!empty($normalizedPembeliName) && !empty($normalizedClientName)) {
                // Calculate similarity score
                $matchScore = $this->calculateNameSimilarity($normalizedClientName, $normalizedPembeliName);
                
                // If similarity is high enough (> 70%), consider it a match
                $isFakturMasukan = $matchScore > 0.70;
            }
            
            // Determine company name and NPWP based on invoice type
            if ($isFakturMasukan) {
                // Faktur Masukan: Client adalah pembeli, maka catat supplier (Pengusaha Kena Pajak)
                $companyName = $this->cleanString($pengusahaData['nama'] ?? '');
                $npwp = $this->cleanString($pengusahaData['npwp'] ?? '');
                $invoiceType = 'Faktur Masuk';
                
                Log::info('Detected as Faktur Masukan', [
                    'client_name' => $clientName,
                    'pembeli_name' => $pembeliData['nama'] ?? '',
                    'supplier_name' => $companyName,
                    'match_score' => $matchScore,
                    'match_reason' => 'Client name matches Pembeli (similarity: ' . round($matchScore * 100, 2) . '%)'
                ]);
            } else {
                // Faktur Keluaran: Client adalah penjual, maka catat customer (Pembeli)
                $companyName = $this->cleanString($pembeliData['nama'] ?? '');
                $npwp = $this->cleanString($pembeliData['npwp'] ?? '');
                $invoiceType = 'Faktur Keluaran';
                
                Log::info('Detected as Faktur Keluaran', [
                    'client_name' => $clientName,
                    'pembeli_name' => $pembeliData['nama'] ?? '',
                    'customer_name' => $companyName,
                    'match_score' => $matchScore,
                    'match_reason' => 'Client name does not match Pembeli (similarity: ' . round($matchScore * 100, 2) . '%)'
                ]);
            }
            
            // Clean DPP and PPN values
            $dppCleaned = $this->cleanNumber($data['dpp'] ?? '0');
            $ppnCleaned = $this->cleanNumber($data['ppn'] ?? '0');
            
            // Calculate actual PPN percentage from the values
            $actualPpnPercentage = $this->calculateActualPpnPercentage(
                (float)$dppCleaned, 
                (float)$ppnCleaned
            );
            
            // Build extracted data
            $extractedData = [
                'invoice_number' => $this->cleanString($data['invoice_number'] ?? ''),
                'invoice_date' => $this->validateDate($data['invoice_date'] ?? ''),
                'company_name' => $companyName,
                'npwp' => $npwp,
                'type' => $invoiceType,
                'dpp' => $dppCleaned,
                'ppn_percentage' => $actualPpnPercentage,
                'ppn' => $ppnCleaned
            ];

            // Log the PPN calculation for audit trail
            Log::info('PPN Percentage Calculation', [
                'dpp' => $dppCleaned,
                'ppn' => $ppnCleaned,
                'calculated_percentage' => $actualPpnPercentage,
                'calculation' => sprintf('%.2f%%', ((float)$ppnCleaned / (float)$dppCleaned) * 100),
                'invoice_type' => $invoiceType,
                'invoice_number' => $extractedData['invoice_number'],
                'is_faktur_masukan' => $isFakturMasukan,
                'pengusaha_kena_pajak' => $pengusahaData['nama'] ?? 'N/A',
                'pembeli' => $pembeliData['nama'] ?? 'N/A',
                'client_name_used' => $clientName
            ]);

            // Basic validation
            if (empty($extractedData['invoice_number'])) {
                throw new \Exception("Missing required field: invoice_number");
            }
            
            if (empty($extractedData['company_name'])) {
                throw new \Exception("Missing required field: company_name. Please check if the document contains clear supplier/customer information.");
            }
            
            // Additional validation for DPP and PPN
            if ($dppCleaned == 0 || $ppnCleaned == 0) {
                Log::warning('DPP or PPN is zero', [
                    'dpp' => $dppCleaned,
                    'ppn' => $ppnCleaned,
                    'invoice_number' => $extractedData['invoice_number'],
                    'invoice_type' => $invoiceType
                ]);
            }
            
            // Validate that PPN calculation is reasonable (within acceptable range)
            if ($dppCleaned > 0) {
                $calculatedPercentage = ((float)$ppnCleaned / (float)$dppCleaned) * 100;
                if ($calculatedPercentage < 10 || $calculatedPercentage > 13) {
                    Log::warning('PPN percentage outside normal range', [
                        'calculated_percentage' => $calculatedPercentage,
                        'dpp' => $dppCleaned,
                        'ppn' => $ppnCleaned,
                        'invoice_number' => $extractedData['invoice_number'],
                        'invoice_type' => $invoiceType
                    ]);
                }
            }

            return $extractedData;

        } catch (\Exception $e) {
            Log::error('Failed to parse AI response: ' . $e->getMessage(), [
                'response_data_type' => gettype($responseData),
                'response_data_debug' => is_object($responseData) ? get_class($responseData) : (is_array($responseData) ? 'array[' . count($responseData) . ']' : substr((string)$responseData, 0, 200)),
                'client_name' => $clientName,
                'error_trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Normalize name for comparison
     * Remove extra spaces, convert to lowercase, remove special characters
     * 
     * @param string $name
     * @return string Normalized name
     */
    private function normalizeName(string $name): string
    {
        // Convert to lowercase
        $normalized = strtolower(trim($name));
        
        // Remove multiple spaces
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        // Remove common company suffixes for better matching
        $suffixes = [' pt', ' cv', ' ud', ' fa', ' pd', ' persero', ' tbk', ','];
        foreach ($suffixes as $suffix) {
            $normalized = str_replace($suffix, '', $normalized);
        }
        
        // Remove dots and commas
        $normalized = str_replace(['.', ','], '', $normalized);
        
        return trim($normalized);
    }

    /**
     * Calculate name similarity between two names
     * Uses multiple algorithms for better matching
     * 
     * @param string $name1 First name (normalized)
     * @param string $name2 Second name (normalized)
     * @return float Similarity score (0-1)
     */
    private function calculateNameSimilarity(string $name1, string $name2): float
    {
        if (empty($name1) || empty($name2)) {
            return 0;
        }
        
        // Method 1: Exact substring match
        if (strpos($name1, $name2) !== false || strpos($name2, $name1) !== false) {
            return 1.0;
        }
        
        // Method 2: Similar text (Levenshtein distance based)
        $similarPercent = 0;
        similar_text($name1, $name2, $similarPercent);
        $similarity1 = $similarPercent / 100;
        
        // Method 3: Levenshtein distance (normalized)
        $maxLength = max(strlen($name1), strlen($name2));
        $levenshtein = levenshtein($name1, $name2);
        $similarity2 = 1 - ($levenshtein / $maxLength);
        
        // Method 4: Word-by-word matching
        $words1 = explode(' ', $name1);
        $words2 = explode(' ', $name2);
        $matchingWords = count(array_intersect($words1, $words2));
        $totalWords = max(count($words1), count($words2));
        $similarity3 = $totalWords > 0 ? $matchingWords / $totalWords : 0;
        
        // Return weighted average of all methods
        // Give more weight to exact substring match and word matching
        $finalSimilarity = ($similarity1 * 0.3) + ($similarity2 * 0.3) + ($similarity3 * 0.4);
        
        Log::debug('Name similarity calculation', [
            'name1' => $name1,
            'name2' => $name2,
            'similar_text' => $similarity1,
            'levenshtein' => $similarity2,
            'word_match' => $similarity3,
            'final_score' => $finalSimilarity
        ]);
        
        return $finalSimilarity;
    }
    
    /**
     * Get MIME type of the uploaded file
     */
    private function getMimeType($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
    
    /**
     * Helper methods for data validation and cleaning
     */
    private function cleanString($str)
    {
        return trim((string) $str);
    }

    private function cleanNumber($num)
    {
        return preg_replace('/[^0-9]/', '', (string) $num);
    }

    private function validateDate($dateStr)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $dateStr);
        if ($date && $date->format('Y-m-d') === $dateStr) {
            return $dateStr;
        }
        
        // Try to parse other formats
        $date = strtotime($dateStr);
        if ($date !== false) {
            return date('Y-m-d', $date);
        }
        
        // Return today if invalid
        return date('Y-m-d');
    }

    private function validateInvoiceType($type)
    {
        $validTypes = ['Faktur Keluaran', 'Faktur Masuk'];
        return in_array($type, $validTypes) ? $type : 'Faktur Keluaran';
    }

    private function validatePpnPercentage($percentage)
    {
        $validPercentages = ['11', '12'];
        return in_array((string) $percentage, $validPercentages) ? (string) $percentage : '11';
    }
    
    /**
     * Format output for display
     */
    public function formatOutput($result)
    {
        if (!$result['success']) {
            return '❌ **Error:** ' . $result['error'];
        }
        
        // If debug mode
        if ($result['debug']) {
            $output = "🔍 **Debug Info:**\n\n";
            $output .= "**Available methods:** " . implode(', ', $result['debug_info']['available_methods']) . "\n";
            $output .= "**Response type:** " . $result['response_type'] . "\n";
            $output .= "**Response data:** " . $this->safeStringify($result['response_data']) . "\n\n";
            $output .= "Debug mode aktif. Set APP_DEBUG=false untuk mode produksi.";
            return $output;
        }
        
        // Normal success output
        $data = $result['data'];
        $output = "✅ **Ekstraksi Data Berhasil**\n\n";
        $output .= "**Data yang ditemukan:**\n";
        $output .= "• Nomor Faktur: {$data['invoice_number']}\n";
        $output .= "• Tanggal Faktur: {$data['invoice_date']}\n";
        $output .= "• Nama Perusahaan: {$data['company_name']}\n";
        $output .= "• NPWP: {$data['npwp']}\n";
        $output .= "• Jenis Faktur: {$data['type']}\n";
        $output .= "• DPP: Rp " . number_format((int)$data['dpp'], 0, ',', '.') . "\n";
        $output .= "• Tarif PPN: {$data['ppn_percentage']}%\n";
        $output .= "• PPN: Rp " . number_format((int)$data['ppn'], 0, ',', '.') . "\n\n";
        $output .= "📋 Klik tombol **'Terapkan Data AI ke Form'** untuk mengisi form secara otomatis.";
        
        return $output;
    }
    
    /**
     * Safely convert data to string for display
     */
    private function safeStringify($data)
    {
        if (is_string($data)) {
            return substr($data, 0, 500) . (strlen($data) > 500 ? '...' : '');
        }
        
        if (is_array($data)) {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        
        return print_r($data, true);
    }
}