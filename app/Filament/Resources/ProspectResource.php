<?php

namespace App\Filament\Resources;

use App\Enums\ProspectSource;
use App\Enums\ProspectStage;
use App\Filament\Resources\ProspectResource\Pages;
use App\Filament\Resources\ProspectResource\RelationManagers;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use App\Rules\ValidGstin;
use App\Support\GstHelper;
use App\Support\IndianCurrencyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProspectResource extends Resource
{
    protected static ?string $model = Prospect::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Prospect Details')
                    ->schema([
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_name')
                            ->label('Company / Organization Name (Optional)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->maxLength(30),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Select::make('source')
                            ->label('Lead Source')
                            ->options(ProspectSource::class)
                            ->required()
                            ->default(ProspectSource::WalkIn),
                        Forms\Components\Select::make('assigned_user_id')
                            ->label('Assigned Executive')
                            ->options(fn () => User::query()->where('is_active', true)->pluck('name', 'id'))
                            ->default(fn () => auth()->id())
                            ->disabled(fn () => !auth()->user()?->isAdmin())
                            ->dehydrated()
                            ->searchable()
                            ->required(),
                    ])->columns(['default' => 1, 'md' => 2]),

                Forms\Components\Section::make('Requirements & Deal Stage')
                    ->schema([
                        Forms\Components\Textarea::make('requirement')
                            ->label('Requirement / Products of Interest')
                            ->required()
                            ->placeholder('e.g. 8 Channel Hikvision CCTV system + 4TB Surveillance HDD + Installation')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('expected_value')
                            ->label('Expected Deal Value (₹)')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0.00)
                            ->required(),
                        Forms\Components\Select::make('stage')
                            ->label('Pipeline Stage')
                            ->options(ProspectStage::class)
                            ->required()
                            ->default(ProspectStage::New)
                            ->live(),
                        Forms\Components\DatePicker::make('next_follow_up_date')
                            ->label('Next Follow-up Date')
                            ->displayFormat('d-m-Y')
                            ->native(false),
                        Forms\Components\Textarea::make('lost_reason')
                            ->label('Lost Reason')
                            ->placeholder('Provide reasons why this deal was lost (e.g. Price high, bought elsewhere)')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => $get('stage') === ProspectStage::Lost->value || $get('stage') === 'Lost')
                            ->required(fn (Get $get) => $get('stage') === ProspectStage::Lost->value || $get('stage') === 'Lost'),
                    ])->columns(['default' => 1, 'md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact Person')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->placeholder('Individual / Retail')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('expected_value')
                    ->label('Expected Value')
                    ->formatStateUsing(fn ($state) => IndianCurrencyHelper::formatRupees($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('stage')
                    ->label('Stage')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_follow_up_date')
                    ->label('Next Follow-up')
                    ->date('d-m-Y')
                    ->sortable()
                    ->color(function (Prospect $record): string {
                        if (!$record->next_follow_up_date || $record->is_converted) {
                            return 'gray';
                        }
                        if ($record->next_follow_up_date->isPast() && !$record->next_follow_up_date->isToday()) {
                            return 'danger';
                        }
                        if ($record->next_follow_up_date->isToday()) {
                            return 'warning';
                        }
                        return 'gray';
                    }),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('convertedClient.company_name')
                    ->label('Converted Client')
                    ->placeholder('Not Converted')
                    ->badge()
                    ->color('success')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->options(ProspectStage::class)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('source')
                    ->options(ProspectSource::class),
                Tables\Filters\SelectFilter::make('assigned_user_id')
                    ->label('Executive')
                    ->relationship('assignedUser', 'name')
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                Tables\Filters\Filter::make('has_follow_up_today')
                    ->label('Follow-up Due Today')
                    ->query(fn (Builder $query) => $query->whereDate('next_follow_up_date', today())),
                Tables\Filters\Filter::make('follow_up_overdue')
                    ->label('Follow-up Overdue')
                    ->query(fn (Builder $query) => $query->whereDate('next_follow_up_date', '<', today())->whereIn('stage', ProspectStage::openStages())),
            ])
            ->actions([
                Tables\Actions\Action::make('convertToClient')
                    ->label('Convert to Client')
                    ->icon('heroicon-m-user-plus')
                    ->color('success')
                    ->visible(fn (Prospect $record): bool => !$record->is_converted)
                    ->form([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Company / Firm Name')
                            ->default(fn (Prospect $record) => $record->company_name ?: $record->contact_person)
                            ->required(),
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->default(fn (Prospect $record) => $record->contact_person)
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->default(fn (Prospect $record) => $record->phone)
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->default(fn (Prospect $record) => $record->email),
                        Forms\Components\TextInput::make('gstin')
                            ->label('GSTIN (15 Digits)')
                            ->placeholder('e.g. 18AABFM1234P1Z2')
                            ->maxLength(15)
                            ->rule(new ValidGstin)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                if (blank($state)) {
                                    return;
                                }
                                $extracted = GstHelper::extractState($state);
                                if ($extracted['state_code']) {
                                    $set('state_code', $extracted['state_code']);
                                }
                                if ($extracted['state_name']) {
                                    $set('state', $extracted['state_name']);
                                }
                            }),
                        Forms\Components\TextInput::make('state')
                            ->label('State')
                            ->default('Assam')
                            ->required(),
                        Forms\Components\TextInput::make('state_code')
                            ->label('State Code')
                            ->default('18')
                            ->required()
                            ->length(2),
                        Forms\Components\Textarea::make('billing_address')
                            ->label('Billing Address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('shipping_address')
                            ->label('Shipping Address')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->action(function (Prospect $record, array $data): void {
                        if ($record->is_converted) {
                            Notification::make()
                                ->title('Already Converted')
                                ->body('This prospect has already been converted to a client.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $client = Client::create([
                            'company_name' => $data['company_name'],
                            'contact_person' => $data['contact_person'],
                            'phone' => $data['phone'],
                            'email' => $data['email'] ?? null,
                            'gstin' => $data['gstin'] ?? null,
                            'billing_address' => $data['billing_address'],
                            'shipping_address' => $data['shipping_address'] ?? null,
                            'state' => $data['state'],
                            'state_code' => $data['state_code'],
                            'assigned_user_id' => $record->assigned_user_id,
                            'is_active' => true,
                            'notes' => 'Converted from Prospect #' . $record->id . ' (' . $record->requirement . ')',
                        ]);

                        $record->update([
                            'converted_client_id' => $client->id,
                            'converted_at' => now(),
                            'stage' => ProspectStage::Won,
                        ]);

                        Notification::make()
                            ->title('Converted Successfully')
                            ->body("Client [{$client->company_name}] created from lead.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FollowUpsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProspects::route('/'),
            'create' => Pages\CreateProspect::route('/create'),
            'kanban' => Pages\KanbanProspects::route('/kanban'),
            'view' => Pages\ViewProspect::route('/{record}'),
            'edit' => Pages\EditProspect::route('/{record}/edit'),
        ];
    }
}
