<?php

namespace App\Filament\Resources\ProspectResource\Pages;

use App\Enums\ProspectStage;
use App\Filament\Resources\ProspectResource;
use App\Models\Prospect;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class KanbanProspects extends Page
{
    protected static string $resource = ProspectResource::class;

    protected static string $view = 'filament.resources.prospect-resource.pages.kanban-prospects';

    protected static ?string $title = 'Prospects Pipeline Board';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('listView')
                ->label('Table View')
                ->icon('heroicon-m-table-cells')
                ->color('gray')
                ->url(ProspectResource::getUrl('index')),
            Actions\CreateAction::make(),
        ];
    }

    public function getStagesProperty(): array
    {
        return ProspectStage::cases();
    }

    public function getProspectsByStageProperty(): array
    {
        $prospects = Prospect::query()
            ->visibleTo(auth()->user())
            ->with(['assignedUser', 'convertedClient'])
            ->latest('updated_at')
            ->get();

        $grouped = [];
        foreach (ProspectStage::cases() as $stage) {
            $grouped[$stage->value] = $prospects->where('stage', $stage);
        }

        return $grouped;
    }

    public function updateStage(int $prospectId, string $newStage): void
    {
        $prospect = Prospect::query()
            ->visibleTo(auth()->user())
            ->findOrFail($prospectId);

        $stageEnum = ProspectStage::tryFrom($newStage);
        if (!$stageEnum) {
            return;
        }

        $prospect->update(['stage' => $stageEnum]);

        Notification::make()
            ->title('Stage Updated')
            ->body("Moved {$prospect->contact_person} to {$stageEnum->value}")
            ->success()
            ->send();
    }
}
