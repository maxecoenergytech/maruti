<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CompanySettingSeeder::class,
            ProductSeeder::class,
            ClientSeeder::class,
            ProspectSeeder::class,
            FollowUpSeeder::class,
            QuotationSeeder::class,
            TargetSeeder::class,
        ]);
    }
}
