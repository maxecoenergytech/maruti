<?php

namespace Database\Factories;

use App\Enums\ProspectSource;
use App\Enums\ProspectStage;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProspectFactory extends Factory
{
    protected $model = Prospect::class;

    public function definition(): array
    {
        return [
            'contact_person' => fake()->name(),
            'company_name' => fake()->boolean(70) ? fake()->company() : null,
            'phone' => '+91 ' . fake()->numerify('98########'),
            'email' => fake()->boolean(60) ? fake()->safeEmail() : null,
            'source' => fake()->randomElement(ProspectSource::cases()),
            'requirement' => fake()->randomElement([
                'Need 16-channel 4K CCTV system with 30 days backup for tea garden bungalow.',
                'Dell OptiPlex desktop computers (5 units) with UPS and quick installation.',
                'Fortinet Firewall and Managed Wi-Fi router setup for corporate branch.',
                'Annual Maintenance Contract (AMC) for 25 office computers and printers.',
                'Interactive Flat Panel Display (75 inch) for smart school classroom.',
                'Biometric Fingerprint and Facial recognition attendance system with battery backup.',
            ]),
            'expected_value' => fake()->randomFloat(2, 15000, 350000),
            'stage' => fake()->randomElement(ProspectStage::cases()),
            'lost_reason' => null,
            'next_follow_up_date' => fake()->boolean(80) ? fake()->dateTimeBetween('-7 days', '+14 days')->format('Y-m-d') : null,
            'assigned_user_id' => User::factory(),
            'converted_client_id' => null,
            'converted_at' => null,
        ];
    }
}
