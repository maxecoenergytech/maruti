<?php

namespace App\Filament\Resources;

use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationResource\Pages;
use App\Filament\Resources\QuotationResource\RelationManagers;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use App\Services\GstCalculationService;
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

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Sales & Billing';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->visibleTo(auth()->user())
            ->with(['client', 'createdBy']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Quotation Details')
                    ->schema([
                        Forms\Components\TextInput::make('quotation_number')
                            ->label('Quotation Number')
                            ->placeholder('Auto-generated (e.g. MSS/2026-27/0001)')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('client_id')
                            ->label('Client / Organization')
                            ->relationship('client', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(fn (?Quotation $record) => $record?->isLocked() ?? false)
                            ->afterStateUpdated(function (?int $state, Set $set, Get $get) {
                                if (!$state) {
                                    return;
                                }
                                $client = Client::find($state);
                                if ($client) {
                                    $set('client_name', $client->company_name);
                                    $set('billing_address', $client->billing_address);
                                    $set('shipping_address', $client->shipping_address);
                                    $set('gstin', $client->gstin);
                                    $set('state', $client->state);
                                    $set('state_code', $client->state_code);

                                    // Trigger recalculation with new state code
                                    self::recalculateTotals($get, $set);
                                }
                            }),

                        Forms\Components\DatePicker::make('quotation_date')
                            ->label('Quotation Date')
                            ->default(today())
                            ->displayFormat('d-m-Y')
                            ->native(false)
                            ->required()
                            ->disabled(fn (?Quotation $record) => $record?->isLocked() ?? false),

                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Valid Until (Default 15 Days)')
                            ->default(today()->addDays(15))
                            ->displayFormat('d-m-Y')
                            ->native(false)
                            ->required()
                            ->disabled(fn (?Quotation $record) => $record?->isLocked() ?? false),

                        Forms\Components\Select::make('status')
                            ->options(QuotationStatus::class)
                            ->default(QuotationStatus::Draft)
                            ->required()
                            ->disabled(fn (?Quotation $record) => $record?->isLocked() ?? false),

                        Forms\Components\Select::make('created_by_user_id')
                            ->label('Sales Executive')
                            ->options(fn () => User::query()->where('is_active', true)->pluck('name', 'id'))
                            ->default(fn () => auth()->id())
                            ->disabled(fn () => !auth()->user()?->isAdmin() || (bool) request()->route('record'))
                            ->dehydrated()
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Client Snapshot (Captured at creation)')
                    ->collapsible()
                    ->collapsed(fn (?Quotation $record) => filled($record?->client_name))
                    ->schema([
                        Forms\Components\TextInput::make('client_name')
                            ->label('Client Name')
                            ->required(),
                        Forms\Components\TextInput::make('gstin')
                            ->label('Client GSTIN')
                            ->placeholder('Unregistered'),
                        Forms\Components\TextInput::make('state')
                            ->label('Client State')
                            ->default('Assam')
                            ->required(),
                        Forms\Components\TextInput::make('state_code')
                            ->label('State Code')
                            ->default('18')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set)),
                        Forms\Components\Textarea::make('billing_address')
                            ->label('Billing Address')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('shipping_address')
                            ->label('Shipping Address')
                            ->columnSpanFull(),
                    ])->columns(4),

                Forms\Components\Section::make('Line Items')
                    ->description('Pick catalog products or enter custom items. Rates and taxes calculate automatically.')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product from Catalog')
                                    ->options(fn () => Product::query()->where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(function (?int $state, Set $set, Get $get) {
                                        if (!$state) {
                                            return;
                                        }
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('item_name', $product->name);
                                            $set('description', $product->description);
                                            $set('hsn_sac', $product->hsn_sac);
                                            $set('unit', $product->unit);
                                            $set('unit_rate', (float) $product->default_rate);
                                            $set('gst_rate', (float) $product->gst_rate);

                                            // Recompute line
                                            $qty = (float) ($get('quantity') ?: 1);
                                            $rate = (float) $product->default_rate;
                                            $disc = (float) ($get('discount_percent') ?: 0);
                                            $taxable = round(($qty * $rate) * (1 - $disc / 100), 2);
                                            $gst = (float) $product->gst_rate;
                                            $total = round($taxable * (1 + $gst / 100), 2);

                                            $set('taxable_value', $taxable);
                                            $set('total_amount', $total);
                                        }
                                    })
                                    ->columnSpan(4),

                                Forms\Components\TextInput::make('item_name')
                                    ->label('Item Name / Model')
                                    ->required()
                                    ->columnSpan(4),

                                Forms\Components\TextInput::make('hsn_sac')
                                    ->label('HSN/SAC')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateLine($get, $set))
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('unit')
                                    ->label('Unit')
                                    ->default('Pcs')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('unit_rate')
                                    ->label('Rate (₹)')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateLine($get, $set))
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('discount_percent')
                                    ->label('Disc %')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateLine($get, $set))
                                    ->columnSpan(2),

                                Forms\Components\Select::make('gst_rate')
                                    ->label('GST %')
                                    ->options([
                                        '0' => '0%',
                                        '5' => '5%',
                                        '12' => '12%',
                                        '18' => '18%',
                                        '28' => '28%',
                                    ])
                                    ->default('18')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateLine($get, $set))
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('taxable_value')
                                    ->label('Taxable (₹)')
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('₹')
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total (₹)')
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('₹')
                                    ->columnSpan(3),

                                Forms\Components\Textarea::make('description')
                                    ->label('Item Description / Specifications')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->reorderable()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set))
                            ->disabled(fn (?Quotation $record) => $record?->isLocked() ?? false),
                    ]),

                Forms\Components\Section::make('Tax & Amount Summary')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal (₹)')
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('discount_amount')
                            ->label('Total Discount (₹)')
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('taxable_amount')
                            ->label('Taxable Amount (₹)')
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('cgst_amount')
                            ->label('CGST Total (₹)')
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('State Code 18: 50% of GST'),

                        Forms\Components\TextInput::make('sgst_amount')
                            ->label('SGST Total (₹)')
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('State Code 18: 50% of GST'),

                        Forms\Components\TextInput::make('igst_amount')
                            ->label('IGST Total (₹)')
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Inter-state (Non-18): 100% of GST'),

                        Forms\Components\TextInput::make('round_off')
                            ->label('Round Off (₹)')
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('grand_total')
                            ->label('Grand Total (₹)')
                            ->prefix('₹')
                            ->extraAttributes(['class' => 'font-bold text-lg text-primary-600'])
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Textarea::make('amount_in_words')
                            ->label('Amount in Words')
                            ->rows(2)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Terms & Notes')
                    ->schema([
                        Forms\Components\Textarea::make('terms_and_conditions')
                            ->label('Terms and Conditions')
                            ->default(fn () => CompanySetting::getSettings()->quotation_default_terms)
                            ->rows(5)
                            ->columnSpanFull()
                            ->disabled(fn (?Quotation $record) => $record?->isLocked() ?? false),

                        Forms\Components\Textarea::make('notes')
                            ->label('Internal Notes / Special Instructions')
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn (?Quotation $record) => $record?->isLocked() ?? false),
                    ]),
            ]);
    }

    public static function recalculateLine(Get $get, Set $set): void
    {
        $qty = (float) ($get('quantity') ?: 1);
        $rate = (float) ($get('unit_rate') ?: 0);
        $discPct = (float) ($get('discount_percent') ?: 0);
        $gstRate = (float) ($get('gst_rate') ?: 18);

        $lineSubtotal = round($qty * $rate, 2);
        $lineDisc = round($lineSubtotal * ($discPct / 100), 2);
        $lineTaxable = round($lineSubtotal - $lineDisc, 2);
        $lineTax = round($lineTaxable * ($gstRate / 100), 2);
        $lineTotal = round($lineTaxable + $lineTax, 2);

        $set('taxable_value', $lineTaxable);
        $set('total_amount', $lineTotal);
    }

    public static function recalculateTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];
        $clientStateCode = $get('state_code') ?: '18';

        $calc = GstCalculationService::calculate($items, $clientStateCode);

        $set('subtotal', $calc['subtotal']);
        $set('discount_amount', $calc['discount_amount']);
        $set('taxable_amount', $calc['taxable_amount']);
        $set('cgst_amount', $calc['cgst_amount']);
        $set('sgst_amount', $calc['sgst_amount']);
        $set('igst_amount', $calc['igst_amount']);
        $set('total_tax', $calc['total_tax']);
        $set('round_off', $calc['round_off']);
        $set('grand_total', $calc['grand_total']);
        $set('amount_in_words', $calc['amount_in_words']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quotation_number')
                    ->label('Quote No')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('quotation_date')
                    ->label('Date')
                    ->date('d-m-Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date('d-m-Y')
                    ->sortable()
                    ->color(fn (Quotation $record) => $record->valid_until->isPast() && in_array($record->status, [QuotationStatus::Draft, QuotationStatus::Sent, QuotationStatus::UnderNegotiation]) ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->formatStateUsing(fn ($state) => IndianCurrencyHelper::formatRupees($state))
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('revision_number')
                    ->label('Rev')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "R{$state}" : 'Orig')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Executive')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('quotation_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(QuotationStatus::class),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'company_name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('created_by_user_id')
                    ->label('Executive')
                    ->relationship('createdBy', 'name')
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                Tables\Filters\Filter::make('active_revisions')
                    ->label('Latest Revisions Only')
                    ->default(true)
                    ->query(fn (Builder $query) => $query->latestRevision()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (Quotation $record) => $record->canBeEdited()),
                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (Quotation $record) => route('quotations.pdf.preview', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary')
                        ->url(fn (Quotation $record) => route('quotations.pdf.download', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('create_revision')
                        ->label('Create Revision')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Create Quotation Revision')
                        ->modalDescription('This will create a new draft revision (e.g. /R1, /R2) and mark the current one as superseded.')
                        ->action(function (Quotation $record) {
                            $revision = $record->createRevision();
                            Notification::make()
                                ->title('Revision Created')
                                ->body("Created revision {$revision->quotation_number}")
                                ->success()
                                ->send();

                            return redirect(QuotationResource::getUrl('edit', ['record' => $revision]));
                        }),
                    Tables\Actions\Action::make('share_whatsapp')
                        ->label('WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success')
                        ->url(function (Quotation $record) {
                            $phone = preg_replace('/[^0-9]/', '', $record->client?->phone ?? '');
                            if (strlen($phone) === 10) {
                                $phone = '91' . $phone;
                            }
                            $contact = $record->client?->contact_person ?: $record->client_name;
                            $amount = IndianCurrencyHelper::formatRupees($record->grand_total);
                            $text = rawurlencode("Dear {$contact},\n\nPlease find Quotation {$record->quotation_number} dated {$record->quotation_date->format('d-m-Y')} from Maruti Sales & Services for {$amount}.\nValid until {$record->valid_until->format('d-m-Y')}.\n\nThank you for choosing Maruti Sales & Services (Guwahati).");

                            return "https://wa.me/{$phone}?text={$text}";
                        })
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('share_email')
                        ->label('Email')
                        ->icon('heroicon-o-envelope')
                        ->url(function (Quotation $record) {
                            $email = $record->client?->email ?? '';
                            $contact = $record->client?->contact_person ?: $record->client_name;
                            $amount = IndianCurrencyHelper::formatRupees($record->grand_total);
                            $subject = rawurlencode("Quotation {$record->quotation_number} from Maruti Sales & Services");
                            $body = rawurlencode("Dear {$contact},\n\nPlease find quotation {$record->quotation_number} dated {$record->quotation_date->format('d-m-Y')} for {$amount}.\n\nValidity: {$record->valid_until->format('d-m-Y')}\n\nWarm regards,\nMaruti Sales & Services\nPaltan Bazar, Guwahati\nPhone: +91 70022 53021");

                            return "mailto:{$email}?subject={$subject}&body={$body}";
                        })
                        ->openUrlInNewTab(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'view' => Pages\ViewQuotation::route('/{record}'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
