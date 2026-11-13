<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Services\GstCalculationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
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
