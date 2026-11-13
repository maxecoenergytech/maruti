<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel, HasColor
{
    case Admin = 'admin';
    case SalesExecutive = 'sales_executive';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Admin => 'Owner / Admin',
            self::SalesExecutive => 'Sales Executive',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Admin => 'danger',
            self::SalesExecutive => 'info',
        };
    }
}
