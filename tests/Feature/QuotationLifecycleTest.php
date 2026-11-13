<?php

namespace Tests\Feature;

use App\Enums\QuotationStatus;
use App\Models\Client;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationNumberService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class QuotationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_expiry_command_marks_overdue_quotations_as_expired(): void
    {
        $client = Client::first();
        $user = User::first();
        $numData = QuotationNumberService::generateNextNumber();

        $overdueQuotation = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'client_id' => $client->id,
            'client_name' => $client->company_name,
            'billing_address' => $client->billing_address,
            'state' => $client->state,
            'state_code' => $client->state_code,
            'quotation_date' => Carbon::today()->subDays(20),
            'valid_until' => Carbon::today()->subDays(5), // Expired 5 days ago
            'status' => QuotationStatus::Sent,
            'grand_total' => 50000.00,
            'created_by_user_id' => $user->id,
        ]);

        Artisan::call('quotations:expire');

        $overdueQuotation->refresh();
        $this->assertEquals(QuotationStatus::Expired, $overdueQuotation->status);

        // Assert activity log was created
        $this->assertTrue(
            $overdueQuotation->activities()
                ->where('to_status', QuotationStatus::Expired->value)
                ->exists()
        );
    }

    public function test_accepted_quotation_sets_accepted_at_and_is_locked(): void
    {
        $client = Client::first();
        $user = User::first();
        $numData = QuotationNumberService::generateNextNumber();

        $quote = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'client_id' => $client->id,
            'client_name' => $client->company_name,
            'billing_address' => $client->billing_address,
            'state' => $client->state,
            'state_code' => $client->state_code,
            'quotation_date' => Carbon::today(),
            'valid_until' => Carbon::today()->addDays(15),
            'status' => QuotationStatus::Draft,
            'grand_total' => 25000.00,
            'created_by_user_id' => $user->id,
        ]);

        $this->assertNull($quote->accepted_at);
        $this->assertFalse($quote->isLocked());
        $this->assertTrue($quote->canBeEdited());

        // Update to Accepted
        $quote->update(['status' => QuotationStatus::Accepted]);
        $quote->refresh();

        $this->assertNotNull($quote->accepted_at);
        $this->assertTrue($quote->isLocked());
        $this->assertFalse($quote->canBeEdited());
        $this->assertFalse($user->can('update', $quote));
    }

    public function test_executive_cannot_view_unassigned_or_uncreated_quotation(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $priya = User::where('email', 'priya@marutisales.in')->first();

        // Client assigned to Priya
        $priyaClient = Client::where('assigned_user_id', $priya->id)->first();
        $numData = QuotationNumberService::generateNextNumber();

        $priyaQuote = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'client_id' => $priyaClient->id,
            'client_name' => $priyaClient->company_name,
            'billing_address' => $priyaClient->billing_address,
            'state' => $priyaClient->state,
            'state_code' => $priyaClient->state_code,
            'quotation_date' => Carbon::today(),
            'valid_until' => Carbon::today()->addDays(15),
            'status' => QuotationStatus::Draft,
            'created_by_user_id' => $priya->id,
        ]);

        $this->assertFalse($rahul->can('view', $priyaQuote));
        $this->assertTrue($priya->can('view', $priyaQuote));
    }
}
