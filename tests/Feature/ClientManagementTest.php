<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\User;
use App\Rules\ValidGstin;
use App\Support\GstHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_see_all_clients(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();
        $this->actingAs($admin);

        $visibleClients = Client::visibleTo($admin)->get();
        $totalClients = Client::count();

        $this->assertEquals($totalClients, $visibleClients->count());
        $this->assertGreaterThanOrEqual(10, $visibleClients->count());
    }

    public function test_sales_executive_sees_only_assigned_clients(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $this->actingAs($rahul);

        $visibleClients = Client::visibleTo($rahul)->get();
        $assignedCount = Client::where('assigned_user_id', $rahul->id)->count();

        $this->assertEquals($assignedCount, $visibleClients->count());

        foreach ($visibleClients as $client) {
            $this->assertEquals($rahul->id, $client->assigned_user_id);
        }
    }

    public function test_sales_executive_cannot_view_unassigned_client(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $priya = User::where('email', 'priya@marutisales.in')->first();

        $priyaClient = Client::where('assigned_user_id', $priya->id)->first();

        $this->assertNotNull($priyaClient);
        $this->assertFalse($rahul->can('view', $priyaClient));
        $this->assertTrue($rahul->can('update', Client::where('assigned_user_id', $rahul->id)->first()));
    }

    public function test_gstin_validator_passes_valid_gstin(): void
    {
        $validGstin = '18ABGFM5224K1Z2'; // 18 is Assam
        $validator = Validator::make(
            ['gstin' => $validGstin],
            ['gstin' => [new ValidGstin]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_gstin_validator_fails_invalid_gstin(): void
    {
        // Invalid state code '95'
        $invalidGstin = '95AABFM1234P1Z2';
        $validator = Validator::make(
            ['gstin' => $invalidGstin],
            ['gstin' => [new ValidGstin]]
        );

        $this->assertTrue($validator->fails());

        // Invalid length
        $shortGstin = '18AABFM1234';
        $validator2 = Validator::make(
            ['gstin' => $shortGstin],
            ['gstin' => [new ValidGstin]]
        );

        $this->assertTrue($validator2->fails());
    }

    public function test_gst_helper_extracts_state_and_code(): void
    {
        // Assam GSTIN
        $assam = GstHelper::extractState('18AABFM1234P1Z2');
        $this->assertEquals('18', $assam['state_code']);
        $this->assertEquals('Assam', $assam['state_name']);

        // Meghalaya GSTIN
        $meghalaya = GstHelper::extractState('17AABCM7788P1Z0');
        $this->assertEquals('17', $meghalaya['state_code']);
        $this->assertEquals('Meghalaya', $meghalaya['state_name']);

        // West Bengal GSTIN
        $bengal = GstHelper::extractState('19AAACE4455N1Z7');
        $this->assertEquals('19', $bengal['state_code']);
        $this->assertEquals('West Bengal', $bengal['state_name']);
    }

    public function test_company_settings_singleton_has_maruti_defaults(): void
    {
        $settings = CompanySetting::getSettings();

        $this->assertEquals('MARUTI SALES & SERVICES', $settings->company_name);
        $this->assertEquals('(A Complete IT Solution)', $settings->tagline);
        $this->assertEquals('Assam', $settings->state);
        $this->assertEquals('18', $settings->state_code);
        $this->assertEquals('Guwahati', $settings->city);
        $this->assertEquals('18ABGFM5224K1Z2', $settings->gstin);
        $this->assertEquals('ABGFM5224K', $settings->pan);
        $this->assertEquals('+91 70022 53021', $settings->phone);
        $this->assertEquals('smarutisales@gmail.com', $settings->email);
        $this->assertEquals('MSS', $settings->quotation_prefix);
        $this->assertEquals('Subject to Guwahati jurisdiction', $settings->jurisdiction_text);
        $this->assertFalse($settings->show_signature_on_pdf);
    }
}
