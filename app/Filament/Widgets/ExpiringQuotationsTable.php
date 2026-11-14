<?php

namespace App\Filament\Widgets;

use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationResource;
use App\Models\Quotation;
use App\Support\IndianCurrencyHelper;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringQuotationsTable extends BaseWidget
{
    protected static ?string $heading = 'Quotations Expiring in Next 7 Days';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = ['default' => 'full', 'md' => 1];

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(
                Quotation::query()
                    ->visibleTo($user)
                    ->where('is_latest_revision', true)
                    ->whereIn('status', [
                        QuotationStatus::Draft,
                        QuotationStatus::Sent,
                        QuotationStatus::UnderNegotiation,
                    ])
                    ->whereBetween('valid_until', [today(), today()->addDays(7)])
                    ->orderBy('valid_until', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('quotation_number')
                    ->label('Quote No')
                    ->weight('bold')
                    ->url(fn (Quotation $record) => QuotationResource::getUrl('view', ['record' => $record])),

                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->wrap(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Expires On')
                    ->date('d-m-Y')
                    ->badge()
                    ->color(fn (Quotation $record) => $record->valid_until->diffInDays(today()) <= 2 ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Value')
                    ->formatStateUsing(fn ($state) => IndianCurrencyHelper::formatRupees($state))
                    ->weight('bold'),
            ])
            ->paginated(false);
    }
}
