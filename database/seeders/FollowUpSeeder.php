<?php

namespace Database\Seeders;

use App\Enums\FollowUpStatus;
use App\Models\Client;
use App\Models\FollowUp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FollowUpSeeder extends Seeder
{
    public function run(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $priya = User::where('email', 'priya@marutisales.in')->first();

        $client1 = Client::where('company_name', 'Pragjyotish Power Solutions Pvt Ltd')->first();
        $client2 = Client::where('company_name', 'Brahmaputra Tea Estates Ltd')->first();
        $client3 = Client::where('company_name', 'Kaziranga Security & Patrol Services')->first();

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $twoDaysAgo = Carbon::today()->subDays(2);
        $tomorrow = Carbon::tomorrow();

        $followUps = [
            [
                'subject' => 'Quarterly server maintenance and UPS battery health check',
                'assigned_user_id' => $rahul->id,
                'client_id' => $client1?->id,
                'prospect_id' => null,
                'due_date' => $twoDaysAgo->format('Y-m-d'), // Overdue!
                'due_time' => '11:00:00',
                'status' => FollowUpStatus::Pending,
                'outcome_notes' => null,
            ],
            [
                'subject' => 'Call manager for CCTV storage expansion quote feedback',
                'assigned_user_id' => $priya->id,
                'client_id' => $client2?->id,
                'prospect_id' => null,
                'due_date' => $today->format('Y-m-d'), // Due today!
                'due_time' => '14:30:00',
                'status' => FollowUpStatus::Pending,
                'outcome_notes' => null,
            ],
            [
                'subject' => 'Annual AMC renewal contract discussion at client office',
                'assigned_user_id' => $rahul->id,
                'client_id' => $client3?->id,
                'prospect_id' => null,
                'due_date' => $yesterday->format('Y-m-d'),
                'due_time' => '16:00:00',
                'status' => FollowUpStatus::Completed,
                'outcome_notes' => 'Client agreed to renew AMC for 1 year with 5% escalation. PO to follow.',
            ],
            [
                'subject' => 'Deliver replacement D-Link Wi-Fi router adapter',
                'assigned_user_id' => $rahul->id,
                'client_id' => $client1?->id,
                'prospect_id' => null,
                'due_date' => $tomorrow->format('Y-m-d'),
                'due_time' => '12:00:00',
                'status' => FollowUpStatus::Pending,
                'outcome_notes' => null,
            ],
        ];

        foreach ($followUps as $item) {
            FollowUp::create($item);
        }
    }
}
