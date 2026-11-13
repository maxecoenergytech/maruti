<?php

namespace App\Services;

use App\Enums\QuotationStatus;
use App\Enums\TargetPeriodType;
use App\Models\Quotation;
use App\Models\Target;
use App\Models\User;
use Carbon\Carbon;

class TargetAchievementService
{
    /**
     * Compute start and end Carbon datetime for an Indian financial year and month.
     * FY runs 1 April to 31 March.
     * Month: 0 = entire FY; 4-12 = April-December of start year; 1-3 = January-March of end year.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public static function getDateRange(string $financialYear, int $month = 0): array
    {
        $parts = explode('-', $financialYear);
        $startYear = (int) $parts[0];
        $endYear = count($parts) > 1 ? (int) (substr((string) $startYear, 0, 2) . $parts[1]) : $startYear + 1;

        if ($month === 0) {
            // Entire financial year: 1 April startYear to 31 March endYear
            $start = Carbon::create($startYear, 4, 1, 0, 0, 0);
            $end = Carbon::create($endYear, 3, 31, 23, 59, 59);
        } elseif ($month >= 4 && $month <= 12) {
            // April to December belongs to startYear
            $start = Carbon::create($startYear, $month, 1, 0, 0, 0)->startOfMonth();
            $end = Carbon::create($startYear, $month, 1, 23, 59, 59)->endOfMonth();
        } else {
            // January to March belongs to endYear
            $start = Carbon::create($endYear, $month, 1, 0, 0, 0)->startOfMonth();
            $end = Carbon::create($endYear, $month, 1, 23, 59, 59)->endOfMonth();
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Calculate achieved value: sum of grand_total of Accepted quotations
     * that are the LATEST revision, counted by accepted_at date.
     */
    public static function getAchievedAmount(?User $user, string $financialYear, int $month = 0): float
    {
        $range = self::getDateRange($financialYear, $month);

        $query = Quotation::query()
            ->where('status', QuotationStatus::Accepted)
            ->where('is_latest_revision', true)
            ->whereBetween('accepted_at', [$range['start'], $range['end']]);

        if ($user) {
            $query->where('created_by_user_id', $user->id);
        }

        return (float) $query->sum('grand_total');
    }

    /**
     * Calculate performance for a specific Target model instance.
     *
     * @return array{
     *     target: float,
     *     achieved: float,
     *     variance: float,
     *     percentage: float,
     *     is_achieved: bool
     * }
     */
    public static function calculateForTarget(Target $target): array
    {
        $achieved = self::getAchievedAmount($target->user, $target->financial_year, $target->month);
        $targetAmount = (float) $target->target_amount;
        $variance = round($achieved - $targetAmount, 2);
        $percentage = $targetAmount > 0 ? round(($achieved / $targetAmount) * 100, 2) : 0.0;

        return [
            'target' => $targetAmount,
            'achieved' => round($achieved, 2),
            'variance' => $variance,
            'percentage' => $percentage,
            'is_achieved' => $achieved >= $targetAmount && $targetAmount > 0,
        ];
    }

    /**
     * Calculate target vs achieved for an executive in a given FY and month.
     * If no target is set, target is 0.00.
     */
    public static function calculateForExecutive(User $user, string $financialYear, int $month = 0): array
    {
        $periodType = $month === 0 ? TargetPeriodType::Yearly : TargetPeriodType::Monthly;

        $targetRecord = Target::query()
            ->where('user_id', $user->id)
            ->where('financial_year', $financialYear)
            ->where('period_type', $periodType)
            ->where('month', $month)
            ->first();

        $targetAmount = $targetRecord ? (float) $targetRecord->target_amount : 0.0;
        $achieved = self::getAchievedAmount($user, $financialYear, $month);
        $variance = round($achieved - $targetAmount, 2);
        $percentage = $targetAmount > 0 ? round(($achieved / $targetAmount) * 100, 2) : 0.0;

        return [
            'user' => $user,
            'target' => $targetAmount,
            'achieved' => round($achieved, 2),
            'variance' => $variance,
            'percentage' => $percentage,
            'is_achieved' => $achieved >= $targetAmount && $targetAmount > 0,
        ];
    }

    /**
     * Calculate firm-wide total target vs achieved across all executives.
     */
    public static function calculateFirmTotal(string $financialYear, int $month = 0): array
    {
        $periodType = $month === 0 ? TargetPeriodType::Yearly : TargetPeriodType::Monthly;

        $totalTarget = (float) Target::query()
            ->where('financial_year', $financialYear)
            ->where('period_type', $periodType)
            ->where('month', $month)
            ->sum('target_amount');

        $totalAchieved = self::getAchievedAmount(null, $financialYear, $month);
        $variance = round($totalAchieved - $totalTarget, 2);
        $percentage = $totalTarget > 0 ? round(($totalAchieved / $totalTarget) * 100, 2) : 0.0;

        return [
            'target' => $totalTarget,
            'achieved' => round($totalAchieved, 2),
            'variance' => $variance,
            'percentage' => $percentage,
            'is_achieved' => $totalAchieved >= $totalTarget && $totalTarget > 0,
        ];
    }

    /**
     * Get 12-month breakdown (April to March) for an executive in a financial year.
     *
     * @return array<int, array{month: int, name: string, target: float, achieved: float, variance: float, percentage: float}>
     */
    public static function getMonthlyBreakdownForExecutive(User $user, string $financialYear): array
    {
        $breakdown = [];
        $months = Target::monthOptions();

        foreach ($months as $monthNum => $name) {
            $data = self::calculateForExecutive($user, $financialYear, $monthNum);
            $breakdown[] = [
                'month' => $monthNum,
                'name' => $name,
                'target' => $data['target'],
                'achieved' => $data['achieved'],
                'variance' => $data['variance'],
                'percentage' => $data['percentage'],
            ];
        }

        return $breakdown;
    }
}
