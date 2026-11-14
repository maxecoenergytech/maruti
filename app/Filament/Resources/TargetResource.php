<?php

namespace App\Filament\Resources;

use App\Enums\TargetPeriodType;
use App\Enums\UserRole;
use App\Filament\Resources\TargetResource\Pages;
use App\Models\Target;
use App\Models\User;
use App\Services\QuotationNumberService;
use App\Services\TargetAchievementService;
use App\Support\IndianCurrencyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TargetResource extends Resource
{
    protected static ?string $model = Target::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Sales & Billing';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->visibleTo(auth()->user())
            ->with(['user']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Target Configuration')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Sales Executive')
                            ->options(
                                User::where('role', UserRole::SalesExecutive)
                                    ->orWhere('role', UserRole::Admin)
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('period_type')
                            ->label('Period Type')
                            ->options(TargetPeriodType::class)
                            ->default(TargetPeriodType::Monthly)
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if ($state === TargetPeriodType::Yearly->value) {
                                    $set('month', 0);
                                } elseif ($get('month') === 0) {
                                    $set('month', today()->month);
                                }
                            }),

                        Forms\Components\TextInput::make('financial_year')
                            ->label('Financial Year')
                            ->default(QuotationNumberService::getFinancialYear())
                            ->required()
                            ->maxLength(10)
                            ->placeholder('e.g. 2026-27'),

                        Forms\Components\Select::make('month')
                            ->label('Month')
                            ->options([0 => 'Full Year (0)'] + Target::monthOptions())
                            ->default(today()->month)
                            ->required()
                            ->disabled(fn (Get $get) => $get('period_type') === TargetPeriodType::Yearly->value || $get('period_type') === TargetPeriodType::Yearly)
                            ->dehydrated(),

                        Forms\Components\TextInput::make('target_amount')
                            ->label('Target Amount')
                            ->prefix('₹')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText('Gross target value in Indian Rupees for the period.'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes / Remarks')
                            ->placeholder('Special focus areas, strategic objectives, etc.')
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Executive')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('financial_year')
                    ->label('FY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('period_type')
                    ->label('Period')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('month_name')
                    ->label('Month')
                    ->badge()
                    ->color(fn (Target $record) => $record->period_type === TargetPeriodType::Yearly ? 'primary' : 'gray'),

                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Target')
                    ->formatStateUsing(fn ($state) => IndianCurrencyHelper::formatRupees($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('achieved_amount')
                    ->label('Achieved')
                    ->getStateUsing(fn (Target $record) => TargetAchievementService::calculateForTarget($record)['achieved'])
                    ->formatStateUsing(fn ($state) => IndianCurrencyHelper::formatRupees($state))
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('variance')
                    ->label('Variance')
                    ->getStateUsing(function (Target $record) {
                        $diff = TargetAchievementService::calculateForTarget($record)['variance'];
                        $formatted = IndianCurrencyHelper::formatRupees(abs($diff));
                        return ($diff >= 0 ? '+ ' : '- ') . $formatted;
                    })
                    ->color(fn (Target $record) => TargetAchievementService::calculateForTarget($record)['variance'] >= 0 ? 'success' : 'danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('Achieved %')
                    ->getStateUsing(function (Target $record) {
                        return TargetAchievementService::calculateForTarget($record)['percentage'] . '%';
                    })
                    ->badge()
                    ->color(function (Target $record) {
                        $pct = TargetAchievementService::calculateForTarget($record)['percentage'];
                        if ($pct >= 100) {
                            return 'success';
                        }
                        if ($pct >= 50) {
                            return 'warning';
                        }
                        return 'danger';
                    }),
            ])
            ->defaultSort('financial_year', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Executive')
                    ->relationship('user', 'name')
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),

                Tables\Filters\SelectFilter::make('period_type')
                    ->options(TargetPeriodType::class),

                Tables\Filters\SelectFilter::make('month')
                    ->options([0 => 'Full Year'] + Target::monthOptions()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTargets::route('/'),
            'create' => Pages\CreateTarget::route('/create'),
            'view' => Pages\ViewTarget::route('/{record}'),
            'edit' => Pages\EditTarget::route('/{record}/edit'),
        ];
    }
}
