<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Filament\Resources\QuotationResource\RelationManagers;
use App\Models\Quotation;
use App\Support\IndianCurrencyHelper;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->canBeEdited()),

            Actions\Action::make('create_revision')
                ->label('Create Revision')
                ->icon('heroicon-o-document-duplicate')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Create Quotation Revision')
                ->modalDescription('This will create a new draft revision (e.g. /R1, /R2) and mark the current one as superseded.')
                ->action(function () {
                    $revision = $this->record->createRevision();
                    Notification::make()
                        ->title('Revision Created')
                        ->body("Created revision {$revision->quotation_number}")
                        ->success()
                        ->send();

                    return redirect(QuotationResource::getUrl('edit', ['record' => $revision]));
                }),

            Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => route('quotations.pdf.preview', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn () => route('quotations.pdf.download', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('share_whatsapp')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(function () {
                    $record = $this->record;
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

            Actions\Action::make('share_email')
                ->label('Email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->url(function () {
                    $record = $this->record;
                    $email = $record->client?->email ?? '';
                    $contact = $record->client?->contact_person ?: $record->client_name;
                    $amount = IndianCurrencyHelper::formatRupees($record->grand_total);
                    $subject = rawurlencode("Quotation {$record->quotation_number} from Maruti Sales & Services");
                    $body = rawurlencode("Dear {$contact},\n\nPlease find quotation {$record->quotation_number} dated {$record->quotation_date->format('d-m-Y')} for {$amount}.\n\nValidity: {$record->valid_until->format('d-m-Y')}\n\nWarm regards,\nMaruti Sales & Services\nPaltan Bazar, Guwahati\nPhone: +91 70022 53021");

                    return "mailto:{$email}?subject={$subject}&body={$body}";
                })
                ->openUrlInNewTab(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            RelationManagers\ActivitiesRelationManager::class,
        ];
    }
}
