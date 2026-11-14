<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Enums\QuotationStatus;
use App\Filament\Actions\QuickShareQuotationModal;
use App\Filament\Resources\QuotationResource;
use App\Filament\Resources\QuotationResource\RelationManagers;
use App\Models\Quotation;
use App\Services\QuotationShareService;
use App\Support\IndianCurrencyHelper;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('quick_share')
                ->label('Quick Share')
                ->icon('heroicon-o-share')
                ->color('warning')
                ->modalHeading(fn () => "Quick Share: {$this->record->quotation_number}")
                ->modalDescription(fn () => "Share quotation for {$this->record->client_name} (" . IndianCurrencyHelper::formatRupees($this->record->grand_total) . ")")
                ->modalIcon('heroicon-o-share')
                ->modalWidth('lg')
                ->mountUsing(function (Forms\ComponentContainer $form) {
                    $form->fill([
                        'channel' => 'whatsapp',
                        'recipient_phone' => $this->record->client?->phone,
                        'recipient_email' => $this->record->client?->email,
                        'message_content' => QuotationShareService::generateMessage($this->record),
                        'mark_as_sent' => $this->record->status === QuotationStatus::Draft,
                    ]);
                })
                ->form(QuickShareQuotationModal::getFormSchema())
                ->modalSubmitActionLabel('Share Now')
                ->action(fn (array $data, $livewire) => QuickShareQuotationModal::executeShare($data, $this->record, $livewire)),

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
                ->url(fn () => QuotationShareService::getWhatsAppUrl($this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('share_email')
                ->label('Email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->url(fn () => QuotationShareService::getMailtoUrl($this->record))
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
