<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum QuotationStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'Draft';
    case Sent = 'Sent';
    case UnderNegotiation = 'Under Negotiation';
    case Accepted = 'Accepted';
    case Rejected = 'Rejected';
    case Expired = 'Expired';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'info',
            self::UnderNegotiation => 'warning',
            self::Accepted => 'success',
            self::Rejected => 'danger',
            self::Expired => 'secondary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-m-pencil-square',
            self::Sent => 'heroicon-m-paper-airplane',
            self::UnderNegotiation => 'heroicon-m-arrows-right-left',
            self::Accepted => 'heroicon-m-check-badge',
            self::Rejected => 'heroicon-m-x-circle',
            self::Expired => 'heroicon-m-clock',
        };
    }
}
