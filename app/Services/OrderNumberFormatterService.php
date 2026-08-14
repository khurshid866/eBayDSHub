<?php

namespace App\Services;

class OrderNumberFormatterService
{
    /**
     * Format and normalize eBay order number to XX-XXXXX-XXXXX.
     */
    public function formatEbayOrderNumber(?string $rawNumber): string
    {
        if (empty($rawNumber)) {
            return '';
        }

        $trimmed = trim($rawNumber);

        // If already in XX-XXXXX-XXXXX format
        if (preg_match('/^\d{2}-\d{5}-\d{5}$/', $trimmed)) {
            return $trimmed;
        }

        // Remove all non-digits
        $digits = preg_replace('/[^\d]/', '', $trimmed);

        if (strlen($digits) === 12) {
            return sprintf(
                '%s-%s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 5),
                substr($digits, 7, 5)
            );
        }

        return $trimmed;
    }

    /**
     * Validate eBay order number format.
     */
    public function isValidEbayOrderNumber(string $number): bool
    {
        return (bool) preg_match('/^\d{2}-\d{5}-\d{5}$/', $number);
    }

    /**
     * Format and normalize Amazon order number to XXX-XXXXXXX-XXXXXXX.
     */
    public function formatAmazonOrderNumber(?string $rawNumber): string
    {
        if (empty($rawNumber)) {
            return '';
        }

        $trimmed = trim($rawNumber);

        // If already in XXX-XXXXXXX-XXXXXXX format
        if (preg_match('/^\d{3}-\d{7}-\d{7}$/', $trimmed)) {
            return $trimmed;
        }

        // Remove all non-digits
        $digits = preg_replace('/[^\d]/', '', $trimmed);

        if (strlen($digits) === 17) {
            return sprintf(
                '%s-%s-%s',
                substr($digits, 0, 3),
                substr($digits, 3, 7),
                substr($digits, 10, 7)
            );
        }

        return $trimmed;
    }

    /**
     * Validate Amazon order number format.
     */
    public function isValidAmazonOrderNumber(string $number): bool
    {
        if (empty($number)) {
            return true; // Amazon order number can be optional/nullable if needed, but if provided must match
        }
        return (bool) preg_match('/^\d{3}-\d{7}-\d{7}$/', $number);
    }
}
