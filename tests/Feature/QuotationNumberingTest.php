<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationNumberService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationNumberingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_financial_year_calculation_resets_on_april_first(): void
    {
        // 31st March 2026 is part of 2025-26 FY
        $marchDate = Carbon::create(2026, 3, 31);
        $this->assertEquals('2025-26', QuotationNumberService::getFinancialYear($marchDate));

        // 1st April 2026 resets to 2026-27 FY
        $aprilDate = Carbon::create(2026, 4, 1);
        $this->assertEquals('2026-27', QuotationNumberService::getFinancialYear($aprilDate));

        // November 2026 is in 2026-27 FY
        $novDate = Carbon::create(2026, 11, 13);
        $this->assertEquals('2026-27', QuotationNumberService::getFinancialYear($novDate));

        // 15th January 2027 is still in 2026-27 FY
        $janDate = Carbon::create(2027, 1, 15);
        $this->assertEquals('2026-27', QuotationNumberService::getFinancialYear($janDate));
    }

    public function test_sequential_number_generation_starts_at_0001(): void
    {
        // Use fresh FY 2029-30 to test from sequence 1
        $date = Carbon::create(2029, 4, 5);
        $gen1 = QuotationNumberService::generateNextNumber($date);

        $this->assertEquals('2029-30', $gen1['financial_year']);
        $this->assertEquals(1, $gen1['sequence_no']);
        $this->assertEquals('MSS/2029-30/0001', $gen1['quotation_number']);

        // Create the quotation in DB
        $client = Client::first();
        $user = User::first();

        $quote1 = Quotation::create([
            'quotation_number' => $gen1['quotation_number'],
            'financial_year' => $gen1['financial_year'],
            'sequence_no' => $gen1['sequence_no'],
            'client_id' => $client->id,
            'client_name' => $client->company_name,
            'billing_address' => $client->billing_address,
            'state' => $client->state,
            'state_code' => $client->state_code,
            'quotation_date' => $date,
            'valid_until' => $date->copy()->addDays(15),
            'created_by_user_id' => $user->id,
        ]);

        // Next number must be 0002
        $gen2 = QuotationNumberService::generateNextNumber($date);
        $this->assertEquals(2, $gen2['sequence_no']);
        $this->assertEquals('MSS/2029-30/0002', $gen2['quotation_number']);
    }

    public function test_soft_deleted_quotation_numbers_are_not_reused(): void
    {
        // Use fresh FY 2030-31
        $date = Carbon::create(2030, 5, 10);
        $gen1 = QuotationNumberService::generateNextNumber($date);
        $client = Client::first();
        $user = User::first();

        $quote1 = Quotation::create([
            'quotation_number' => $gen1['quotation_number'],
            'financial_year' => $gen1['financial_year'],
            'sequence_no' => $gen1['sequence_no'],
            'client_id' => $client->id,
            'client_name' => $client->company_name,
            'billing_address' => $client->billing_address,
            'state' => $client->state,
            'state_code' => $client->state_code,
            'quotation_date' => $date,
            'valid_until' => $date->copy()->addDays(15),
            'created_by_user_id' => $user->id,
        ]);

        // Soft delete the quotation
        $quote1->delete();
        $this->assertSoftDeleted('quotations', ['id' => $quote1->id]);

        // Next generated number must count soft-deleted rows and return 0002
        $gen2 = QuotationNumberService::generateNextNumber($date);
        $this->assertEquals(2, $gen2['sequence_no']);
        $this->assertEquals('MSS/2030-31/0002', $gen2['quotation_number']);
    }
}
