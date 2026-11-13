<?php

namespace App\Filament\Resources\QuotationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Activity & Status Change History';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('from_status')
                    ->label('From Status')
                    ->disabled(),
                Forms\Components\TextInput::make('to_status')
                    ->label('To Status')
                    ->required(),
                Forms\Components\Textarea::make('remarks')
                    ->label('Remarks / Reason')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('to_status')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d-m-Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Changed By')
                    ->placeholder('System')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('from_status')
                    ->label('Previous Status')
                    ->placeholder('New')
                    ->badge(),
                Tables\Columns\TextColumn::make('to_status')
                    ->label('New Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Accepted' => 'success',
                        'Rejected' => 'danger',
                        'Sent' => 'info',
                        'Under Negotiation' => 'warning',
                        'Expired' => 'secondary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
