<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Owner / Admin
        User::updateOrCreate(
            ['email' => 'admin@marutisales.in'],
            [
                'name' => 'Dipankar Das (Admin)',
                'password' => Hash::make('Admin@123'),
                'role' => UserRole::Admin,
                'phone' => '+91 98640 11223',
                'is_active' => true,
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Sales Executive 1
        User::updateOrCreate(
            ['email' => 'rahul@marutisales.in'],
            [
                'name' => 'Rahul Sharma',
                'password' => Hash::make('Sales@123'),
                'role' => UserRole::SalesExecutive,
                'phone' => '+91 94350 44556',
                'is_active' => true,
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Sales Executive 2
        User::updateOrCreate(
            ['email' => 'priya@marutisales.in'],
            [
                'name' => 'Priya Kalita',
                'password' => Hash::make('Sales@123'),
                'role' => UserRole::SalesExecutive,
                'phone' => '+91 97060 77889',
                'is_active' => true,
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
