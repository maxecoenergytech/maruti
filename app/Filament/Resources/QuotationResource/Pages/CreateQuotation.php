<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Models\Client;
use App\Services\GstCalculationService;
use App\Services\QuotationNumberService;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $quoteDate = !empty($data['quotation_date']) ? Carbon::parse($data['quotation_date']) : Carbon::today();

        // 1. Generate unique sequence number
        $numberData = QuotationNumberService::generateNextNumber($quoteDate);
        $data['quotation_number'] = $numberData['quotation_number'];
        $data['financial_year'] = $numberData['financial_year'];
        $data['sequence_no'] = $numberData['sequence_no'];
        $data['revision_number'] = 0;
        $data['is_latest_revision'] = true;

        // 2. Client snapshot
        if (!empty($data['client_id'])) {
            $client = Client::find($data['client_id']);
            if ($client) {
                $data['client_name'] = $client->company_name;
                $data['billing_address'] = $client->billing_address;
                $data['shipping_address'] = $client->shipping_address;
                $data['gstin'] = $client->gstin;
                $data['state'] = $client->state;
                $data['state_code'] = $client->state_code;
            }
        }

        // 3. User
        if (!auth()->user()->isAdmin()) {
            $data['created_by_user_id'] = auth()->id();
        }

        // 4. Calculations
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

    protected function afterCreate(): void
    {
        $record = $this->record;
        $record->activities()->create([
            'user_id' => auth()->id(),
            'from_status' => null,
            'to_status' => $record->status->value,
            'remarks' => "Quotation created with total " . \App\Support\IndianCurrencyHelper::formatRupees($record->grand_total),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
