<?php

namespace App\Filament\Widgets;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Filament\Widgets\ChartWidget;

class QuotationStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Quotations by Status';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = ['default' => 'full', 'md' => 1];

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $user = auth()->user();

        $statuses = [
            QuotationStatus::Draft,
            QuotationStatus::Sent,
            QuotationStatus::UnderNegotiation,
            QuotationStatus::Accepted,
            QuotationStatus::Rejected,
            QuotationStatus::Expired,
        ];

        $rawCounts = Quotation::query()
            ->visibleTo($user)
            ->where('is_latest_revision', true)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $labels = [];
        $counts = [];

        foreach ($statuses as $status) {
            $labels[] = $status->value;
            $counts[] = (int) ($rawCounts[$status->value] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Quotations',
                    'data' => $counts,
                    'backgroundColor' => [
                        '#94a3b8', // Draft (slate)
                        '#3b82f6', // Sent (blue)
                        '#f59e0b', // Under Negotiation (amber)
                        '#10b981', // Accepted (emerald)
                        '#ef4444', // Rejected (red)
                        '#64748b', // Expired (slate-dark)
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
