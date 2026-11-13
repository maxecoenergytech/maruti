<?php

namespace Database\Seeders;

use App\Enums\QuotationStatus;
use App\Models\Client;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use App\Services\GstCalculationService;
use App\Services\QuotationNumberService;
use App\Support\IndianCurrencyHelper;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@marutisales.in')->first();
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $priya = User::where('email', 'priya@marutisales.in')->first();

        $clients = Client::all();
        $products = Product::all();

        if ($clients->isEmpty() || $products->isEmpty()) {
            return;
        }

        $now = Carbon::today();

        // 3 special guaranteed sample quotations first:
        // Sample 1: Assam client with multiple items
        $c1 = Client::where('company_name', 'Pragjyotish Power Solutions Pvt Ltd')->first() ?: $clients[0];
        $this->createQuotationWithItems(
            client: $c1,
            user: $rahul,
            date: $now->copy()->subDays(5),
            validDays: 15,
            status: QuotationStatus::Sent,
            itemConfigs: [
                ['product_name' => 'Dell OptiPlex 7010 Desktop PC (i5 13th Gen, 16GB, 512GB SSD, Win 11 Pro)', 'qty' => 4, 'disc' => 5],
                ['product_name' => 'Microtek Legend 1000VA / 12V Line Interactive UPS', 'qty' => 4, 'disc' => 0],
            ]
        );

        // Sample 2: Other state client (Meghalaya, IGST!)
        $c2 = Client::where('state_code', '17')->first() ?: $clients[1];
        $this->createQuotationWithItems(
            client: $c2,
            user: $priya,
            date: $now->copy()->subDays(3),
            validDays: 15,
            status: QuotationStatus::UnderNegotiation,
            itemConfigs: [
                ['product_name' => 'Hikvision 4MP Smart Hybrid Light IP Dome Camera (DS-2CD1143G2-LIU)', 'qty' => 12, 'disc' => 0],
                ['product_name' => 'Hikvision 16-Channel 4K NVR (DS-7616NI-K2)', 'qty' => 1, 'disc' => 0],
                ['product_name' => 'Western Digital Purple 4TB Surveillance Internal Hard Disk', 'qty' => 2, 'disc' => 5],
                ['product_name' => 'TP-Link Omada 24-Port Gigabit Smart PoE+ Switch (TL-SG2428P)', 'qty' => 1, 'disc' => 0],
            ]
        );

        // Sample 3: Assam client with Accepted status
        $c3 = Client::where('company_name', 'Kaziranga Security & Patrol Services')->first() ?: $clients[2];
        $this->createQuotationWithItems(
            client: $c3,
            user: $rahul,
            date: $now->copy()->subDays(10),
            validDays: 15,
            status: QuotationStatus::Accepted,
            itemConfigs: [
                ['product_name' => 'HP LaserJet Pro MFP M126nw Multi-Function Network Printer', 'qty' => 2, 'disc' => 3],
                ['product_name' => 'TallyPrime Gold Multi-User Unlimited LAN License', 'qty' => 1, 'disc' => 5],
                ['product_name' => 'Quick Heal Total Security Antivirus 3 User 3 Years License', 'qty' => 2, 'disc' => 0],
            ],
            acceptedAt: $now->copy()->subDays(2)
        );

        // 17 more realistic quotations to make 20 total
        $remainingStatuses = [
            QuotationStatus::Draft,
            QuotationStatus::Sent,
            QuotationStatus::Accepted,
            QuotationStatus::UnderNegotiation,
            QuotationStatus::Rejected,
            QuotationStatus::Expired,
        ];

        for ($i = 4; $i <= 20; $i++) {
            $client = $clients->random();
            $user = ($client->assigned_user_id === $priya->id) ? $priya : (($client->assigned_user_id === $admin->id) ? $admin : $rahul);
            $status = $remainingStatuses[($i) % count($remainingStatuses)];
            $subDays = ($i * 2);
            $date = $now->copy()->subDays($subDays);
            $validDays = ($status === QuotationStatus::Expired) ? 10 : 20;
            $acceptedAt = ($status === QuotationStatus::Accepted) ? $date->copy()->addDays(3) : null;

            $randomProduct1 = $products->random();
            $randomProduct2 = $products->where('id', '!=', $randomProduct1->id)->random();

            $this->createQuotationWithItems(
                client: $client,
                user: $user,
                date: $date,
                validDays: $validDays,
                status: $status,
                itemConfigs: [
                    ['product_name' => $randomProduct1->name, 'qty' => rand(1, 5), 'disc' => rand(0, 8)],
                    ['product_name' => $randomProduct2->name, 'qty' => rand(1, 3), 'disc' => 0],
                ],
                acceptedAt: $acceptedAt
            );
        }
    }

    private function createQuotationWithItems(
        Client $client,
        User $user,
        Carbon $date,
        int $validDays,
        QuotationStatus $status,
        array $itemConfigs,
        ?Carbon $acceptedAt = null
    ): void {
        $numData = QuotationNumberService::generateNextNumber($date);

        $lineItems = [];
        foreach ($itemConfigs as $config) {
            $product = Product::where('name', $config['product_name'])->first() ?? Product::first();
            $qty = $config['qty'];
            $disc = $config['disc'];

            $lineItems[] = [
                'product_id' => $product->id,
                'item_name' => $product->name,
                'description' => $product->description,
                'hsn_sac' => $product->hsn_sac,
                'quantity' => $qty,
                'unit' => $product->unit,
                'unit_rate' => (float) $product->default_rate,
                'discount_percent' => (float) $disc,
                'gst_rate' => (float) $product->gst_rate,
            ];
        }

        $calc = GstCalculationService::calculate($lineItems, $client->state_code);

        $quotation = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'revision_number' => 0,
            'is_latest_revision' => true,
            'client_id' => $client->id,
            'client_name' => $client->company_name,
            'billing_address' => $client->billing_address,
            'shipping_address' => $client->shipping_address,
            'gstin' => $client->gstin,
            'state' => $client->state,
            'state_code' => $client->state_code,
            'quotation_date' => $date,
            'valid_until' => $date->copy()->addDays($validDays),
            'accepted_at' => $acceptedAt,
            'status' => $status,
            'subtotal' => $calc['subtotal'],
            'discount_amount' => $calc['discount_amount'],
            'taxable_amount' => $calc['taxable_amount'],
            'is_interstate' => $calc['is_interstate'],
            'cgst_amount' => $calc['cgst_amount'],
            'sgst_amount' => $calc['sgst_amount'],
            'igst_amount' => $calc['igst_amount'],
            'total_tax' => $calc['total_tax'],
            'round_off' => $calc['round_off'],
            'grand_total' => $calc['grand_total'],
            'amount_in_words' => $calc['amount_in_words'],
            'terms_and_conditions' => \App\Models\CompanySetting::getSettings()->quotation_default_terms,
            'notes' => 'Quotation generated by sales desk for official procurement review.',
            'created_by_user_id' => $user->id,
        ]);

        foreach ($calc['items'] as $item) {
            $quotation->items()->create($item);
        }

        $quotation->activities()->create([
            'user_id' => $user->id,
            'from_status' => null,
            'to_status' => $status->value,
            'remarks' => "Quotation created in {$status->value} status with grand total " . IndianCurrencyHelper::formatRupees($quotation->grand_total),
        ]);
    }
}
