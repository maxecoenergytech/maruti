<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_person' => fake()->name(),
            'phone' => '+91 ' . fake()->numerify('98########'),
            'email' => fake()->safeEmail(),
            'gstin' => '18' . strtoupper(fake()->bothify('?????####?')) . '1Z' . fake()->randomDigit(),
            'billing_address' => fake()->streetAddress() . ', Guwahati, Assam',
            'shipping_address' => null,
            'state' => 'Assam',
            'state_code' => '18',
            'assigned_user_id' => User::factory(),
            'notes' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
