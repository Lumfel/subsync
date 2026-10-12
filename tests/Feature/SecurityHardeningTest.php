<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Household;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Admin', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function officer(): Officer
    {
        return Officer::create(['name' => 'Officer', 'email' => 'o@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function resident(array $attrs = []): Resident
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);

        return Resident::create(array_merge([
            'name' => 'Resident', 'email' => 'r@x.com',
            'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id,
        ], $attrs));
    }

    // ── 1. Officer account management is admin-only ──
    public function test_officer_cannot_manage_officer_accounts(): void
    {
        $officer = $this->officer();

        $this->actingAs($officer, 'officer')
            ->postJson('/api/officers', ['name' => 'Evil', 'email' => 'evil@x.com', 'password' => 'Secret@12345'])
            ->assertForbidden();

        $this->actingAs($officer, 'officer')
            ->putJson("/api/officers/{$officer->id}", ['role_description' => 'HOA President'])
            ->assertForbidden();

        $this->actingAs($officer, 'officer')
            ->deleteJson("/api/officers/{$officer->id}")
            ->assertForbidden();

        $this->assertDatabaseMissing('officers', ['email' => 'evil@x.com']);
        $this->assertSame('Active', $officer->fresh()->status);
    }

    public function test_admin_can_manage_officer_accounts(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->postJson('/api/officers', ['name' => 'New', 'email' => 'new@x.com', 'password' => 'Secret@12345'])
            ->assertOk();

        $this->assertDatabaseHas('officers', ['email' => 'new@x.com', 'status' => 'Active']);
    }

    // ── 2. Report-template create no longer 500s without `variables` ──
    public function test_report_template_can_be_created_without_variables(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->postJson('/api/report-templates', [
                'name' => 'N', 'type' => 'T', 'subject_template' => 'S', 'body_template' => 'B',
            ])
            ->assertOk();

        $this->assertDatabaseHas('report_templates', ['name' => 'N']);
    }

    // ── 3. Staff can read the community finance summary ──
    public function test_admin_can_read_transparency_summary(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->getJson('/api/transparency/summary')
            ->assertOk()
            ->assertJsonStructure(['collected', 'spent', 'net']);
    }

    // ── 4. Admin resident payload exposes current_balance ──
    public function test_admin_resident_lists_include_current_balance(): void
    {
        $admin = $this->admin();
        $this->resident(['current_balance' => 250.5]);

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->getJson('/api/residents')
            ->assertOk()
            ->assertJsonFragment(['current_balance' => 250.5]);

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->getJson('/api/dashboard-data')
            ->assertOk()
            ->assertJsonFragment(['current_balance' => 250.5]);
    }

    // ── 5. Malformed ids 404 instead of 500 ──
    public function test_malformed_ids_return_404_not_500(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->putJson('/api/officers/abc', ['name' => 'x'])
            ->assertNotFound();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->getJson('/api/ledger/abc')
            ->assertNotFound();
    }

    // ── 6. /api/stats requires authentication ──
    public function test_stats_requires_authentication(): void
    {
        $this->getJson('/api/stats')->assertUnauthorized();

        $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'Admin'])
            ->getJson('/api/stats')
            ->assertOk();
    }

    // ── 7. Residents cannot read archived announcements ──
    public function test_resident_cannot_read_archived_announcements(): void
    {
        $resident = $this->resident();
        Announcement::create(['title' => 'Hidden Archived', 'content' => 'x', 'status' => 'Archived']);
        Announcement::create(['title' => 'Live Active', 'content' => 'x', 'status' => 'Active']);

        $res = $this->actingAs($resident, 'resident')
            ->getJson('/api/announcements?status=Archived')
            ->assertOk();

        $titles = array_column($res->json(), 'title');
        $this->assertNotContains('Hidden Archived', $titles);
        $this->assertContains('Live Active', $titles);
    }

    // ── 8. Resident self-update cannot escalate status/house/password ──
    public function test_resident_self_update_cannot_change_protected_fields(): void
    {
        $resident = $this->resident(['status' => 'Inactive']);
        $originalHash = $resident->password;

        $this->actingAs($resident, 'resident')
            ->putJson("/api/residents/{$resident->id}", [
                'name' => 'Renamed',
                'status' => 'Active',
                'house_id' => Household::create(['block_lot_number' => 'Block 9 Lot 9'])->id,
                'password' => 'Changed@12345',
            ])
            ->assertOk();

        $resident->refresh();
        $this->assertSame('Renamed', $resident->name);
        $this->assertSame('Inactive', $resident->status);
        $this->assertTrue(Hash::check('Secret@12345', $resident->password));
        $this->assertSame($originalHash, $resident->password);
    }

    // ── 9. Map writes are admin-only ──
    public function test_officer_cannot_write_map_data(): void
    {
        $officer = $this->officer();

        $this->actingAs($officer, 'officer')
            ->putJson('/api/map/settings', ['min_lat' => 1, 'max_lat' => 2, 'min_lng' => 1, 'max_lng' => 2])
            ->assertUnauthorized();

        $this->actingAs($officer, 'officer')
            ->postJson('/api/map/facility', ['name' => 'F', 'latitude' => 1.5, 'longitude' => 1.5])
            ->assertUnauthorized();
    }

    public function test_admin_can_write_map_settings(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->putJson('/api/map/settings', ['min_lat' => 1, 'max_lat' => 2, 'min_lng' => 1, 'max_lng' => 2])
            ->assertOk();
    }

    // ── 10. GCash partial update preserves untouched fields ──
    public function test_gcash_partial_update_preserves_account_details(): void
    {
        $admin = $this->admin();
        $session = fn () => $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin']);

        $session()->postJson('/api/gcash-settings', [
            'account_name' => 'TNHOA', 'account_number' => '0917', 'instructions' => 'Pay here',
        ])->assertOk();

        $session()->postJson('/api/gcash-settings', ['remove_qr' => 1])->assertOk();

        $this->assertDatabaseHas('gcash_settings', [
            'account_name' => 'TNHOA', 'account_number' => '0917', 'instructions' => 'Pay here',
        ]);
    }

    // ── 11. CSV exports neutralise formula injection ──
    public function test_csv_export_neutralises_formula_injection(): void
    {
        $admin = $this->admin();
        $this->resident(['name' => '=HYPERLINK("http://evil")']);

        $res = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->get('/api/export/residents');

        $res->assertOk();
        $this->assertStringContainsString("'=HYPERLINK", $res->getContent());
    }

    public function test_officer_accounts_have_no_role(): void
    {
        $admin = $this->admin();
        $session = fn () => $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin']);

        // role_description sent by a client is ignored, not stored or returned.
        $session()->postJson('/api/officers', [
            'name' => 'NoRole', 'email' => 'norole@x.com',
            'password' => 'Secret@12345', 'role_description' => 'President',
        ])
            ->assertOk()
            ->assertJsonMissingPath('officer.role_description');

        $this->assertDatabaseHas('officers', ['email' => 'norole@x.com', 'role_description' => null]);

        $list = $session()->getJson('/api/officers')->assertOk()->json();
        foreach ($list as $officer) {
            $this->assertArrayNotHasKey('role_description', $officer);
        }
    }
}
