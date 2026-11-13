<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationResource;
use App\Models\Quotation;
use App\Support\IndianCurrencyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuotationsRelationManager extends RelationManager
{
    protected static string $relationship = 'quotations';

    protected static ?string $title = 'Quotation History';

    public function form(Form $form): Form
    {
        return QuotationResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('quotation_number')
            ->columns([
                Tables\Columns\TextColumn::make('quotation_number')
                    ->label('Quote No')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quotation_date')
                    ->label('Date')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->formatStateUsing(fn ($state) => IndianCurrencyHelper::formatRupees($state))
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('revision_number')
                    ->label('Rev')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "R{$state}" : 'Orig')
                    ->badge(),
            ])
            ->defaultSort('quotation_date', 'desc')
            ->actions([
                Tables\Actions\Action::make('viewQuote')
                    ->label('View')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Quotation $record): string => QuotationResource::getUrl('view', ['record' => $record->id])),
            ]);
    }
}
