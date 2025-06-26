<?php

namespace App\Services;

class TaxCalculationService
{
    /**
     * Calculate DPP and PPN from DPP Nilai Lainnya (when PPN is 12%)
     * Formula: DPP = DPP Nilai Lainnya × 12/11
     * PPN = DPP × 11% (always 11% of the calculated DPP)
     */
    public static function calculateFromDppNilaiLainnya(float $dppNilaiLainnya): array
    {
        // Calculate DPP using the formula: DPP Nilai Lainnya × 12/11
        $dppValue = $dppNilaiLainnya * (12/11);
        
        // Round DPP to nearest whole number
        $dppValueRounded = round($dppValue);
        
        // Calculate PPN (11% of the rounded DPP)
        $ppnValue = $dppValueRounded * 0.11;
        
        // Round PPN to nearest whole number
        $ppnValueRounded = round($ppnValue);
        
        return [
            'dpp' => $dppValueRounded,
            'ppn' => $ppnValueRounded,
            'dpp_formatted' => number_format($dppValueRounded, 2, '.', ','),
            'ppn_formatted' => number_format($ppnValueRounded, 2, '.', ','),
        ];
    }

    /**
     * Calculate PPN from DPP (when PPN is 11%)
     */
    public static function calculatePPNFromDpp(float $dpp): array
    {
        // Calculate PPN (11% of DPP)
        $ppnValue = $dpp * 0.11;
        
        // Round PPN to nearest whole number
        $ppnValueRounded = round($ppnValue);
        
        return [
            'ppn' => $ppnValueRounded,
            'ppn_formatted' => number_format($ppnValueRounded, 2, '.', ','),
        ];
    }

    /**
     * Clean monetary input (remove formatting, keep only numbers and decimal point)
     */
    public static function cleanMonetaryInput(?string $input): float
    {
        if (empty($input)) {
            return 0.0;
        }
        
        $cleaned = preg_replace('/[^0-9.]/', '', $input);
        
        return is_numeric($cleaned) ? floatval($cleaned) : 0.0;
    }

    /**
     * Format number for currency display
     */
    public static function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, '.', ',');
    }

    /**
     * Calculate tax based on percentage and amount
     */
    public static function calculateTax(float $amount, float $percentage): float
    {
        return round($amount * ($percentage / 100));
    }

    /**
     * Get PPN percentage options
     */
    public static function getPPNPercentageOptions(): array
    {
        return [
            '11' => '11%',
            '12' => '12%',
        ];
    }

    /**
     * Validate if PPN percentage is valid
     */
    public static function isValidPPNPercentage(string $percentage): bool
    {
        return in_array($percentage, ['11', '12']);
    }
}