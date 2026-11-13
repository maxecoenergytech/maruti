<?php

namespace App\Filament\Resources\ProspectResource\RelationManagers;

use App\Enums\FollowUpStatus;
use App\Models\FollowUp;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FollowUpsRelationManager extends RelationManager
{
    protected static string $relationship = 'followUps';

    protected static ?string $title = 'Follow-up History & Tasks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('subject')
                    ->label('Subject / Task')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('due_date')
                    ->required()
                    ->default(today())
                    ->displayFormat('d-m-Y')
                    ->native(false),
                Forms\Components\TimePicker::make('due_time')
                    ->seconds(false),
                Forms\Components\Select::make('assigned_user_id')
                    ->label('Assigned To')
                    ->options(fn () => User::query()->where('is_active', true)->pluck('name', 'id'))
                    ->default(fn () => auth()->id())
                    ->disabled(fn () => !auth()->user()?->isAdmin())
                    ->dehydrated()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(FollowUpStatus::class)
                    ->default(FollowUpStatus::Pending)
                    ->required(),
                Forms\Components\Textarea::make('outcome_notes')
                    ->label('Outcome Notes')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d-m-Y')
                    ->sortable()
                    ->color(function (FollowUp $record): string {
                        if ($record->isOverdue()) {
                            return 'danger';
                        }
                        if ($record->isDueToday()) {
                            return 'warning';
                        }
                        return 'gray';
                    }),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Assigned To'),
                Tables\Columns\TextColumn::make('outcome_notes')
                    ->label('Outcome')
                    ->limit(30),
            ])
            ->defaultSort('due_date', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!auth()->user()->isAdmin()) {
                            $data['assigned_user_id'] = auth()->id();
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('markCompleted')
                    ->label('Mark Done')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (FollowUp $record): bool => $record->status === FollowUpStatus::Pending)
                    ->form([
                        Forms\Components\Textarea::make('outcome_notes')
                            ->label('Interaction Outcome Notes')
                            ->required(),
                    ])
                    ->action(function (FollowUp $record, array $data): void {
                        $record->update([
                            'status' => FollowUpStatus::Completed,
                            'outcome_notes' => $data['outcome_notes'],
                        ]);
                        Notification::make()->title('Completed')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
