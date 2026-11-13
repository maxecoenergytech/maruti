<?php

namespace App\Console\Commands;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireQuotationsCommand extends Command
{
    protected $signature = 'quotations:expire';

    protected $description = 'Automatically mark overdue Sent and Under Negotiation quotations as Expired';

    public function handle(): int
    {
        $today = Carbon::today();

        $expiredQuotations = Quotation::whereIn('status', [
            QuotationStatus::Sent,
            QuotationStatus::UnderNegotiation,
        ])
            ->whereDate('valid_until', '<', $today)
            ->get();

        $count = 0;

        foreach ($expiredQuotations as $quotation) {
            $fromStatus = $quotation->status->value;

            $quotation->status = QuotationStatus::Expired;
            $quotation->save();

            $quotation->activities()->create([
                'user_id' => null,
                'from_status' => $fromStatus,
                'to_status' => QuotationStatus::Expired->value,
                'remarks' => 'System: Automatically marked expired after validity period (' . $quotation->valid_until->format('d-m-Y') . ')',
            ]);

            $count++;
        }

        $this->info("Expired {$count} quotation(s) successfully.");

        return Command::SUCCESS;
    }
}
