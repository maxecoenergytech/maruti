<?php

namespace App\Filament\Resources\FollowUpResource\Pages;

use App\Filament\Resources\FollowUpResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateFollowUp extends CreateRecord
{
    protected static string $resource = FollowUpResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!auth()->user()->isAdmin()) {
            $data['assigned_user_id'] = auth()->id();
        }

        if (empty($data['client_id']) && empty($data['prospect_id']) && empty($data['quotation_id'])) {
            throw ValidationException::withMessages([
                'prospect_id' => 'Please select at least one linked entity (Prospect or Client).',
            ]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
