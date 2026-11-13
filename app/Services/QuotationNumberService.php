<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuotationNumberService
{
    /**
     * Compute financial year for a given date (e.g. 2026-11-13 -> "2026-27").
     * Resets each year on April 1.
     */
    public static function getFinancialYear(?Carbon $date = null): string
    {
        $date ??= Carbon::today();
        $year = $date->year;
        $month = $date->month;

        if ($month >= 4) {
            $startYear = $year;
            $endYear = ($year + 1) % 100;
        } else {
            $startYear = $year - 1;
            $endYear = $year % 100;
        }

        return sprintf('%04d-%02d', $startYear, $endYear);
    }

    /**
     * Generate the next unique quotation number inside a DB transaction with locking.
     * Counts soft-deleted rows so numbers are never reused.
     *
     * @return array{quotation_number: string, financial_year: string, sequence_no: int}
     */
    public static function generateNextNumber(?Carbon $date = null): array
    {
        $date ??= Carbon::today();
        $fy = self::getFinancialYear($date);

        return DB::transaction(function () use ($fy) {
            $settings = CompanySetting::getSettings();
            $prefix = $settings->quotation_prefix ?: 'MSS';

            // Lock the latest original quotation in this FY including soft-deleted rows
            $lastOriginal = Quotation::withTrashed()
                ->where('financial_year', $fy)
                ->whereNull('parent_quotation_id')
                ->lockForUpdate()
                ->orderByDesc('sequence_no')
                ->first();

            $nextSequence = ($lastOriginal?->sequence_no ?? 0) + 1;
            $formattedSequence = sprintf('%04d', $nextSequence);
            $quotationNumber = "{$prefix}/{$fy}/{$formattedSequence}";

            return [
                'quotation_number' => $quotationNumber,
                'financial_year' => $fy,
                'sequence_no' => $nextSequence,
            ];
        });
    }

    /**
     * Generate a revision number from parent (e.g. MSS/2026-27/0001/R1).
     */
    public static function generateRevisionNumber(Quotation $parent): string
    {
        // Extract base number without /R...
        $baseNumber = preg_replace('/\/R\d+$/i', '', $parent->quotation_number);

        // Count existing revisions for this base sequence
        $revisionCount = Quotation::withTrashed()
            ->where(function ($query) use ($parent) {
                $query->where('parent_quotation_id', $parent->id)
                    ->orWhere('id', $parent->id)
                    ->orWhere('parent_quotation_id', $parent->parent_quotation_id);
            })
            ->whereNotNull('parent_quotation_id')
            ->count();

        $nextRev = $revisionCount + 1;

        return "{$baseNumber}/R{$nextRev}";
    }
}
