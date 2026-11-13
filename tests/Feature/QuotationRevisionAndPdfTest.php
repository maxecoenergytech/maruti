<?php

namespace Tests\Feature;

use App\Enums\QuotationStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationRevisionAndPdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $executive1;
    protected User $executive2;
    protected Client $clientAssam;
    protected Client $clientInterstate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->executive1 = User::factory()->create([
            'role' => UserRole::SalesExecutive,
            'is_active' => true,
        ]);

        $this->executive2 = User::factory()->create([
            'role' => UserRole::SalesExecutive,
            'is_active' => true,
        ]);

        $this->clientAssam = Client::factory()->create([
            'company_name' => 'Assam Power Corp',
            'state' => 'Assam',
            'state_code' => '18',
            'assigned_user_id' => $this->executive1->id,
        ]);

        $this->clientInterstate = Client::factory()->create([
            'company_name' => 'Meghalaya Cement Ltd',
            'state' => 'Meghalaya',
            'state_code' => '17',
            'assigned_user_id' => $this->executive2->id,
        ]);

        CompanySetting::getSettings();
    }

    public function test_can_create_revision_from_original_quotation(): void
    {
        $numData = QuotationNumberService::generateNextNumber();

        $quotation = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'revision_number' => 0,
            'is_latest_revision' => true,
            'client_id' => $this->clientAssam->id,
            'client_name' => $this->clientAssam->company_name,
            'billing_address' => $this->clientAssam->billing_address,
            'gstin' => $this->clientAssam->gstin,
            'state' => $this->clientAssam->state,
            'state_code' => $this->clientAssam->state_code,
            'quotation_date' => today(),
            'valid_until' => today()->addDays(15),
            'status' => QuotationStatus::Sent,
            'subtotal' => 10000,
            'taxable_amount' => 10000,
            'is_interstate' => false,
            'cgst_amount' => 900,
            'sgst_amount' => 900,
            'total_tax' => 1800,
            'grand_total' => 11800,
            'created_by_user_id' => $this->executive1->id,
        ]);

        $quotation->items()->create([
            'item_name' => 'Quick Heal Total Security',
            'quantity' => 10,
            'unit' => 'Pcs',
            'unit_rate' => 1000,
            'discount_percentage' => 0,
            'gst_rate' => 18,
            'taxable_value' => 10000,
            'total_amount' => 11800,
        ]);

        // Create revision R1
        $revision1 = $quotation->createRevision($this->executive1);

        $this->assertEquals("{$numData['quotation_number']}/R1", $revision1->quotation_number);
        $this->assertEquals(1, $revision1->revision_number);
        $this->assertTrue($revision1->is_latest_revision);
        $this->assertEquals(QuotationStatus::Draft, $revision1->status);
        $this->assertEquals($quotation->id, $revision1->parent_quotation_id);
        $this->assertCount(1, $revision1->items);

        // Original is now no longer latest revision
        $this->assertFalse($quotation->fresh()->is_latest_revision);

        // Create revision R2 from R1
        $revision2 = $revision1->createRevision($this->executive1);

        $this->assertEquals("{$numData['quotation_number']}/R2", $revision2->quotation_number);
        $this->assertEquals(2, $revision2->revision_number);
        $this->assertTrue($revision2->is_latest_revision);
        $this->assertFalse($revision1->fresh()->is_latest_revision);
        $this->assertFalse($quotation->fresh()->is_latest_revision);
    }

    public function test_accepted_quotation_can_still_be_revised(): void
    {
        $numData = QuotationNumberService::generateNextNumber();

        $quotation = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'revision_number' => 0,
            'is_latest_revision' => true,
            'client_id' => $this->clientAssam->id,
            'client_name' => $this->clientAssam->company_name,
            'billing_address' => 'Paltan Bazar, Guwahati',
            'state_code' => '18',
            'quotation_date' => today(),
            'valid_until' => today()->addDays(15),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => now(),
            'subtotal' => 50000,
            'taxable_amount' => 50000,
            'grand_total' => 59000,
            'created_by_user_id' => $this->executive1->id,
        ]);

        $this->assertTrue($quotation->isLocked());
        $this->assertFalse($quotation->canBeEdited());

        // Revision should succeed even if parent is accepted/locked
        $revision = $quotation->createRevision($this->executive1);
        $this->assertEquals("{$numData['quotation_number']}/R1", $revision->quotation_number);
        $this->assertEquals(QuotationStatus::Draft, $revision->status);
        $this->assertTrue($revision->canBeEdited());
    }

    public function test_pdf_preview_and_download_for_authorized_users(): void
    {
        $numData = QuotationNumberService::generateNextNumber();

        $quotation = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'client_id' => $this->clientAssam->id,
            'client_name' => $this->clientAssam->company_name,
            'billing_address' => 'Guwahati, Assam',
            'gstin' => '18ABCDE1234F1Z5',
            'state' => 'Assam',
            'state_code' => '18',
            'quotation_date' => today(),
            'valid_until' => today()->addDays(15),
            'status' => QuotationStatus::Draft,
            'subtotal' => 20000,
            'discount_amount' => 0,
            'taxable_amount' => 20000,
            'is_interstate' => false,
            'cgst_amount' => 1800,
            'sgst_amount' => 1800,
            'igst_amount' => 0,
            'total_tax' => 3600,
            'grand_total' => 23600,
            'amount_in_words' => 'Rupees Twenty Three Thousand Six Hundred Only',
            'terms_and_conditions' => 'Payment 100% advance.',
            'created_by_user_id' => $this->executive1->id,
        ]);

        $quotation->items()->create([
            'item_name' => 'Dell Inspiron Laptop',
            'description' => 'Core i5 12th Gen, 16GB RAM, 512GB SSD',
            'hsn_sac' => '8471',
            'quantity' => 1,
            'unit' => 'Pcs',
            'unit_rate' => 20000,
            'discount_percentage' => 0,
            'gst_rate' => 18,
            'taxable_value' => 20000,
            'total_amount' => 23600,
        ]);

        // Preview by assigned executive
        $response = $this->actingAs($this->executive1)
            ->get(route('quotations.pdf.preview', $quotation));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));

        // Download by admin
        $responseAdmin = $this->actingAs($this->admin)
            ->get(route('quotations.pdf.download', $quotation));

        $responseAdmin->assertStatus(200);
        $this->assertStringContainsString('attachment;', $responseAdmin->headers->get('Content-Disposition'));
    }

    public function test_pdf_access_denied_for_unauthorized_executive(): void
    {
        $numData = QuotationNumberService::generateNextNumber();

        $quotation = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'client_id' => $this->clientAssam->id, // Assigned to executive1
            'client_name' => $this->clientAssam->company_name,
            'billing_address' => 'Paltan Bazar, Guwahati',
            'state_code' => '18',
            'quotation_date' => today(),
            'valid_until' => today()->addDays(15),
            'status' => QuotationStatus::Draft,
            'grand_total' => 5000,
            'created_by_user_id' => $this->executive1->id,
        ]);

        // executive2 has no connection to this client or quotation
        $response = $this->actingAs($this->executive2)
            ->get(route('quotations.pdf.preview', $quotation));

        $response->assertStatus(403);
    }

    public function test_interstate_quotation_pdf_renders_with_igst(): void
    {
        $numData = QuotationNumberService::generateNextNumber();

        $quotation = Quotation::create([
            'quotation_number' => $numData['quotation_number'],
            'financial_year' => $numData['financial_year'],
            'sequence_no' => $numData['sequence_no'],
            'client_id' => $this->clientInterstate->id,
            'client_name' => $this->clientInterstate->company_name,
            'billing_address' => 'Shillong, Meghalaya',
            'gstin' => '17ABCDE1234F1Z5',
            'state' => 'Meghalaya',
            'state_code' => '17',
            'quotation_date' => today(),
            'valid_until' => today()->addDays(15),
            'status' => QuotationStatus::Sent,
            'subtotal' => 10000,
            'taxable_amount' => 10000,
            'is_interstate' => true,
            'cgst_amount' => 0,
            'sgst_amount' => 0,
            'igst_amount' => 1800,
            'total_tax' => 1800,
            'grand_total' => 11800,
            'amount_in_words' => 'Rupees Eleven Thousand Eight Hundred Only',
            'created_by_user_id' => $this->executive2->id,
        ]);

        $quotation->items()->create([
            'item_name' => 'CCTV Surveillance Camera 4MP',
            'hsn_sac' => '8525',
            'quantity' => 2,
            'unit' => 'Pcs',
            'unit_rate' => 5000,
            'discount_percentage' => 0,
            'gst_rate' => 18,
            'taxable_value' => 10000,
            'total_amount' => 11800,
        ]);

        $response = $this->actingAs($this->executive2)
            ->get(route('quotations.pdf.preview', $quotation));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
