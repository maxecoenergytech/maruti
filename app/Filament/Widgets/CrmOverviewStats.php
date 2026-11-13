<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Prospect;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmOverviewStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        $openProspectsCount = Prospect::query()->visibleTo($user)->open()->count();
        $dueTodayCount = FollowUp::query()->visibleTo($user)->dueToday()->count();
        $overdueCount = FollowUp::query()->visibleTo($user)->overdue()->count();
        $totalClientsCount = Client::query()->visibleTo($user)->active()->count();

        return [
            Stat::make('Open Prospects / Leads', $openProspectsCount)
                ->description('Active pipeline deals')
                ->descriptionIcon('heroicon-m-funnel')
                ->color('primary'),

            Stat::make('Follow-ups Due Today', $dueTodayCount)
                ->description('Tasks scheduled for today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($dueTodayCount > 0 ? 'warning' : 'gray'),

            Stat::make('Overdue Follow-ups', $overdueCount)
                ->description($overdueCount > 0 ? 'Requires immediate action' : 'All caught up')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueCount > 0 ? 'danger' : 'success'),

            Stat::make('Active Clients', $totalClientsCount)
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),
        ];
    }
}
