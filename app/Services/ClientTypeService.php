<?php

namespace App\Services;

class ClientTypeService
{
    /**
     * Client type mapping based on first 2 digits of invoice number
     */
    private const CLIENT_TYPE_MAPPING = [
        '01' => ['type' => 'Swasta', 'has_ppn' => true],
        '02' => ['type' => 'Pemerintah', 'has_ppn' => false],
        '03' => ['type' => 'Pemerintah', 'has_ppn' => false],
        '04' => ['type' => 'Swasta', 'has_ppn' => true],
        '05' => ['type' => 'Swasta', 'has_ppn' => true],
        '06' => ['type' => 'Swasta', 'has_ppn' => true],
        '07' => ['type' => 'BUMN', 'has_ppn' => false],
        '08' => ['type' => 'Swasta (SKB)', 'has_ppn' => false],
        '09' => ['type' => 'Swasta', 'has_ppn' => true],
    ];

    /**
     * Get client type and PPN status based on first 2 digits of invoice number
     */
    public static function getClientTypeFromInvoiceNumber(string $invoiceNumber): array
    {
        // Extract first 2 digits
        $firstTwoDigits = substr($invoiceNumber, 0, 2);
        
        return self::CLIENT_TYPE_MAPPING[$firstTwoDigits] ?? ['type' => 'Unknown', 'has_ppn' => true];
    }

    /**
     * Get all available client types for select options
     */
    public static function getClientTypeOptions(): array
    {
        return [
            'Swasta' => 'Swasta',
            'Pemerintah' => 'Pemerintah',
            'BUMN' => 'BUMN',
            'Swasta (SKB)' => 'Swasta (SKB)',
        ];
    }

    /**
     * Get color for client type badges
     */
    public static function getClientTypeColor(string $clientType): string
    {
        return match($clientType) {
            'Swasta' => 'success',
            'Pemerintah' => 'info',
            'BUMN' => 'warning',
            'Swasta (SKB)' => 'danger',
            default => 'gray'
        };
    }

    /**
     * Check if client type has PPN
     */
    public static function hasPPN(string $clientType): bool
    {
        $mapping = array_filter(self::CLIENT_TYPE_MAPPING, fn($item) => $item['type'] === $clientType);
        
        if (!empty($mapping)) {
            return array_values($mapping)[0]['has_ppn'];
        }
        
        return true; // Default to true for unknown types
    }

    /**
     * Get all client types that have PPN
     */
    public static function getClientTypesWithPPN(): array
    {
        return array_filter(self::CLIENT_TYPE_MAPPING, fn($item) => $item['has_ppn']);
    }

    /**
     * Get all client types that don't have PPN
     */
    public static function getClientTypesWithoutPPN(): array
    {
        return array_filter(self::CLIENT_TYPE_MAPPING, fn($item) => !$item['has_ppn']);
    }
}