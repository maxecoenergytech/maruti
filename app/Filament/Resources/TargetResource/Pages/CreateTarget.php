<?php

namespace App\Filament\Resources\TargetResource\Pages;

use App\Enums\TargetPeriodType;
use App\Filament\Resources\TargetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTarget extends CreateRecord
{
    protected static string $resource = TargetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['period_type'] === TargetPeriodType::Yearly->value || $data['period_type'] === TargetPeriodType::Yearly) {
            $data['month'] = 0;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
