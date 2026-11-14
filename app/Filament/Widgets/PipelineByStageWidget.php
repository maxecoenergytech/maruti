<?php

namespace App\Filament\Widgets;

use App\Enums\ProspectStage;
use App\Models\Prospect;
use Filament\Widgets\ChartWidget;

class PipelineByStageWidget extends ChartWidget
{
    protected static ?string $heading = 'Pipeline Value by Stage (₹)';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = ['default' => 'full', 'md' => 1];

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $user = auth()->user();

        $stages = [
            ProspectStage::New,
            ProspectStage::Contacted,
            ProspectStage::Quoted,
            ProspectStage::Negotiation,
            ProspectStage::Won,
            ProspectStage::Lost,
        ];

        $labels = [];
        $values = [];

        foreach ($stages as $stage) {
            $labels[] = $stage->value;
            $values[] = (float) Prospect::query()
                ->visibleTo($user)
                ->where('stage', $stage)
                ->sum('expected_value');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Expected Value (₹)',
                    'data' => $values,
                    'backgroundColor' => [
                        '#38bdf8', // New
                        '#818cf8', // Contacted
                        '#a855f7', // Quoted
                        '#f59e0b', // Negotiation
                        '#10b981', // Won
                        '#f43f5e', // Lost
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
