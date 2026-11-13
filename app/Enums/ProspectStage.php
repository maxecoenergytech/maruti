<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProspectStage: string implements HasLabel, HasColor, HasIcon
{
    case New = 'New';
    case Contacted = 'Contacted';
    case Quoted = 'Quoted';
    case Negotiation = 'Negotiation';
    case Won = 'Won';
    case Lost = 'Lost';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::New => 'gray',
            self::Contacted => 'info',
            self::Quoted => 'warning',
            self::Negotiation => 'primary',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-m-sparkles',
            self::Contacted => 'heroicon-m-chat-bubble-left-ellipsis',
            self::Quoted => 'heroicon-m-document-currency-rupee',
            self::Negotiation => 'heroicon-m-arrows-right-left',
            self::Won => 'heroicon-m-check-badge',
            self::Lost => 'heroicon-m-x-circle',
        };
    }

    public static function openStages(): array
    {
        return [
            self::New->value,
            self::Contacted->value,
            self::Quoted->value,
            self::Negotiation->value,
        ];
    }
}
