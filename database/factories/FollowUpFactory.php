<?php

namespace Database\Factories;

use App\Enums\FollowUpStatus;
use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowUpFactory extends Factory
{
    protected $model = FollowUp::class;

    public function definition(): array
    {
        return [
            'subject' => fake()->randomElement([
                'Call to discuss revised quotation and pricing discount.',
                'Product demo at client premises in Guwahati.',
                'Send product catalog and OEM warranty details.',
                'Follow up on PO signing and advance payment token.',
                'Site survey for CCTV cable routing and network points.',
            ]),
            'assigned_user_id' => User::factory(),
            'client_id' => null,
            'prospect_id' => Prospect::factory(),
            'quotation_id' => null,
            'due_date' => fake()->dateTimeBetween('-5 days', '+10 days')->format('Y-m-d'),
            'due_time' => fake()->time('H:i'),
            'status' => fake()->randomElement(FollowUpStatus::cases()),
            'outcome_notes' => fake()->sentence(),
        ];
    }
}
