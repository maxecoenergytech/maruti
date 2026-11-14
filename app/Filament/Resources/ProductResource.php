<?php

namespace App\Filament\Resources;

use App\Enums\ProductCategory;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Support\IndianCurrencyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sales & Billing';

    protected static ?string $navigationLabel = 'Products & Services';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product / Service Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Product / Service Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category')
                            ->options(ProductCategory::class)
                            ->required()
                            ->default(ProductCategory::Hardware),
                        Forms\Components\TextInput::make('hsn_sac')
                            ->label('HSN / SAC Code')
                            ->placeholder('e.g. 84713010, 85258090, 998713')
                            ->maxLength(20),
                        Forms\Components\Select::make('unit')
                            ->label('Measuring Unit')
                            ->options([
                                'Pcs' => 'Pcs (Pieces)',
                                'Set' => 'Set',
                                'Meter' => 'Meter',
                                'Lot' => 'Lot',
                                'Hours' => 'Hours',
                                'Month' => 'Month',
                                'Year' => 'Year',
                            ])
                            ->default('Pcs')
                            ->required(),
                        Forms\Components\TextInput::make('default_rate')
                            ->label('Default Unit Rate (₹)')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0.00)
                            ->required(),
                        Forms\Components\Select::make('gst_rate')
                            ->label('GST Rate (%)')
                            ->options([
                                '0.00' => '0% (Exempt)',
                                '5.00' => '5%',
                                '12.00' => '12%',
                                '18.00' => '18% (Standard IT/CCTV)',
                                '28.00' => '28%',
                            ])
                            ->default('18.00')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Standard Description / Specifications')
                            ->placeholder('Detailed specifications, model number, warranty notes...')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active in Catalog')
                            ->default(true)
                            ->required(),
                    ])->columns(['default' => 1, 'md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hsn_sac')
                    ->label('HSN/SAC')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('default_rate')
                    ->label('Default Rate')
                    ->formatStateUsing(fn ($state) => IndianCurrencyHelper::formatRupees($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit'),
                Tables\Columns\TextColumn::make('gst_rate')
                    ->label('GST %')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0) . '%')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(ProductCategory::class),
                Tables\Filters\SelectFilter::make('gst_rate')
                    ->label('GST Rate')
                    ->options([
                        '0.00' => '0%',
                        '5.00' => '5%',
                        '12.00' => '12%',
                        '18.00' => '18%',
                        '28.00' => '28%',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
