<?php

namespace App\Filament\Widgets;

use App\Enums\FollowUpStatus;
use App\Filament\Resources\FollowUpResource;
use App\Models\FollowUp;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OverdueFollowUpsTable extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '⚠️ Overdue Follow-ups (Action Required)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FollowUp::query()
                    ->visibleTo(auth()->user())
                    ->overdue()
                    ->with(['assignedUser', 'prospect', 'client'])
                    ->latest('due_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d-m-Y')
                    ->badge()
                    ->color('danger')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject / Task')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('linked_to')
                    ->label('Linked Record')
                    ->state(function (FollowUp $record): string {
                        if ($record->prospect) {
                            return '🎯 ' . $record->prospect->contact_person . ($record->prospect->company_name ? ' (' . $record->prospect->company_name . ')' : '');
                        }
                        if ($record->client) {
                            return '🏢 ' . $record->client->company_name;
                        }
                        return '—';
                    }),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Executive')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('markCompleted')
                    ->label('Mark Done')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('outcome_notes')
                            ->label('Interaction Outcome Notes')
                            ->placeholder('Describe what was discussed or next actions...')
                            ->required(),
                    ])
                    ->action(function (FollowUp $record, array $data): void {
                        $record->update([
                            'status' => FollowUpStatus::Completed,
                            'outcome_notes' => $data['outcome_notes'],
                        ]);

                        Notification::make()
                            ->title('Follow-up Marked Completed')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('view')
                    ->label('View / Edit')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (FollowUp $record): string => FollowUpResource::getUrl('edit', ['record' => $record->id])),
            ])
            ->emptyStateHeading('No Overdue Follow-ups')
            ->emptyStateDescription('Great job! All customer interactions are currently on schedule.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
