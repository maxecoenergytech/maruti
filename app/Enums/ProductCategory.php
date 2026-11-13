<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductCategory: string implements HasLabel, HasColor
{
    case Hardware = 'Hardware';
    case Software = 'Software';
    case CctvSecurity = 'CCTV/Security';
    case Networking = 'Networking';
    case Service = 'Service';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Hardware => 'primary',
            self::Software => 'info',
            self::CctvSecurity => 'danger',
            self::Networking => 'warning',
            self::Service => 'success',
        };
    }
}
