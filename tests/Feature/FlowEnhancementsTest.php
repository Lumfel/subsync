<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Household;
use App\Models\Officer;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlowEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_data_includes_pending_payments_count(): void
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin_flow@example.test',
            'password' => 'Admin@12345',
            'status' => 'Active',
        ]);

        $household = Household::create(['block_lot_number' => 'Block 1 Lot 1']);
        $resident = Resident::create([
            'name' => 'Resident User',
            'email' => 'resident_flow@example.test',
            'password' => 'Resident@12345',
            'status' => 'Active',
            'house_id' => $household->id,
        ]);

        PaymentSubmission::create([
            'resident_id' => $resident->id,
            'amount' => 500.00,
            'reference_no' => '1002345678901',
            'sender_name' => $resident->name,
            'status' => 'Pending',
        ]);

        PaymentSubmission::create([
            'resident_id' => $resident->id,
            'amount' => 300.00,
            'reference_no' => '1002345678902',
            'sender_name' => $resident->name,
            'status' => 'Validated',
        ]);

        $res = $this->actingAs($admin, 'admin')
            ->withSession(['admin_id' => $admin->id, 'active_role' => 'admin'])
            ->getJson('/api/dashboard-data');

        $res->assertOk();
        $this->assertArrayHasKey('pendingPaymentsCount', $res->json());
        $this->assertEquals(1, $res->json('pendingPaymentsCount'));
    }

    public function test_resident_portal_renders_quick_pay_action_and_form(): void
    {
        $household = Household::create(['block_lot_number' => 'Block 1 Lot 2']);
        $resident = Resident::create([
            'name' => 'Resident User 2',
            'email' => 'resident2_flow@example.test',
            'password' => 'Resident@12345',
            'status' => 'Active',
            'house_id' => $household->id,
            'current_balance' => 750.00,
        ]);

        $res = $this->actingAs($resident, 'resident')
            ->withSession(['resident_id' => $resident->id, 'active_role' => 'resident'])
            ->get('/residents');

        $res->assertOk();
        $res->assertSee('quickPay()', false);
        $res->assertSee('id="payFormCard"', false);
        $res->assertSee('13 digits', false);
    }

    public function test_officer_portal_renders_assigned_to_me_filter(): void
    {
        $officer = Officer::create([
            'name' => 'Officer User',
            'email' => 'officer_flow@example.test',
            'password' => 'Officer@12345',
            'status' => 'Active',
        ]);

        $res = $this->actingAs($officer, 'officer')
            ->withSession(['officer_id' => $officer->id, 'active_role' => 'officer'])
            ->get('/officer-portal');

        $res->assertOk();
        $res->assertSee('id="officerIssueAssigneeFilter"', false);
        $res->assertSee('Assigned to Me', false);
        $res->assertSee('__OFFICER_ID', false);
    }

    public function test_admin_dashboard_renders_daily_action_center(): void
    {
        $admin = Admin::create([
            'name' => 'Admin User 2',
            'email' => 'admin2_flow@example.test',
            'password' => 'Admin@12345',
            'status' => 'Active',
        ]);

        $res = $this->actingAs($admin, 'admin')
            ->withSession(['admin_id' => $admin->id, 'active_role' => 'admin'])
            ->get('/dashboard');

        $res->assertOk();
        $res->assertSee('id="adminActionCenter"', false);
        $res->assertSee('Action Center · Items Requiring Attention', false);
        $res->assertSee('id="actionItemRegistrations"', false);
        $res->assertSee('id="actionItemPayments"', false);
        $res->assertSee('id="actionItemIssues"', false);
    }
}
