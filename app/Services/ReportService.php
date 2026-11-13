<?php

namespace App\Services;

use App\Enums\ProspectSource;
use App\Enums\ProspectStage;
use App\Enums\QuotationStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\Target;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /**
     * Report 1: Quotation Register
     */
    public static function getQuotationRegister(array $filters, ?User $user = null): array
    {
        $user ??= auth()->user();

        $dateFrom = !empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : Carbon::create(2026, 4, 1, 0, 0, 0);
        $dateTo = !empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : Carbon::create(2027, 3, 31, 23, 59, 59);

        $query = Quotation::query()
            ->visibleTo($user)
            ->whereBetween('quotation_date', [$dateFrom, $dateTo])
            ->with(['client', 'createdBy']);

        if (empty($filters['include_all_revisions'])) {
            $query->where('is_latest_revision', true);
        }

        if (!empty($filters['user_id']) && $user->isAdmin()) {
            $query->where('created_by_user_id', $filters['user_id']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $records = $query->orderBy('quotation_date', 'desc')->get();

        $rows = [];
        $totalTaxable = 0.0;
        $totalTax = 0.0;
        $totalGrand = 0.0;

        foreach ($records as $q) {
            $taxable = (float) $q->taxable_amount;
            $tax = (float) $q->total_tax;
            $grand = (float) $q->grand_total;

            $totalTaxable += $taxable;
            $totalTax += $tax;
            $totalGrand += $grand;

            $rows[] = [
                'number' => $q->quotation_number,
                'date' => $q->quotation_date->format('d-m-Y'),
                'client' => $q->client_name,
                'state' => $q->state,
                'executive' => $q->createdBy?->name ?? '—',
                'status' => $q->status instanceof QuotationStatus ? $q->status->value : (string) $q->status,
                'taxable' => $taxable,
                'tax' => $tax,
                'grand_total' => $grand,
            ];
        }

        return [
            'rows' => $rows,
            'totals' => [
                'count' => count($rows),
                'taxable' => round($totalTaxable, 2),
                'tax' => round($totalTax, 2),
                'grand_total' => round($totalGrand, 2),
            ],
        ];
    }

    /**
     * Report 2: Client-wise Sales
     */
    public static function getClientSales(array $filters, ?User $user = null): array
    {
        $user ??= auth()->user();

        $dateFrom = !empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : Carbon::create(2026, 4, 1, 0, 0, 0);
        $dateTo = !empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : Carbon::create(2027, 3, 31, 23, 59, 59);

        $query = Quotation::query()
            ->visibleTo($user)
            ->where('status', QuotationStatus::Accepted)
            ->where('is_latest_revision', true)
            ->whereBetween('accepted_at', [$dateFrom, $dateTo]);

        if (!empty($filters['user_id']) && $user->isAdmin()) {
            $query->where('created_by_user_id', $filters['user_id']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        $acceptedQuotes = $query->with('client')->get();

        // Group by client
        $grouped = [];
        foreach ($acceptedQuotes as $quote) {
            $clientId = $quote->client_id;
            if (!isset($grouped[$clientId])) {
                $grouped[$clientId] = [
                    'client_name' => $quote->client_name,
                    'state' => $quote->state,
                    'accepted_count' => 0,
                    'total_value' => 0.0,
                ];
            }
            $grouped[$clientId]['accepted_count']++;
            $grouped[$clientId]['total_value'] += (float) $quote->grand_total;
        }

        // Sort descending by total_value
        usort($grouped, fn ($a, $b) => $b['total_value'] <=> $a['total_value']);

        $totalQuotes = 0;
        $totalSales = 0.0;
        foreach ($grouped as $row) {
            $totalQuotes += $row['accepted_count'];
            $totalSales += $row['total_value'];
        }

        return [
            'rows' => $grouped,
            'totals' => [
                'client_count' => count($grouped),
                'total_quotes' => $totalQuotes,
                'total_sales' => round($totalSales, 2),
            ],
        ];
    }

    /**
     * Report 3: Executive-wise Target Achievement
     */
    public static function getTargetAchievement(array $filters, ?User $user = null): array
    {
        $user ??= auth()->user();

        $fy = !empty($filters['financial_year']) ? $filters['financial_year'] : QuotationNumberService::getFinancialYear();
        $dateRange = TargetAchievementService::getDateRange($fy, 0);

        $query = User::query()->where('is_active', true);

        if ($user->isAdmin()) {
            if (!empty($filters['user_id'])) {
                $query->where('id', $filters['user_id']);
            } else {
                $query->whereIn('role', [UserRole::SalesExecutive, UserRole::Admin]);
            }
        } else {
            $query->where('id', $user->id);
        }

        $executives = $query->get();

        $rows = [];
        $totalTarget = 0.0;
        $totalAchieved = 0.0;

        foreach ($executives as $exec) {
            // Annual Target for this FY
            $annualTarget = (float) Target::query()
                ->where('user_id', $exec->id)
                ->where('financial_year', $fy)
                ->where('period_type', \App\Enums\TargetPeriodType::Yearly)
                ->where('month', 0)
                ->value('target_amount');

            // If no yearly target set, sum monthly targets
            if ($annualTarget <= 0) {
                $annualTarget = (float) Target::query()
                    ->where('user_id', $exec->id)
                    ->where('financial_year', $fy)
                    ->where('period_type', \App\Enums\TargetPeriodType::Monthly)
                    ->sum('target_amount');
            }

            $achieved = TargetAchievementService::getAchievedAmount($exec, $fy, 0);
            $variance = round($achieved - $annualTarget, 2);
            $percentage = $annualTarget > 0 ? round(($achieved / $annualTarget) * 100, 2) : 0.0;

            $totalTarget += $annualTarget;
            $totalAchieved += $achieved;

            $rows[] = [
                'executive' => $exec->name,
                'email' => $exec->email,
                'target' => $annualTarget,
                'achieved' => round($achieved, 2),
                'variance' => $variance,
                'percentage' => $percentage,
            ];
        }

        $overallVariance = round($totalAchieved - $totalTarget, 2);
        $overallPercentage = $totalTarget > 0 ? round(($totalAchieved / $totalTarget) * 100, 2) : 0.0;

        return [
            'rows' => $rows,
            'totals' => [
                'target' => round($totalTarget, 2),
                'achieved' => round($totalAchieved, 2),
                'variance' => $overallVariance,
                'percentage' => $overallPercentage,
            ],
        ];
    }

    /**
     * Report 4: Prospect Conversion
     */
    public static function getProspectConversion(array $filters, ?User $user = null): array
    {
        $user ??= auth()->user();

        $query = Prospect::query()->visibleTo($user);

        if (!empty($filters['user_id']) && $user->isAdmin()) {
            $query->where('assigned_user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $from = Carbon::parse($filters['date_from'])->startOfDay();
            $to = Carbon::parse($filters['date_to'])->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        }

        $prospects = $query->get();

        $sources = ProspectSource::cases();
        $rows = [];

        $totalLeads = 0;
        $totalWon = 0;
        $totalLost = 0;
        $totalOpen = 0;
        $totalPipeline = 0.0;

        foreach ($sources as $source) {
            $sourceProspects = $prospects->where('source', $source);
            $leadsCount = $sourceProspects->count();
            $wonCount = $sourceProspects->where('stage', ProspectStage::Won)->count();
            $lostCount = $sourceProspects->where('stage', ProspectStage::Lost)->count();
            $openCount = $sourceProspects->whereIn('stage', [
                ProspectStage::New,
                ProspectStage::Contacted,
                ProspectStage::Quoted,
                ProspectStage::Negotiation,
            ])->count();
            $pipelineVal = (float) $sourceProspects->whereIn('stage', [
                ProspectStage::New,
                ProspectStage::Contacted,
                ProspectStage::Quoted,
                ProspectStage::Negotiation,
            ])->sum('expected_value');

            $resolved = $wonCount + $lostCount;
            $winRate = $resolved > 0 ? round(($wonCount / $resolved) * 100, 1) : ($leadsCount > 0 ? round(($wonCount / $leadsCount) * 100, 1) : 0.0);

            $totalLeads += $leadsCount;
            $totalWon += $wonCount;
            $totalLost += $lostCount;
            $totalOpen += $openCount;
            $totalPipeline += $pipelineVal;

            $rows[] = [
                'source' => $source->value,
                'total_leads' => $leadsCount,
                'won' => $wonCount,
                'lost' => $lostCount,
                'open' => $openCount,
                'win_rate' => $winRate,
                'pipeline_value' => round($pipelineVal, 2),
            ];
        }

        $overallResolved = $totalWon + $totalLost;
        $overallWinRate = $overallResolved > 0 ? round(($totalWon / $overallResolved) * 100, 1) : 0.0;

        return [
            'rows' => $rows,
            'totals' => [
                'total_leads' => $totalLeads,
                'won' => $totalWon,
                'lost' => $totalLost,
                'open' => $totalOpen,
                'win_rate' => $overallWinRate,
                'pipeline_value' => round($totalPipeline, 2),
            ],
        ];
    }
}
