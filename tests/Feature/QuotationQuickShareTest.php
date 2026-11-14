<?php

namespace Tests\Feature;

use App\Enums\QuotationStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationNumberService;
use App\Services\QuotationShareService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationQuickShareTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $executive;
    protected Client $client;
    protected Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->executive = User::factory()->create([
            'role' => UserRole::SalesExecutive,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->client = Client::factory()->create([
            'company_name' => 'Assam Power Distribution Co',
            'contact_person' => 'Bipul Sarma',
            'phone' => '9854012345',
            'email' => 'bipul.sarma@apdcl.org',
            'state' => 'Assam',
            'state_code' => '18',
            'assigned_user_id' => $this->executive->id,
        ]);

        CompanySetting::getSettings();

        $numData = QuotationNumberService::generateNextNumber();

        $this->quotation = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'revision_number' => 0,
            'is_latest_revision' => true,
            'client_id' => $this->client->id,
            'client_name' => $this->client->company_name,
            'billing_address' => 'Bijulee Bhawan, Paltan Bazar, Guwahati',
            'gstin' => '18AABCA1234F1Z1',
            'state' => 'Assam',
            'state_code' => '18',
            'quotation_date' => today(),
            'valid_until' => today()->addDays(15),
            'status' => QuotationStatus::Draft,
            'subtotal' => 25000,
            'taxable_amount' => 25000,
            'is_interstate' => false,
            'cgst_amount' => 2250,
            'sgst_amount' => 2250,
            'total_tax' => 4500,
            'grand_total' => 29500,
            'amount_in_words' => 'Rupees Twenty Nine Thousand Five Hundred Only',
            'created_by_user_id' => $this->executive->id,
        ]);

        $this->quotation->items()->create([
            'item_name' => 'Hikvision 8Ch 4K NVR',
            'quantity' => 1,
            'unit' => 'Pcs',
            'unit_rate' => 12000,
            'discount_percentage' => 0,
            'gst_rate' => 18,
            'taxable_value' => 12000,
            'total_amount' => 14160,
        ]);

        $this->quotation->items()->create([
            'item_name' => 'Seagate 4TB SkyHawk HDD',
            'quantity' => 1,
            'unit' => 'Pcs',
            'unit_rate' => 13000,
            'discount_percentage' => 0,
            'gst_rate' => 18,
            'taxable_value' => 13000,
            'total_amount' => 15340,
        ]);
    }

    public function test_share_service_generates_detailed_summary(): void
    {
        $message = QuotationShareService::generateMessage($this->quotation);

        $this->assertStringContainsString('Bipul Sarma', $message);
        $this->assertStringContainsString($this->quotation->quotation_number, $message);
        $this->assertStringContainsString('₹ 29,500.00', $message);
        $this->assertStringContainsString('Hikvision 8Ch 4K NVR', $message);
        $this->assertStringContainsString('Seagate 4TB SkyHawk HDD', $message);
        $this->assertStringContainsString('State Bank of India', $message);
        $this->assertStringContainsString('38472910482', $message);
        $this->assertStringContainsString('MARUTI SALES & SERVICES', $message);
    }

    public function test_whatsapp_url_formatting(): void
    {
        $url = QuotationShareService::getWhatsAppUrl($this->quotation);

        $this->assertStringStartsWith('https://wa.me/919854012345?text=', $url);
        $this->assertStringContainsString(rawurlencode($this->quotation->quotation_number), $url);
    }

    public function test_mailto_url_formatting(): void
    {
        $url = QuotationShareService::getMailtoUrl($this->quotation);

        $this->assertStringStartsWith('mailto:bipul.sarma@apdcl.org?subject=', $url);
        $this->assertStringContainsString(rawurlencode("Quotation {$this->quotation->quotation_number} from Maruti Sales & Services"), $url);
    }

    public function test_record_share_activity_promotes_draft_to_sent(): void
    {
        $this->actingAs($this->executive);

        $this->assertEquals(QuotationStatus::Draft, $this->quotation->status);

        QuotationShareService::recordShareActivity(
            $this->quotation,
            'WhatsApp',
            '9854012345',
            markAsSent: true,
            user: $this->executive
        );

        $this->quotation->refresh();

        // Status promoted to Sent
        $this->assertEquals(QuotationStatus::Sent, $this->quotation->status);

        // Activity logged
        $activity = $this->quotation->activities()->orderByDesc('id')->first();
        $this->assertNotNull($activity);
        $this->assertStringContainsString('Quotation shared via WhatsApp to 9854012345', $activity->remarks);
        $this->assertEquals($this->executive->id, $activity->user_id);
    }

    public function test_record_share_activity_preserves_accepted_status(): void
    {
        $this->actingAs($this->admin);

        $this->quotation->update([
            'status' => QuotationStatus::Accepted,
            'accepted_at' => now(),
        ]);

        QuotationShareService::recordShareActivity(
            $this->quotation,
            'Email',
            'client@test.com',
            markAsSent: true,
            user: $this->admin
        );

        $this->quotation->refresh();

        // Does not downgrade from Accepted
        $this->assertEquals(QuotationStatus::Accepted, $this->quotation->status);

        $activity = $this->quotation->activities()->orderByDesc('id')->first();
        $this->assertStringContainsString('Quotation shared via Email to client@test.com', $activity->remarks);
    }
}
