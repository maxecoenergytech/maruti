<?php

namespace App\Services;

use App\Support\IndianCurrencyHelper;

class GstCalculationService
{
    /**
     * Company home state code (Assam = 18).
     */
    public const HOME_STATE_CODE = '18';

    /**
     * Calculate item line amounts and entire quotation totals.
     *
     * @param array $items Array of line items [product_id, item_name, description, hsn_sac, quantity, unit, unit_rate, discount_percent, gst_rate]
     * @param string|null $clientStateCode Client's 2-digit state code (e.g. '18' for Assam)
     * @return array
     */
    public static function calculate(array $items, ?string $clientStateCode = '18'): array
    {
        $isInterstate = trim((string) $clientStateCode) !== self::HOME_STATE_CODE;

        $processedItems = [];
        $subtotal = 0.00;
        $discountTotal = 0.00;
        $taxableTotal = 0.00;
        $cgstTotal = 0.00;
        $sgstTotal = 0.00;
        $igstTotal = 0.00;

        foreach ($items as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $rate = (float) ($item['unit_rate'] ?? 0);
            $discPct = (float) ($item['discount_percent'] ?? 0);
            $gstRate = (float) ($item['gst_rate'] ?? 18);

            $lineSubtotal = round($qty * $rate, 2);
            $lineDiscount = round($lineSubtotal * ($discPct / 100), 2);
            $lineTaxable = round($lineSubtotal - $lineDiscount, 2);

            if ($isInterstate) {
                $lineCgst = 0.00;
                $lineSgst = 0.00;
                $lineIgst = round($lineTaxable * ($gstRate / 100), 2);
            } else {
                $lineCgst = round($lineTaxable * ($gstRate / 200), 2);
                $lineSgst = round($lineTaxable * ($gstRate / 200), 2);
                $lineIgst = 0.00;
            }

            $lineTotal = round($lineTaxable + $lineCgst + $lineSgst + $lineIgst, 2);

            $subtotal += $lineSubtotal;
            $discountTotal += $lineDiscount;
            $taxableTotal += $lineTaxable;
            $cgstTotal += $lineCgst;
            $sgstTotal += $lineSgst;
            $igstTotal += $lineIgst;

            $item['quantity'] = $qty;
            $item['unit_rate'] = $rate;
            $item['discount_percent'] = $discPct;
            $item['discount_amount'] = $lineDiscount;
            $item['taxable_value'] = $lineTaxable;
            $item['gst_rate'] = $gstRate;
            $item['cgst_amount'] = $lineCgst;
            $item['sgst_amount'] = $lineSgst;
            $item['igst_amount'] = $lineIgst;
            $item['total_amount'] = $lineTotal;
            $item['sort_order'] = $index;

            $processedItems[] = $item;
        }

        $subtotal = round($subtotal, 2);
        $discountTotal = round($discountTotal, 2);
        $taxableTotal = round($taxableTotal, 2);
        $cgstTotal = round($cgstTotal, 2);
        $sgstTotal = round($sgstTotal, 2);
        $igstTotal = round($igstTotal, 2);
        $totalTax = round($cgstTotal + $sgstTotal + $igstTotal, 2);

        $exactGrandTotal = round($taxableTotal + $totalTax, 2);
        $roundedGrandTotal = round($exactGrandTotal);
        $roundOff = round($roundedGrandTotal - $exactGrandTotal, 2);

        return [
            'items' => $processedItems,
            'is_interstate' => $isInterstate,
            'subtotal' => $subtotal,
            'discount_amount' => $discountTotal,
            'taxable_amount' => $taxableTotal,
            'cgst_amount' => $cgstTotal,
            'sgst_amount' => $sgstTotal,
            'igst_amount' => $igstTotal,
            'total_tax' => $totalTax,
            'round_off' => $roundOff,
            'grand_total' => $roundedGrandTotal,
            'amount_in_words' => IndianCurrencyHelper::convertToWords($roundedGrandTotal),
        ];
    }
}
