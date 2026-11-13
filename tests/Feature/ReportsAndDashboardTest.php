<?php

namespace Tests\Feature;

use App\Enums\ProspectSource;
use App\Enums\ProspectStage;
use App\Enums\QuotationStatus;
use App\Enums\TargetPeriodType;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\FollowUp;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\Target;
use App\Models\User;
use App\Services\ReportService;
use App\Services\TargetAchievementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $executive1;
    protected User $executive2;
    protected Client $client1;
    protected Client $client2;

    protected function setUp(): void
    {
        parent::setUp();

        CompanySetting::getSettings();

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

        $this->client1 = Client::factory()->create([
            'company_name' => 'Assam Power Corp',
            'assigned_user_id' => $this->executive1->id,
            'state_code' => '18',
        ]);

        $this->client2 = Client::factory()->create([
            'company_name' => 'Meghalaya Cement Ltd',
            'assigned_user_id' => $this->executive2->id,
            'state_code' => '17',
        ]);
    }

    public function test_dashboard_stat_numbers_and_role_scoping(): void
    {
        $fy = '2026-27';
        $today = today();

        // 1. Executive 1 has an accepted quotation this month: ₹ 1,00,000
        Quotation::create([
            'quotation_number' => 'MSS/2026-27/0001',
            'financial_year' => $fy,
            'sequence_no' => 1,
            'client_id' => $this->client1->id,
            'client_name' => $this->client1->company_name,
            'billing_address' => 'Guwahati',
            'quotation_date' => $today,
            'valid_until' => $today->copy()->addDays(15),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => $today,
            'subtotal' => 100000.00,
            'taxable_amount' => 100000.00,
            'grand_total' => 118000.00,
            'is_latest_revision' => true,
            'created_by_user_id' => $this->executive1->id,
        ]);

        // 2. Executive 2 has an accepted quotation this month: ₹ 2,00,000
        Quotation::create([
            'quotation_number' => 'MSS/2026-27/0002',
            'financial_year' => $fy,
            'sequence_no' => 2,
            'client_id' => $this->client2->id,
            'client_name' => $this->client2->company_name,
            'billing_address' => 'Shillong',
            'quotation_date' => $today,
            'valid_until' => $today->copy()->addDays(15),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => $today,
            'subtotal' => 200000.00,
            'taxable_amount' => 200000.00,
            'grand_total' => 236000.00,
            'is_latest_revision' => true,
            'created_by_user_id' => $this->executive2->id,
        ]);

        // Check TargetAchievementService for Executive 1
        $exec1Stats = TargetAchievementService::calculateForExecutive($this->executive1, $fy, $today->month);
        $this->assertEquals(118000.00, $exec1Stats['achieved']);

        // Check TargetAchievementService for Executive 2
        $exec2Stats = TargetAchievementService::calculateForExecutive($this->executive2, $fy, $today->month);
        $this->assertEquals(236000.00, $exec2Stats['achieved']);

        // Check Firm Total for Admin: 1,18,000 + 2,36,000 = 3,54,000
        $firmStats = TargetAchievementService::calculateFirmTotal($fy, $today->month);
        $this->assertEquals(354000.00, $firmStats['achieved']);
    }

    public function test_quotation_register_report_matches_and_filters(): void
    {
        $fy = '2026-27';
        $today = today();

        Quotation::create([
            'quotation_number' => 'MSS/2026-27/0001',
            'financial_year' => $fy,
            'sequence_no' => 1,
            'client_id' => $this->client1->id,
            'client_name' => $this->client1->company_name,
            'billing_address' => 'Guwahati',
            'quotation_date' => $today,
            'valid_until' => $today->copy()->addDays(15),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => $today,
            'taxable_amount' => 50000.00,
            'total_tax' => 9000.00,
            'grand_total' => 59000.00,
            'is_latest_revision' => true,
            'created_by_user_id' => $this->executive1->id,
        ]);

        $reportAdmin = ReportService::getQuotationRegister([], $this->admin);
        $this->assertEquals(1, $reportAdmin['totals']['count']);
        $this->assertEquals(59000.00, $reportAdmin['totals']['grand_total']);
        $this->assertEquals(50000.00, $reportAdmin['totals']['taxable']);

        // Executive 2 should see 0 records
        $reportExec2 = ReportService::getQuotationRegister([], $this->executive2);
        $this->assertEquals(0, $reportExec2['totals']['count']);
    }

    public function test_csv_export_endpoint_returns_streaming_csv_with_correct_totals(): void
    {
        $fy = '2026-27';
        $today = today();

        Quotation::create([
            'quotation_number' => 'MSS/2026-27/0001',
            'financial_year' => $fy,
            'sequence_no' => 1,
            'client_id' => $this->client1->id,
            'client_name' => $this->client1->company_name,
            'billing_address' => 'Guwahati',
            'quotation_date' => $today,
            'valid_until' => $today->copy()->addDays(15),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => $today,
            'taxable_amount' => 10000.00,
            'total_tax' => 1800.00,
            'grand_total' => 11800.00,
            'is_latest_revision' => true,
            'created_by_user_id' => $this->executive1->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.export.csv', ['type' => 'quotation_register']));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('MSS/2026-27/0001', $content);
        $this->assertStringContainsString('Assam Power Corp', $content);
        $this->assertStringContainsString('11800.00', $content);
        $this->assertStringContainsString('TOTALS', $content);
    }

    public function test_excel_export_endpoint_returns_xml_spreadsheet(): void
    {
        $fy = '2026-27';
        $today = today();

        Quotation::create([
            'quotation_number' => 'MSS/2026-27/0001',
            'financial_year' => $fy,
            'sequence_no' => 1,
            'client_id' => $this->client1->id,
            'client_name' => $this->client1->company_name,
            'billing_address' => 'Guwahati',
            'quotation_date' => $today,
            'valid_until' => $today->copy()->addDays(15),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => $today,
            'taxable_amount' => 10000.00,
            'total_tax' => 1800.00,
            'grand_total' => 11800.00,
            'is_latest_revision' => true,
            'created_by_user_id' => $this->executive1->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.export.excel', ['type' => 'quotation_register']));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('urn:schemas-microsoft-com:office:spreadsheet', $content);
        $this->assertStringContainsString('MSS/2026-27/0001', $content);
        $this->assertStringContainsString('11800', $content);
    }

    public function test_unauthenticated_user_cannot_access_export(): void
    {
        $response = $this->get(route('reports.export.csv', ['type' => 'quotation_register']));
        $response->assertRedirect('/admin/login');
    }
}
