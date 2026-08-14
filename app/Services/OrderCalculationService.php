<?php

namespace App\Services;

class OrderCalculationService
{
    /**
     * Calculate E_NET: Customer Price - Ad Fee Charges.
     */
    public function calculateEbayNet(float $customerPrice, float $adFeeCharges = 0.0): float
    {
        return round($customerPrice - $adFeeCharges, 2);
    }

    /**
     * Calculate Profit: E_NET - Amazon Price (Supplier Cost).
     */
    public function calculateProfit(float $ebayNet, float $amazonPrice): float
    {
        return round($ebayNet - $amazonPrice, 2);
    }

    /**
     * Calculate ROI: Profit / E_NET.
     * Safely handles division by zero.
     */
    public function calculateRoi(float $profit, float $ebayNet): float
    {
        if ($ebayNet == 0.0) {
            return 0.0;
        }

        return round($profit / $ebayNet, 4);
    }

    /**
     * Format a financial amount with currency symbol.
     */
    public function formatFinancial(float $amount, string $currencySymbol = '€'): string
    {
        return $currencySymbol . number_format($amount, 2, '.', ',');
    }

    /**
     * Format ROI as percentage string.
     */
    public function formatPercentage(float $roi): string
    {
        return number_format($roi * 100, 2, '.', '') . '%';
    }
}
