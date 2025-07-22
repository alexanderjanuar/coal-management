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
            // Handle different file input types
            $filePath = $this->resolveFilePath($file);
            
            // Check if file exists
            if (!file_exists($filePath)) {
                throw new \Exception('File tidak ditemukan: ' . $filePath);
            }
            
            // Read file and encode to base64
            $fileContent = file_get_contents($filePath);
            $base64Content = base64_encode($fileContent);
            
            // Determine MIME type
            $mimeType = $this->getMimeType($filePath);
            
            // Prepare the prompt for Indonesian tax invoice extraction
            $prompt = $this->getInvoiceExtractionPrompt();

            // Use Laravel Gemini package with debugging
            $result = Gemini::generativeModel(model: 'gemini-2.0-flash-exp')
                ->withGenerationConfig(
                    generationConfig: new GenerationConfig(
                        responseMimeType: ResponseMimeType::APPLICATION_JSON,
                        temperature: 0.1,
                        maxOutputTokens: 1000,
                    )
                )
                ->generateContent([
                    $prompt,
                    new Blob(
                        mimeType: MimeType::from($mimeType),
                        data: $base64Content
                    )
                ]);

            // Debug the response structure
            $debugInfo = $this->debugResponse($result);
            
            // Try to get response data
            $responseData = $this->extractResponseData($result);
            
            // Parse and validate the response (remove debug mode check)
            $extractedData = $this->parseAndValidateResponse($responseData);
            
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
    
    /**
     * Get the prompt for invoice extraction
     */
    private function getInvoiceExtractionPrompt()
    {
        return "Analisis dokumen faktur pajak Indonesia ini dan ekstrak informasi berikut dalam format JSON yang tepat:
        {
            \"invoice_number\": \"nomor faktur pajak lengkap\",
            \"invoice_date\": \"tanggal faktur dalam format YYYY-MM-DD\",
            \"type\": \"Faktur Keluaran atau Faktur Masuk\",
            \"company_name\": \"nama Pembeli Barang Kena Pajak/Penerima Jasa Kena Pajak lengkap jika faktur keluaran\",
            \"npwp\": \"nomor NPWP lengkap\",
            \"dpp\": \"nilai DPP dalam angka saja (tanpa titik, koma, atau simbol)\",
            \"ppn_percentage\": \"11 atau 12\",
            \"ppn\": \"nilai PPN dalam angka saja (tanpa titik, koma, atau simbol)\"
        }

        Instruksi penting:
        - Nomor faktur harus dalam format Indonesia (contoh: 010.000-25.12345678)
        - Tanggal harus format YYYY-MM-DD
        - NPWP harus format Indonesia (contoh: 01.234.567.8-901.000)
        - DPP dan PPN hanya angka, tanpa pemisah ribuan
        - Type hanya \"Faktur Keluaran\" atau \"Faktur Masuk\"
        - PPN percentage hanya \"11\" atau \"12\"
        - Jika ada field yang tidak ditemukan, gunakan nilai default yang masuk akal

        Berikan hanya JSON, tanpa penjelasan tambahan.";
    }
    
    /**
     * Parse and validate AI response
     */
    private function parseAndValidateResponse($responseData)
    {
        try {
            $data = null;
            
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
                $data = (array) $responseData;
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
            
            // Validate and clean the data
            $extractedData = [
                'invoice_number' => $this->cleanString($data['invoice_number'] ?? ''),
                'invoice_date' => $this->validateDate($data['invoice_date'] ?? ''),
                'company_name' => $this->cleanString($data['company_name'] ?? ''),
                'npwp' => $this->cleanString($data['npwp'] ?? ''),
                'type' => $this->validateInvoiceType($data['type'] ?? 'Faktur Keluaran'),
                'dpp' => $this->cleanNumber($data['dpp'] ?? '0'),
                'ppn_percentage' => $this->validatePpnPercentage($data['ppn_percentage'] ?? '11'),
                'ppn' => $this->cleanNumber($data['ppn'] ?? '0')
            ];

            // Basic validation
            if (empty($extractedData['invoice_number']) || empty($extractedData['company_name'])) {
                throw new \Exception("Missing required fields: invoice_number ('{$extractedData['invoice_number']}') or company_name ('{$extractedData['company_name']}')");
            }

            return $extractedData;

        } catch (\Exception $e) {
            Log::error('Failed to parse AI response: ' . $e->getMessage(), [
                'response_data_type' => gettype($responseData),
                'response_data_debug' => is_object($responseData) ? get_class($responseData) : (is_array($responseData) ? 'array[' . count($responseData) . ']' : $responseData),
                'error_trace' => $e->getTraceAsString()
            ]);
            return null;
        }
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