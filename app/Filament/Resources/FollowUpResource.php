<?php

namespace App\Filament\Resources;

use App\Enums\FollowUpStatus;
use App\Filament\Resources\FollowUpResource\Pages;
use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Prospect;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FollowUpResource extends Resource
{
    protected static ?string $model = FollowUp::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Follow-up Information')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Subject / Task Title')
                            ->placeholder('e.g. Call regarding CCTV quotation approval')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('assigned_user_id')
                            ->label('Assigned Executive')
                            ->options(fn () => User::query()->where('is_active', true)->pluck('name', 'id'))
                            ->default(fn () => auth()->id())
                            ->disabled(fn () => !auth()->user()?->isAdmin())
                            ->dehydrated()
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(FollowUpStatus::class)
                            ->default(FollowUpStatus::Pending)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Linked Entity')
                    ->description('Link this follow-up to a Prospect or Client (at least one is required).')
                    ->schema([
                        Forms\Components\Select::make('prospect_id')
                            ->label('Linked Prospect (Lead)')
                            ->options(fn () => Prospect::query()->visibleTo(auth()->user())->pluck('contact_person', 'id'))
                            ->searchable()
                            ->nullable(),
                        Forms\Components\Select::make('client_id')
                            ->label('Linked Client')
                            ->options(fn () => Client::query()->visibleTo(auth()->user())->pluck('company_name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Schedule & Outcome')
                    ->schema([
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->default(today())
                            ->displayFormat('d-m-Y')
                            ->native(false),
                        Forms\Components\TimePicker::make('due_time')
                            ->label('Due Time')
                            ->seconds(false),
                        Forms\Components\Textarea::make('outcome_notes')
                            ->label('Outcome / Interaction Notes')
                            ->placeholder('Notes on customer reaction, feedback, or next steps...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d-m-Y')
                    ->sortable()
                    ->description(fn (FollowUp $record): ?string => $record->due_time ? date('h:i A', strtotime($record->due_time)) : null)
                    ->color(function (FollowUp $record): string {
                        if ($record->isOverdue()) {
                            return 'danger';
                        }
                        if ($record->isDueToday()) {
                            return 'warning';
                        }
                        return 'gray';
                    })
                    ->weight(fn (FollowUp $record): string => ($record->isOverdue() || $record->isDueToday()) ? 'bold' : 'normal'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('linked_to')
                    ->label('Linked To')
                    ->state(function (FollowUp $record): string {
                        if ($record->prospect) {
                            return '🎯 Lead: ' . $record->prospect->contact_person . ($record->prospect->company_name ? ' (' . $record->prospect->company_name . ')' : '');
                        }
                        if ($record->client) {
                            return '🏢 Client: ' . $record->client->company_name;
                        }
                        return '—';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('outcome_notes')
                    ->label('Outcome')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('due_date', 'asc')
            ->filters([
                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue (Red)')
                    ->query(fn (Builder $query) => $query->overdue()),
                Tables\Filters\Filter::make('due_today')
                    ->label('Due Today (Amber)')
                    ->query(fn (Builder $query) => $query->dueToday()),
                Tables\Filters\SelectFilter::make('status')
                    ->options(FollowUpStatus::class),
                Tables\Filters\SelectFilter::make('assigned_user_id')
                    ->label('Executive')
                    ->relationship('assignedUser', 'name')
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
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
                            ->placeholder('Describe what was discussed or next actions...')
                            ->required()
                            ->rows(3),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFollowUps::route('/'),
            'create' => Pages\CreateFollowUp::route('/create'),
            'edit' => Pages\EditFollowUp::route('/{record}/edit'),
        ];
    }
}
