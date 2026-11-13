<?php

namespace App\Filament\Widgets;

use App\Services\QuotationNumberService;
use App\Services\TargetAchievementService;
use App\Support\IndianCurrencyHelper;
use Filament\Widgets\Widget;

class MonthlyTargetProgressBar extends Widget
{
    protected static ?int $sort = 2;

    protected static string $view = 'filament.widgets.monthly-target-progress-bar';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = auth()->user();
        $fy = QuotationNumberService::getFinancialYear();
        $currentMonth = today()->month;
        $monthName = today()->format('F Y');

        if ($user->isAdmin()) {
            $data = TargetAchievementService::calculateFirmTotal($fy, $currentMonth);
            $scopeLabel = 'Firm-Wide Total (All Executives)';
        } else {
            $data = TargetAchievementService::calculateForExecutive($user, $fy, $currentMonth);
            $scopeLabel = $user->name . ' (Personal Monthly Target)';
        }

        $pct = $data['percentage'];
        if ($pct >= 100) {
            $color = 'success';
            $barClass = 'bg-emerald-600';
            $textClass = 'text-emerald-700 dark:text-emerald-400';
            $statusLabel = 'Target Met / Exceeded!';
        } elseif ($pct >= 50) {
            $color = 'warning';
            $barClass = 'bg-amber-500';
            $textClass = 'text-amber-700 dark:text-amber-400';
            $statusLabel = 'In Progress (On Track)';
        } else {
            $color = 'danger';
            $barClass = 'bg-rose-500';
            $textClass = 'text-rose-700 dark:text-rose-400';
            $statusLabel = 'Needs Attention';
        }

        return [
            'monthName' => $monthName,
            'scopeLabel' => $scopeLabel,
            'target' => $data['target'],
            'targetFormatted' => IndianCurrencyHelper::formatRupees($data['target']),
            'achieved' => $data['achieved'],
            'achievedFormatted' => IndianCurrencyHelper::formatRupees($data['achieved']),
            'variance' => $data['variance'],
            'varianceFormatted' => ($data['variance'] >= 0 ? '+ ' : '- ') . IndianCurrencyHelper::formatRupees(abs($data['variance'])),
            'percentage' => $pct,
            'displayPercent' => min($pct, 100),
            'color' => $color,
            'barClass' => $barClass,
            'textClass' => $textClass,
            'statusLabel' => $statusLabel,
        ];
    }
}
