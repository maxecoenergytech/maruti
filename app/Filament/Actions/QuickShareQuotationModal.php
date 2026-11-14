<?php

namespace App\Filament\Actions;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Services\QuotationShareService;
use Filament\Forms;
use Filament\Notifications\Notification;

class QuickShareQuotationModal
{
    /**
     * Form schema for Quick Share modal.
     */
    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Radio::make('channel')
                ->label('Choose Sharing Method')
                ->options([
                    'whatsapp' => '📱 WhatsApp (Direct chat with formatted quotation)',
                    'email' => '✉️ Email (Open pre-filled draft in mail client)',
                    'clipboard' => '📋 Copy Text (Full message summary for SMS/Chat)',
                ])
                ->default('whatsapp')
                ->live()
                ->required(),

            Forms\Components\TextInput::make('recipient_phone')
                ->label('Recipient Phone / WhatsApp Number')
                ->tel()
                ->prefix('+91')
                ->visible(fn (Forms\Get $get) => $get('channel') === 'whatsapp')
                ->required(fn (Forms\Get $get) => $get('channel') === 'whatsapp')
                ->helperText('10-digit Indian mobile number (e.g. 9876543210).'),

            Forms\Components\TextInput::make('recipient_email')
                ->label('Recipient Email Address')
                ->email()
                ->visible(fn (Forms\Get $get) => $get('channel') === 'email')
                ->required(fn (Forms\Get $get) => $get('channel') === 'email')
                ->helperText('Client email address to receive quotation.'),

            Forms\Components\Textarea::make('message_content')
                ->label('Quotation Message Text')
                ->rows(8)
                ->helperText('Review or customize the message before sending.')
                ->required(),

            Forms\Components\Toggle::make('mark_as_sent')
                ->label('Mark Status as Sent & Record Activity')
                ->default(true)
                ->helperText('Automatically transitions quotation from Draft to Sent and logs the share event.')
                ->visible(fn (?Quotation $record) => $record?->status === QuotationStatus::Draft),
        ];
    }

    /**
     * Execute share action from modal data.
     */
    public static function executeShare(array $data, Quotation $record, $livewire): void
    {
        $channel = $data['channel'] ?? 'whatsapp';
        $phone = $data['recipient_phone'] ?? null;
        $email = $data['recipient_email'] ?? null;
        $message = $data['message_content'] ?? '';
        $markAsSent = (bool) ($data['mark_as_sent'] ?? true);

        if ($channel === 'whatsapp') {
            QuotationShareService::recordShareActivity($record, 'WhatsApp', $phone, $markAsSent);
            $url = QuotationShareService::getWhatsAppUrl($record, $phone, $message);
            $livewire->js("window.open(" . json_encode($url) . ", '_blank');");

            Notification::make()
                ->title('WhatsApp Opened')
                ->body("Quotation {$record->quotation_number} prepared for WhatsApp.")
                ->success()
                ->send();
        } elseif ($channel === 'email') {
            QuotationShareService::recordShareActivity($record, 'Email', $email, $markAsSent);
            $url = QuotationShareService::getMailtoUrl($record, $email, $message);
            $livewire->js("window.open(" . json_encode($url) . ", '_blank');");

            Notification::make()
                ->title('Email Client Opened')
                ->body("Quotation {$record->quotation_number} prepared for Email.")
                ->success()
                ->send();
        } else {
            QuotationShareService::recordShareActivity($record, 'Clipboard Copy', null, $markAsSent);
            $livewire->js("navigator.clipboard.writeText(" . json_encode($message) . ");");

            Notification::make()
                ->title('Copied to Clipboard')
                ->body("Quotation summary for {$record->quotation_number} copied to clipboard.")
                ->success()
                ->send();
        }
    }
}
