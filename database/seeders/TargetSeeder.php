<?php

namespace Database\Seeders;

use App\Enums\TargetPeriodType;
use App\Models\Target;
use App\Models\User;
use Illuminate\Database\Seeder;

class TargetSeeder extends Seeder
{
    public function run(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $priya = User::where('email', 'priya@marutisales.in')->first();

        if (!$rahul || !$priya) {
            return;
        }

        $fy = '2026-27';

        // 1. Rahul - Yearly target
        Target::updateOrCreate(
            [
                'user_id' => $rahul->id,
                'period_type' => TargetPeriodType::Yearly,
                'financial_year' => $fy,
                'month' => 0,
            ],
            [
                'target_amount' => 6000000.00,
                'notes' => 'Annual corporate sales and government IT infrastructure target for FY 2026-27',
            ]
        );

        // 1. Rahul - Monthly targets
        $rahulMonthly = [
            4 => 450000.00,
            5 => 450000.00,
            6 => 500000.00,
            7 => 500000.00,
            8 => 500000.00,
            9 => 500000.00,
            10 => 600000.00, // Durga Puja / Diwali
            11 => 600000.00,
            12 => 500000.00,
            1 => 450000.00,
            2 => 450000.00,
            3 => 500000.00,
        ];

        foreach ($rahulMonthly as $month => $amount) {
            Target::updateOrCreate(
                [
                    'user_id' => $rahul->id,
                    'period_type' => TargetPeriodType::Monthly,
                    'financial_year' => $fy,
                    'month' => $month,
                ],
                [
                    'target_amount' => $amount,
                    'notes' => "Monthly target for {$month}/{$fy}",
                ]
            );
        }

        // 2. Priya - Yearly target
        Target::updateOrCreate(
            [
                'user_id' => $priya->id,
                'period_type' => TargetPeriodType::Yearly,
                'financial_year' => $fy,
                'month' => 0,
            ],
            [
                'target_amount' => 5000000.00,
                'notes' => 'Annual retail, security, CCTV, and AMC contract sales target for FY 2026-27',
            ]
        );

        // 2. Priya - Monthly targets
        $priyaMonthly = [
            4 => 400000.00,
            5 => 400000.00,
            6 => 400000.00,
            7 => 400000.00,
            8 => 400000.00,
            9 => 450000.00,
            10 => 550000.00,
            11 => 550000.00,
            12 => 450000.00,
            1 => 350000.00,
            2 => 350000.00,
            3 => 450000.00,
        ];

        foreach ($priyaMonthly as $month => $amount) {
            Target::updateOrCreate(
                [
                    'user_id' => $priya->id,
                    'period_type' => TargetPeriodType::Monthly,
                    'financial_year' => $fy,
                    'month' => $month,
                ],
                [
                    'target_amount' => $amount,
                    'notes' => "Monthly target for {$month}/{$fy}",
                ]
            );
        }
    }
}
