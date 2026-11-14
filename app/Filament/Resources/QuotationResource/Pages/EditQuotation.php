<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Enums\QuotationStatus;
use App\Filament\Actions\QuickShareQuotationModal;
use App\Filament\Resources\QuotationResource;
use App\Services\GstCalculationService;
use App\Services\QuotationShareService;
use App\Support\IndianCurrencyHelper;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
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

            Actions\ViewAction::make(),
            Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => route('quotations.pdf.preview', $this->record))
                ->openUrlInNewTab(),
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
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->canBeEdited()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recompute all amounts on save
        $items = $data['items'] ?? [];
        $stateCode = $data['state_code'] ?? '18';
        $calc = GstCalculationService::calculate($items, $stateCode);

        $data['items'] = $calc['items'];
        $data['is_interstate'] = $calc['is_interstate'];
        $data['subtotal'] = $calc['subtotal'];
        $data['discount_amount'] = $calc['discount_amount'];
        $data['taxable_amount'] = $calc['taxable_amount'];
        $data['cgst_amount'] = $calc['cgst_amount'];
        $data['sgst_amount'] = $calc['sgst_amount'];
        $data['igst_amount'] = $calc['igst_amount'];
        $data['total_tax'] = $calc['total_tax'];
        $data['round_off'] = $calc['round_off'];
        $data['grand_total'] = $calc['grand_total'];
        $data['amount_in_words'] = $calc['amount_in_words'];

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
