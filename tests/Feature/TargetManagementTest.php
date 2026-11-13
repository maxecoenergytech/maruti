<?php

namespace Tests\Feature;

use App\Enums\QuotationStatus;
use App\Enums\TargetPeriodType;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Quotation;
use App\Models\Target;
use App\Models\User;
use App\Services\TargetAchievementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TargetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $executive1;
    protected User $executive2;
    protected Client $client;

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

        $this->client = Client::factory()->create([
            'assigned_user_id' => $this->executive1->id,
            'state_code' => '18',
        ]);
    }

    public function test_target_achievement_counts_only_accepted_and_latest_revision(): void
    {
        $fy = '2026-27';

        // 1. Target for Executive 1 for November 2026 (Month 11): ₹ 5,00,000
        $target = Target::create([
            'user_id' => $this->executive1->id,
            'period_type' => TargetPeriodType::Monthly,
            'financial_year' => $fy,
            'month' => 11,
            'target_amount' => 500000.00,
        ]);

        // 2. Draft quotation in Nov 2026: ₹ 1,00,000 (should NOT count)
        Quotation::create([
            'quotation_number' => 'MSS/2026-27/0001',
            'financial_year' => $fy,
            'sequence_no' => 1,
            'client_id' => $this->client->id,
            'client_name' => $this->client->company_name,
            'billing_address' => 'Guwahati',
            'quotation_date' => Carbon::create(2026, 11, 5),
            'valid_until' => Carbon::create(2026, 11, 20),
            'status' => QuotationStatus::Draft,
            'grand_total' => 100000.00,
            'is_latest_revision' => true,
            'created_by_user_id' => $this->executive1->id,
        ]);

        // 3. Accepted quotation in Nov 2026: ₹ 2,00,000 (SHOULD count)
        Quotation::create([
            'quotation_number' => 'MSS/2026-27/0002',
            'financial_year' => $fy,
            'sequence_no' => 2,
            'client_id' => $this->client->id,
            'client_name' => $this->client->company_name,
            'billing_address' => 'Guwahati',
            'quotation_date' => Carbon::create(2026, 11, 6),
            'valid_until' => Carbon::create(2026, 11, 21),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => Carbon::create(2026, 11, 8, 14, 30),
            'grand_total' => 200000.00,
            'is_latest_revision' => true,
            'created_by_user_id' => $this->executive1->id,
        ]);

        // 4. Accepted quotation that is NOT latest revision: ₹ 1,50,000 (should NOT count, superseded by revision)
        Quotation::create([
            'quotation_number' => 'MSS/2026-27/0003',
            'financial_year' => $fy,
            'sequence_no' => 3,
            'client_id' => $this->client->id,
            'client_name' => $this->client->company_name,
            'billing_address' => 'Guwahati',
            'quotation_date' => Carbon::create(2026, 11, 7),
            'valid_until' => Carbon::create(2026, 11, 22),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => Carbon::create(2026, 11, 9, 10, 0),
            'grand_total' => 150000.00,
            'is_latest_revision' => false,
            'created_by_user_id' => $this->executive1->id,
        ]);

        // 5. Revision of quotation 3 that is Accepted and latest revision: ₹ 1,80,000 (SHOULD count)
        Quotation::create([
            'quotation_number' => 'MSS/2026-27/0003/R1',
            'financial_year' => $fy,
            'sequence_no' => 3,
            'revision_number' => 1,
            'client_id' => $this->client->id,
            'client_name' => $this->client->company_name,
            'billing_address' => 'Guwahati',
            'quotation_date' => Carbon::create(2026, 11, 10),
            'valid_until' => Carbon::create(2026, 11, 25),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => Carbon::create(2026, 11, 12, 11, 0),
            'grand_total' => 180000.00,
            'is_latest_revision' => true,
            'created_by_user_id' => $this->executive1->id,
        ]);

        // Expected achieved = 2,00,000 + 1,80,000 = 3,80,000
        $result = TargetAchievementService::calculateForTarget($target);

        $this->assertEquals(500000.00, $result['target']);
        $this->assertEquals(380000.00, $result['achieved']);
        $this->assertEquals(-120000.00, $result['variance']);
        $this->assertEquals(76.00, $result['percentage']); // 380,000 / 500,000 = 76%
        $this->assertFalse($result['is_achieved']);
    }

    public function test_accepted_in_april_counts_to_new_fy_even_if_created_in_march(): void
    {
        // Quotation created on 28 March 2026 (FY 2025-26)
        // But accepted on 5 April 2026 (FY 2026-27)
        $quote = Quotation::create([
            'quotation_number' => 'MSS/2025-26/0500',
            'financial_year' => '2025-26',
            'sequence_no' => 500,
            'client_id' => $this->client->id,
            'client_name' => $this->client->company_name,
            'billing_address' => 'Guwahati',
            'quotation_date' => Carbon::create(2026, 3, 28),
            'valid_until' => Carbon::create(2026, 4, 12),
            'status' => QuotationStatus::Accepted,
            'accepted_at' => Carbon::create(2026, 4, 5, 15, 0), // April 2026
            'grand_total' => 250000.00,
            'is_latest_revision' => true,
            'created_by_user_id' => $this->executive1->id,
        ]);

        // Target for April 2026 (Month 4 of FY 2026-27)
        $aprilTarget = Target::create([
            'user_id' => $this->executive1->id,
            'period_type' => TargetPeriodType::Monthly,
            'financial_year' => '2026-27',
            'month' => 4,
            'target_amount' => 200000.00,
        ]);

        // Target for March 2026 (Month 3 of FY 2025-26)
        $marchTarget = Target::create([
            'user_id' => $this->executive1->id,
            'period_type' => TargetPeriodType::Monthly,
            'financial_year' => '2025-26',
            'month' => 3,
            'target_amount' => 300000.00,
        ]);

        $aprilResult = TargetAchievementService::calculateForTarget($aprilTarget);
        $marchResult = TargetAchievementService::calculateForTarget($marchTarget);

        // April (new FY) should get the 2,50,000 credit
        $this->assertEquals(250000.00, $aprilResult['achieved']);
        $this->assertEquals(125.00, $aprilResult['percentage']);
        $this->assertTrue($aprilResult['is_achieved']);

        // March (old FY) should NOT count it
        $this->assertEquals(0.00, $marchResult['achieved']);
    }

    public function test_zero_target_division_guard(): void
    {
        $target = Target::create([
            'user_id' => $this->executive1->id,
            'period_type' => TargetPeriodType::Monthly,
            'financial_year' => '2026-27',
            'month' => 5,
            'target_amount' => 0.00,
        ]);

        $result = TargetAchievementService::calculateForTarget($target);

        $this->assertEquals(0.00, $result['target']);
        $this->assertEquals(0.00, $result['percentage']);
        $this->assertEquals(0.00, $result['variance']);
        $this->assertFalse($result['is_achieved']);
    }

    public function test_admin_can_manage_targets_and_executive_sees_only_own(): void
    {
        $target1 = Target::create([
            'user_id' => $this->executive1->id,
            'period_type' => TargetPeriodType::Yearly,
            'financial_year' => '2026-27',
            'month' => 0,
            'target_amount' => 5000000.00,
        ]);

        $target2 = Target::create([
            'user_id' => $this->executive2->id,
            'period_type' => TargetPeriodType::Yearly,
            'financial_year' => '2026-27',
            'month' => 0,
            'target_amount' => 4000000.00,
        ]);

        // Admin sees both
        $adminVisible = Target::visibleTo($this->admin)->get();
        $this->assertCount(2, $adminVisible);

        // Executive 1 sees only own target
        $exec1Visible = Target::visibleTo($this->executive1)->get();
        $this->assertCount(1, $exec1Visible);
        $this->assertEquals($target1->id, $exec1Visible->first()->id);

        // Executive 2 sees only own target
        $exec2Visible = Target::visibleTo($this->executive2)->get();
        $this->assertCount(1, $exec2Visible);
        $this->assertEquals($target2->id, $exec2Visible->first()->id);
    }
}
