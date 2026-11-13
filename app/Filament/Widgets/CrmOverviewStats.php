<?php

namespace App\Filament\Widgets;

use App\Enums\QuotationStatus;
use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Support\IndianCurrencyHelper;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmOverviewStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        // 1. Clients
        $totalClientsCount = Client::query()->visibleTo($user)->active()->count();

        // 2. Open Prospects
        $openProspects = Prospect::query()->visibleTo($user)->open();
        $openProspectsCount = (clone $openProspects)->count();
        $openPipelineValue = (clone $openProspects)->sum('expected_value');

        // 3. Quotations This Month
        $monthStart = today()->startOfMonth();
        $monthEnd = today()->endOfMonth();

        $quotationsThisMonth = Quotation::query()
            ->visibleTo($user)
            ->where('is_latest_revision', true)
            ->whereBetween('quotation_date', [$monthStart, $monthEnd]);

        $quotesCount = (clone $quotationsThisMonth)->count();
        $quotesTotal = (clone $quotationsThisMonth)->sum('grand_total');

        // 4. Won Value This Month (Accepted by accepted_at)
        $wonThisMonth = Quotation::query()
            ->visibleTo($user)
            ->where('status', QuotationStatus::Accepted)
            ->where('is_latest_revision', true)
            ->whereBetween('accepted_at', [$monthStart, $monthEnd]);

        $wonTotal = (clone $wonThisMonth)->sum('grand_total');
        $wonCount = (clone $wonThisMonth)->count();

        // 5. Follow-ups Due Today & Overdue
        $dueTodayCount = FollowUp::query()->visibleTo($user)->dueToday()->count();
        $overdueCount = FollowUp::query()->visibleTo($user)->overdue()->count();

        return [
            Stat::make('Active Clients', $totalClientsCount)
                ->description('Registered client accounts')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),

            Stat::make('Open Prospects', $openProspectsCount)
                ->description('Pipeline: ' . IndianCurrencyHelper::formatRupees($openPipelineValue))
                ->descriptionIcon('heroicon-m-funnel')
                ->color('primary'),

            Stat::make('Quotations (This Month)', "{$quotesCount} Quotes")
                ->description('Total: ' . IndianCurrencyHelper::formatRupees($quotesTotal))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('Won Value (This Month)', IndianCurrencyHelper::formatRupees($wonTotal))
                ->description("{$wonCount} accepted deal(s)")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Follow-ups Due Today', $dueTodayCount)
                ->description($dueTodayCount > 0 ? 'Scheduled for today' : 'None scheduled')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($dueTodayCount > 0 ? 'warning' : 'gray'),

            Stat::make('Overdue Follow-ups', $overdueCount)
                ->description($overdueCount > 0 ? 'Immediate action required' : 'All up to date')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueCount > 0 ? 'danger' : 'success'),
        ];
    }
}
