<?php

namespace App\Filament\Widgets;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Filament\Widgets\ChartWidget;

class QuotationStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Quotations by Status';

    protected static ?int $sort = 3;

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

        $labels = [];
        $counts = [];

        foreach ($statuses as $status) {
            $labels[] = $status->value;
            $counts[] = Quotation::query()
                ->visibleTo($user)
                ->where('is_latest_revision', true)
                ->where('status', $status)
                ->count();
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
