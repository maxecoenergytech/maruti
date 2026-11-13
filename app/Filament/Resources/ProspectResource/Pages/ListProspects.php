<?php

namespace App\Filament\Resources\ProspectResource\Pages;

use App\Filament\Resources\ProspectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProspects extends ListRecords
{
    protected static string $resource = ProspectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kanbanView')
                ->label('Pipeline Board')
                ->icon('heroicon-m-view-columns')
                ->color('primary')
                ->url(ProspectResource::getUrl('kanban')),
            Actions\CreateAction::make(),
        ];
    }
}
