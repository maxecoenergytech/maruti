<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use App\Models\User;
use App\Rules\ValidGstin;
use App\Support\GstHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return $query->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Company & Contact Info')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Company / Firm Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->required()
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
                        Forms\Components\Select::make('assigned_user_id')
                            ->label('Assigned Sales Executive')
                            ->options(fn () => User::query()->where('is_active', true)->pluck('name', 'id'))
                            ->default(fn () => auth()->id())
                            ->disabled(fn () => !auth()->user()?->isAdmin())
                            ->dehydrated()
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Tax & Address Details')
                    ->schema([
                        Forms\Components\TextInput::make('state')
                            ->label('State Name')
                            ->default('Assam')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('state_code')
                            ->label('State Code (2 Digits)')
                            ->default('18')
                            ->required()
                            ->length(2)
                            ->numeric()
                            ->helperText('18 = Assam. Used to automatically calculate CGST+SGST vs IGST.'),
                        Forms\Components\Textarea::make('billing_address')
                            ->label('Billing Address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('shipping_address')
                            ->label('Shipping Address')
                            ->helperText('Leave empty if same as billing address.')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Internal Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Client')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact Person')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('gstin')
                    ->label('GSTIN')
                    ->searchable()
                    ->placeholder('Unregistered')
                    ->badge()
                    ->color(fn (?string $state): string => blank($state) ? 'gray' : 'success'),
                Tables\Columns\TextColumn::make('state')
                    ->label('State')
                    ->badge()
                    ->color(fn (Client $record): string => $record->state_code === '18' ? 'primary' : 'warning'),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state_code')
                    ->label('State / GST Type')
                    ->options([
                        '18' => 'Assam (18 - Intra-state)',
                        'other' => 'Other States (Inter-state / IGST)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === '18') {
                            $query->where('state_code', '18');
                        } elseif ($data['value'] === 'other') {
                            $query->where('state_code', '!=', '18');
                        }
                    }),
                Tables\Filters\SelectFilter::make('assigned_user_id')
                    ->label('Executive')
                    ->relationship('assignedUser', 'name')
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            ClientResource\RelationManagers\FollowUpsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
