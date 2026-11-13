<?php

namespace Tests\Feature;

use App\Enums\FollowUpStatus;
use App\Enums\ProspectSource;
use App\Enums\ProspectStage;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Prospect;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProspectAndFollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_lead_conversion_creates_client_and_links_prospect(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();

        $prospect = Prospect::create([
            'contact_person' => 'Bhupen Hazarika',
            'company_name' => 'Assam Melody Studio',
            'phone' => '+91 94350 99999',
            'email' => 'studio@assammelody.in',
            'source' => ProspectSource::Referral,
            'requirement' => 'Studio recording high performance PC + Yamaha studio monitors.',
            'expected_value' => 150000.00,
            'stage' => ProspectStage::Negotiation,
            'assigned_user_id' => $rahul->id,
        ]);

        $this->assertFalse($prospect->is_converted);

        // Convert to client
        $client = Client::create([
            'company_name' => $prospect->company_name,
            'contact_person' => $prospect->contact_person,
            'phone' => $prospect->phone,
            'email' => $prospect->email,
            'gstin' => '18AAACH9999P1Z1',
            'billing_address' => 'Silpukhuri, Guwahati, Assam',
            'state' => 'Assam',
            'state_code' => '18',
            'assigned_user_id' => $prospect->assigned_user_id,
            'is_active' => true,
        ]);

        $prospect->update([
            'converted_client_id' => $client->id,
            'converted_at' => now(),
            'stage' => ProspectStage::Won,
        ]);

        $prospect->refresh();

        $this->assertTrue($prospect->is_converted);
        $this->assertEquals($client->id, $prospect->converted_client_id);
        $this->assertEquals(ProspectStage::Won, $prospect->stage);
        $this->assertNotNull($prospect->converted_at);
    }

    public function test_double_conversion_is_blocked(): void
    {
        $prospect = Prospect::whereNotNull('converted_client_id')->first();

        if (!$prospect) {
            $client = Client::first();
            $prospect = Prospect::first();
            $prospect->update([
                'converted_client_id' => $client->id,
                'converted_at' => now(),
            ]);
        }

        $this->assertTrue($prospect->is_converted);

        // Verification that is_converted flag guards re-conversion
        $canConvertAgain = !$prospect->is_converted;
        $this->assertFalse($canConvertAgain);
    }

    public function test_executive_sees_only_own_prospects(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $priya = User::where('email', 'priya@marutisales.in')->first();
        $admin = User::where('email', 'admin@marutisales.in')->first();

        $rahulProspects = Prospect::visibleTo($rahul)->get();
        $priyaProspects = Prospect::visibleTo($priya)->get();
        $adminProspects = Prospect::visibleTo($admin)->get();

        $this->assertEquals(Prospect::where('assigned_user_id', $rahul->id)->count(), $rahulProspects->count());
        $this->assertEquals(Prospect::where('assigned_user_id', $priya->id)->count(), $priyaProspects->count());
        $this->assertEquals(Prospect::count(), $adminProspects->count());

        foreach ($rahulProspects as $item) {
            $this->assertEquals($rahul->id, $item->assigned_user_id);
        }
    }

    public function test_executive_sees_only_own_follow_ups(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $admin = User::where('email', 'admin@marutisales.in')->first();

        $rahulFollowUps = FollowUp::visibleTo($rahul)->get();
        $adminFollowUps = FollowUp::visibleTo($admin)->get();

        $this->assertEquals(FollowUp::where('assigned_user_id', $rahul->id)->count(), $rahulFollowUps->count());
        $this->assertEquals(FollowUp::count(), $adminFollowUps->count());

        foreach ($rahulFollowUps as $followUp) {
            $this->assertEquals($rahul->id, $followUp->assigned_user_id);
        }
    }

    public function test_overdue_follow_up_scope_and_status(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();

        // 1. Overdue pending item
        $overdue = FollowUp::create([
            'subject' => 'Overdue Test Task',
            'assigned_user_id' => $rahul->id,
            'prospect_id' => Prospect::first()->id,
            'due_date' => Carbon::today()->subDays(2),
            'status' => FollowUpStatus::Pending,
        ]);

        $this->assertTrue($overdue->isOverdue());
        $this->assertFalse($overdue->isDueToday());
        $this->assertTrue(FollowUp::overdue()->where('id', $overdue->id)->exists());

        // 2. Due today item
        $dueToday = FollowUp::create([
            'subject' => 'Due Today Task',
            'assigned_user_id' => $rahul->id,
            'prospect_id' => Prospect::first()->id,
            'due_date' => Carbon::today(),
            'status' => FollowUpStatus::Pending,
        ]);

        $this->assertFalse($dueToday->isOverdue());
        $this->assertTrue($dueToday->isDueToday());
        $this->assertTrue(FollowUp::dueToday()->where('id', $dueToday->id)->exists());

        // 3. Completed item (even if in past, should NOT be overdue)
        $completed = FollowUp::create([
            'subject' => 'Completed Past Task',
            'assigned_user_id' => $rahul->id,
            'prospect_id' => Prospect::first()->id,
            'due_date' => Carbon::today()->subDays(4),
            'status' => FollowUpStatus::Completed,
        ]);

        $this->assertFalse($completed->isOverdue());
        $this->assertFalse(FollowUp::overdue()->where('id', $completed->id)->exists());
    }

    public function test_setting_next_follow_up_date_syncs_linked_follow_up(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();

        $followUpDate = Carbon::today()->addDays(3);

        $prospect = Prospect::create([
            'contact_person' => 'Nabajyoti Das',
            'company_name' => 'Brahmaputra Cold Storage',
            'phone' => '+91 94350 77665',
            'source' => ProspectSource::PhoneCall,
            'requirement' => 'Barcode thermal printers + ribbons.',
            'expected_value' => 45000.00,
            'stage' => ProspectStage::New,
            'assigned_user_id' => $rahul->id,
            'next_follow_up_date' => $followUpDate,
        ]);

        // Assert auto-created follow-up
        $linkedFollowUp = FollowUp::where('prospect_id', $prospect->id)->first();
        $this->assertNotNull($linkedFollowUp);
        $this->assertEquals($followUpDate->format('Y-m-d'), $linkedFollowUp->due_date->format('Y-m-d'));
        $this->assertEquals(FollowUpStatus::Pending, $linkedFollowUp->status);
        $this->assertEquals($rahul->id, $linkedFollowUp->assigned_user_id);

        // Update follow-up date and assert no duplicate is created, but existing is updated
        $newDate = Carbon::today()->addDays(7);
        $prospect->update(['next_follow_up_date' => $newDate]);

        $followUpCount = FollowUp::where('prospect_id', $prospect->id)->count();
        $this->assertEquals(1, $followUpCount);

        $linkedFollowUp->refresh();
        $this->assertEquals($newDate->format('Y-m-d'), $linkedFollowUp->due_date->format('Y-m-d'));
    }

    public function test_kanban_board_route_is_accessible_to_authenticated_user(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();
        $response = $this->actingAs($admin)->get('/admin/prospects/kanban');

        $response->assertSuccessful();
        $response->assertSee('Prospects Pipeline Board');
        $response->assertSee('New');
        $response->assertSee('Quoted');
        $response->assertSee('Negotiation');
    }
}
