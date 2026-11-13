<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProspectSource: string implements HasLabel
{
    case WalkIn = 'Walk-in';
    case PhoneCall = 'Phone Call';
    case Referral = 'Referral';
    case Website = 'Website';
    case Tender = 'Tender';
    case Other = 'Other';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
