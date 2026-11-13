<?php

namespace App\Filament\Resources\TargetResource\Pages;

use App\Enums\TargetPeriodType;
use App\Filament\Resources\TargetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTarget extends EditRecord
{
    protected static string $resource = TargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->isAdmin() ?? false),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
