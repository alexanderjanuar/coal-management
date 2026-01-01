<?php

namespace App\Services;

use Illuminate\Support\Str;
use Carbon\Carbon;

class FileManagementService
{
    /**
     * Generate dynamic directory path for invoice file uploads
     */
    public static function generateInvoiceDirectoryPath($taxReport, string $invoiceType = null): string
    {
        // Default values
        $clientName = 'unknown-client';
        $monthName = 'unknown-month';
        $currentYear = date('Y');
        
        if ($taxReport && $taxReport->client) {
            // Clean client name for folder structure
            $clientName = Str::slug($taxReport->client->name);
            
            // Convert month from tax report to Indonesian month name
            $monthName = self::convertToIndonesianMonth($taxReport->month);
            
            // Extract year from tax report if available
            if ($taxReport->month && preg_match('/(\d{4})/', $taxReport->month, $matches)) {
                $currentYear = $matches[1];
            }
        }
        
        // Determine the transaction type folder (Penjualan/Pembelian)
        $transactionFolder = self::getTransactionFolder($invoiceType);
        
        // Determine the invoice type folder (Faktur Keluaran/Faktur Masukan)
        $invoiceFolder = self::getInvoiceFolder($invoiceType);
        
        return "clients/{$clientName}/Kegiatan Perusahaan/{$currentYear}/SPT MASA/{$monthName}/TRANSAKSI/{$transactionFolder}/{$invoiceFolder}";
    }

    /**
     * Get transaction folder based on invoice type
     */
    private static function getTransactionFolder(?string $invoiceType): string
    {
        if (!$invoiceType) {
            return 'Unknown';
        }
        
        return $invoiceType === 'Faktur Keluaran' ? 'Penjualan' : 'Pembelian';
    }

    /**
     * Get invoice folder based on invoice type
     */
    private static function getInvoiceFolder(?string $invoiceType): string
    {
        return $invoiceType ?? 'Unknown';
    }

    /**
     * Generate filename with invoice type and number
     */
    public static function generateInvoiceFileName(string $invoiceType, string $invoiceNumber, string $originalFileName): string
    {
        // Clean invoice number for filename (remove special characters)
        $cleanInvoiceNumber = Str::slug($invoiceNumber);
        
        // Get file extension
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        
        return "{$invoiceType}-{$cleanInvoiceNumber}.{$extension}";
    }

    /**
     * Generate bukti setor filename
     */
    public static function generateBuktiSetorFileName(string $invoiceType, string $invoiceNumber, string $originalFileName): string
    {
        $cleanInvoiceNumber = Str::slug($invoiceNumber);
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        
        return "Bukti-Setor-{$invoiceType}-{$cleanInvoiceNumber}.{$extension}";
    }

    /**
     * Generate surat penagihan filename
     */
    public static function generateSuratPenagihanFileName(string $invoiceType, string $invoiceNumber, string $originalFileName): string
    {
        $cleanInvoiceNumber = Str::slug($invoiceNumber);
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        
        return "Surat-Penagihan-{$invoiceType}-{$cleanInvoiceNumber}.{$extension}";
    }

    /**
     * Generate full bukti setor directory path
     */
    public static function generateBuktiSetorDirectoryPath($taxReport, string $invoiceType = null): string
    {
        $basePath = self::generateInvoiceDirectoryPath($taxReport, $invoiceType);
        return $basePath . '/Bukti Setor';
    }

    /**
     * Generate full surat penagihan directory path
     * Surat Penagihan is at the same level as Faktur Keluaran/Faktur Masukan
     */
    public static function generateSuratPenagihanDirectoryPath($taxReport, string $invoiceType = null): string
    {
        // Default values
        $clientName = 'unknown-client';
        $monthName = 'unknown-month';
        $currentYear = date('Y');
        
        if ($taxReport && $taxReport->client) {
            // Clean client name for folder structure
            $clientName = Str::slug($taxReport->client->name);
            
            // Convert month from tax report to Indonesian month name
            $monthName = self::convertToIndonesianMonth($taxReport->month);
            
            // Extract year from tax report if available
            if ($taxReport->month && preg_match('/(\d{4})/', $taxReport->month, $matches)) {
                $currentYear = $matches[1];
            }
        }
        
        // Determine the transaction type folder (Penjualan/Pembelian)
        $transactionFolder = self::getTransactionFolder($invoiceType);
        
        // Surat Penagihan is at the same level as Faktur Keluaran/Faktur Masukan
        return "clients/{$clientName}/Kegiatan Perusahaan/{$currentYear}/SPT MASA/{$monthName}/{$transactionFolder}/Surat Penagihan";
    }

    /**
     * Convert month format to Indonesian month names
     */
    public static function convertToIndonesianMonth(string $month): string
    {
        // Handle different month formats
        $monthNames = [
            '01' => 'Januari', '1' => 'Januari', 'january' => 'Januari', 'jan' => 'Januari',
            '02' => 'Februari', '2' => 'Februari', 'february' => 'Februari', 'feb' => 'Februari',
            '03' => 'Maret', '3' => 'Maret', 'march' => 'Maret', 'mar' => 'Maret',
            '04' => 'April', '4' => 'April', 'april' => 'April', 'apr' => 'April',
            '05' => 'Mei', '5' => 'Mei', 'may' => 'Mei',
            '06' => 'Juni', '6' => 'Juni', 'june' => 'Juni', 'jun' => 'Juni',
            '07' => 'Juli', '7' => 'Juli', 'july' => 'Juli', 'jul' => 'Juli',
            '08' => 'Agustus', '8' => 'Agustus', 'august' => 'Agustus', 'aug' => 'Agustus',
            '09' => 'September', '9' => 'September', 'september' => 'September', 'sep' => 'September',
            '10' => 'Oktober', 'october' => 'Oktober', 'oct' => 'Oktober',
            '11' => 'November', 'november' => 'November', 'nov' => 'November',
            '12' => 'Desember', 'december' => 'Desember', 'dec' => 'Desember',
        ];

        $cleanMonth = strtolower(trim($month));
        
        // If it's a date format like "2025-01", extract the month part
        if (preg_match('/\d{4}-(\d{1,2})/', $month, $matches)) {
            $cleanMonth = $matches[1];
        }
        
        return $monthNames[$cleanMonth] ?? Str::title($cleanMonth);
    }

    /**
     * Generate directory path for PPh import/export files
     */
    public static function generatePphImportDirectoryPath($taxReport, string $jenisPajak = 'PPH 21'): string
    {
        // Default values
        $clientName = 'unknown-client';
        $monthName = 'unknown-month';
        $currentYear = date('Y');
        
        if ($taxReport && $taxReport->client) {
            // Clean client name for folder structure
            $clientName = Str::slug($taxReport->client->name);
            
            // Convert month from tax report to Indonesian month name
            $monthName = self::convertToIndonesianMonth($taxReport->month);
            
            // Extract year from tax report if available
            if ($taxReport->month && preg_match('/(\d{4})/', $taxReport->month, $matches)) {
                $currentYear = $matches[1];
            }
        }
        
        return "clients/{$clientName}/Kegiatan Perusahaan/{$currentYear}/SPT MASA/{$monthName}/{$jenisPajak}/PELAPORAN {$jenisPajak}";
    }

    /**
     * Get accepted file types for invoices
     */
    public static function getAcceptedFileTypes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/webp'
        ];
    }

    /**
     * Get max file size in KB
     */
    public static function getMaxFileSize(): int
    {
        return 10240; // 10MB
    }
}