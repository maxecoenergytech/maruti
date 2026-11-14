<?php

namespace App\Services;

use App\Enums\QuotationStatus;
use App\Models\CompanySetting;
use App\Models\Quotation;
use App\Models\User;
use App\Support\IndianCurrencyHelper;

class QuotationShareService
{
    /**
     * Generate a professional formatted message summary for WhatsApp, SMS, or Email.
     */
    public static function generateMessage(Quotation $quotation): string
    {
        $quotation->loadMissing(['items', 'client']);
        $company = CompanySetting::getSettings();
        $contact = $quotation->client?->contact_person ?: $quotation->client_name;
        $amount = IndianCurrencyHelper::formatRupees($quotation->grand_total);

        $lines = [];
        $lines[] = "Dear {$contact},";
        $lines[] = "";
        $lines[] = "Greetings from {$company->company_name} (Guwahati).";
        $lines[] = "Please find the details of your Quotation below:";
        $lines[] = "";
        $lines[] = "📄 *Quote No:* {$quotation->quotation_number}";
        $lines[] = "📅 *Date:* {$quotation->quotation_date->format('d-m-Y')}";
        $lines[] = "⏳ *Valid Until:* {$quotation->valid_until->format('d-m-Y')}";
        $lines[] = "💰 *Total Amount:* {$amount} (Inclusive of GST)";

        if ($quotation->items->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "📦 *Item Summary:*";
            foreach ($quotation->items->take(5) as $index => $item) {
                $itemTotal = IndianCurrencyHelper::formatRupees($item->total_amount);
                $lines[] = "  " . ($index + 1) . ". {$item->item_name} (Qty: " . ((float) $item->quantity) . " {$item->unit}) — {$itemTotal}";
            }
            if ($quotation->items->count() > 5) {
                $remaining = $quotation->items->count() - 5;
                $lines[] = "  ... and {$remaining} more item(s)";
            }
        }

        $lines[] = "";
        $lines[] = "🏦 *Bank Details for Remittance:*";
        $lines[] = "• Bank: " . ($company->bank_name ?: 'State Bank of India');
        $lines[] = "• A/C Name: " . ($company->bank_account_name ?: $company->company_name);
        $lines[] = "• A/C No: " . ($company->bank_account_no ?: '38472910482');
        $lines[] = "• IFSC: " . ($company->bank_ifsc ?: 'SBIN0003295');
        $lines[] = "• Branch: " . ($company->bank_branch ?: 'G.S. Road, Guwahati');

        $lines[] = "";
        $lines[] = "For any queries or orders, reach us at: {$company->phone} | {$company->email}";
        $lines[] = "";
        $lines[] = "Thank you for choosing {$company->company_name}!";

        return implode("\n", $lines);
    }

    /**
     * Generate direct WhatsApp Web / App link.
     */
    public static function getWhatsAppUrl(Quotation $quotation, ?string $overridePhone = null, ?string $customMessage = null): string
    {
        $phone = $overridePhone ?: ($quotation->client?->phone ?? '');
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $message = $customMessage ?: self::generateMessage($quotation);

        return "https://wa.me/{$cleanPhone}?text=" . rawurlencode($message);
    }

    /**
     * Generate mailto: link for sending via user's email client.
     */
    public static function getMailtoUrl(Quotation $quotation, ?string $overrideEmail = null, ?string $customMessage = null): string
    {
        $email = $overrideEmail ?: ($quotation->client?->email ?? '');
        $subject = "Quotation {$quotation->quotation_number} from Maruti Sales & Services";
        $body = $customMessage ?: self::generateMessage($quotation);

        return "mailto:{$email}?subject=" . rawurlencode($subject) . "&body=" . rawurlencode($body);
    }

    /**
     * Record a share activity in the database and optionally promote Draft -> Sent.
     */
    public static function recordShareActivity(
        Quotation $quotation,
        string $channel,
        ?string $recipient = null,
        bool $markAsSent = true,
        ?User $user = null
    ): void {
        $user ??= auth()->user();
        $target = $recipient ? " to {$recipient}" : "";
        $remarks = "Quotation shared via {$channel}{$target}";

        if ($markAsSent && $quotation->status === QuotationStatus::Draft) {
            request()->merge(['activity_remarks' => $remarks]);
            $quotation->status = QuotationStatus::Sent;
            $quotation->save();
        } else {
            $quotation->activities()->create([
                'user_id' => $user?->id,
                'from_status' => $quotation->status->value,
                'to_status' => $quotation->status->value,
                'remarks' => $remarks,
            ]);
        }
    }
}
