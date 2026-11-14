<?php

namespace App\Filament\Widgets;

use App\Enums\QuotationStatus;
use App\Models\Client;
use App\Services\QuotationNumberService;
use App\Services\TargetAchievementService;
use App\Support\IndianCurrencyHelper;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopClientsTable extends BaseWidget
{
    protected static ?string $heading = 'Top 5 Clients by Won Value (FY ' . '2026-27' . ')';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = ['default' => 'full', 'md' => 1];

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $fy = QuotationNumberService::getFinancialYear();
        $range = TargetAchievementService::getDateRange($fy, 0);

        return $table
            ->query(
                Client::query()
                    ->visibleTo($user)
                    ->withSum(['quotations as won_value' => function ($q) use ($range, $user) {
                        $q->where('status', QuotationStatus::Accepted)
                            ->where('is_latest_revision', true)
                            ->whereBetween('accepted_at', [$range['start'], $range['end']]);

                        if (!$user->isAdmin()) {
                            $q->where('created_by_user_id', $user->id);
                        }
                    }], 'grand_total')
                    ->having('won_value', '>', 0)
                    ->orderByDesc('won_value')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Client')
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('state')
                    ->label('State')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('won_value')
                    ->label('Won Value')
                    ->formatStateUsing(fn ($state) => IndianCurrencyHelper::formatRupees($state ?? 0))
                    ->weight('bold')
                    ->color('success'),
            ])
            ->paginated(false);
    }
}
