<?php

namespace App\Support;

class IndianCurrencyHelper
{
    /**
     * Format a number in Indian rupee grouping format (e.g. ₹ 1,50,000.00)
     */
    public static function formatRupees(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $amount = (float) ($amount ?? 0);
        $negative = $amount < 0;
        $amount = abs($amount);

        $parts = explode('.', number_format($amount, 2, '.', ''));
        $integerPart = $parts[0];
        $decimalPart = $parts[1];

        if (strlen($integerPart) > 3) {
            $lastThree = substr($integerPart, -3);
            $remaining = substr($integerPart, 0, -3);
            $remainingFormatted = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
            $formattedInteger = $remainingFormatted . ',' . $lastThree;
        } else {
            $formattedInteger = $integerPart;
        }

        $result = ($negative ? '-' : '') . $formattedInteger . '.' . $decimalPart;

        return $withSymbol ? '₹ ' . $result : $result;
    }
}
