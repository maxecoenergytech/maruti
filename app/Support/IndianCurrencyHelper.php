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

    /**
     * Convert amount to Indian currency words format (e.g. "Rupees One Lakh Forty Thousand Only")
     */
    public static function convertToWords(float|int|string|null $amount): string
    {
        $amount = round((float) ($amount ?? 0), 2);
        if ($amount == 0) {
            return 'Rupees Zero Only';
        }

        $isNegative = $amount < 0;
        $amount = abs($amount);

        $rupees = (int) floor($amount);
        $paisa = (int) round(($amount - $rupees) * 100);

        $words = [];
        if ($isNegative) {
            $words[] = 'Minus';
        }

        $words[] = 'Rupees';

        if ($rupees > 0) {
            $words[] = self::numberToIndianWords($rupees);
        } else {
            $words[] = 'Zero';
        }

        if ($paisa > 0) {
            $words[] = 'and ' . self::numberToIndianWords($paisa) . ' Paise';
        }

        $words[] = 'Only';

        return trim(preg_replace('/\s+/', ' ', implode(' ', $words)));
    }

    private static function numberToIndianWords(int $num): string
    {
        if ($num === 0) {
            return 'Zero';
        }

        $units = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'
        ];

        $tens = [
            '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];

        $result = '';

        // Crores (1,00,00,000)
        if ($num >= 10000000) {
            $crores = (int) floor($num / 10000000);
            $result .= self::numberToIndianWords($crores) . ' Crore ';
            $num %= 10000000;
        }

        // Lakhs (1,00,000)
        if ($num >= 100000) {
            $lakhs = (int) floor($num / 100000);
            $result .= self::convertTwoDigits($lakhs, $units, $tens) . ' Lakh ';
            $num %= 100000;
        }

        // Thousands (1,000)
        if ($num >= 1000) {
            $thousands = (int) floor($num / 1000);
            $result .= self::convertTwoDigits($thousands, $units, $tens) . ' Thousand ';
            $num %= 1000;
        }

        // Hundreds (100)
        if ($num >= 100) {
            $hundreds = (int) floor($num / 100);
            $result .= $units[$hundreds] . ' Hundred ';
            $num %= 100;
        }

        // Remainder 1-99
        if ($num > 0) {
            $result .= self::convertTwoDigits($num, $units, $tens) . ' ';
        }

        return trim($result);
    }

    private static function convertTwoDigits(int $num, array $units, array $tens): string
    {
        if ($num < 20) {
            return $units[$num];
        }

        $ten = (int) floor($num / 10);
        $unit = $num % 10;

        return $tens[$ten] . ($unit > 0 ? '-' . $units[$unit] : '');
    }
}
