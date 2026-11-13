<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TargetPeriodType: string implements HasLabel, HasColor
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Yearly => 'Yearly',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Monthly => 'info',
            self::Yearly => 'primary',
        };
    }
}
