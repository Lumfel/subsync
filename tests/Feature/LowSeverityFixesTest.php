<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Family;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowSeverityFixesTest extends TestCase
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
        $house = Household::create(['block_lot_number' => 'Block '.random_int(1, 99).' Lot '.random_int(1, 99)]);

        return Resident::create(array_merge([
            'name' => 'Resident', 'email' => 'r'.uniqid().'@x.com',
            'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id,
        ], $attrs));
    }

    private function adminSession()
    {
        $admin = $this->admin();

        return $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin']);
    }

    public function test_bulk_approve_only_activates_pending_residents(): void
    {
        $pending = $this->resident(['status' => 'Pending']);
        $inactive = $this->resident(['status' => 'Inactive']);

        $this->adminSession()
            ->postJson('/api/residents/bulk-approve', ['ids' => [$pending->id, $inactive->id]])
            ->assertOk()
            ->assertJsonPath('updated', 1);

        $this->assertSame('Active', $pending->fresh()->status);
        $this->assertSame('Inactive', $inactive->fresh()->status);
    }

    public function test_household_members_rejects_unknown_house_id(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->getJson('/api/household-members?house_id=999999')
            ->assertStatus(422);
    }

    public function test_missing_conversation_returns_404(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->getJson('/api/messages/999999')
            ->assertNotFound();
    }

    public function test_issue_creation_returns_computed_priority(): void
    {
        $resident = $this->resident();

        $res = $this->actingAs($resident, 'resident')
            ->postJson('/api/issues', ['category' => 'Noise', 'title' => 'Loud', 'description' => 'Very loud'])
            ->assertOk();

        $this->assertNotNull($res->json('issue.priority'));
    }

    public function test_bulk_archive_reports_only_newly_archived(): void
    {
        $resident = $this->resident();
        $a = IssueReport::create(['resident_id' => $resident->id, 'category' => 'Noise', 'title' => 'A', 'description' => 'x', 'status' => 'Open', 'archived_at' => now()]);
        $b = IssueReport::create(['resident_id' => $resident->id, 'category' => 'Noise', 'title' => 'B', 'description' => 'x', 'status' => 'Open']);

        $this->adminSession()
            ->patchJson('/api/issues/bulk-archive', ['ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJsonPath('archived', 1);

        $this->assertNotNull($b->fresh()->archived_at);
    }

    public function test_issue_history_missing_id_returns_404(): void
    {
        $this->adminSession()
            ->getJson('/api/issues/999999/history')
            ->assertNotFound();
    }

    public function test_officer_responses_hide_google_and_avatar_fields(): void
    {
        $officer = Officer::create([
            'name' => 'Off', 'email' => 'g@x.com', 'password' => 'Secret@12345',
            'status' => 'Active', 'google_id' => 'g-1', 'avatar' => 'https://example.com/a.png',
        ]);

        $this->adminSession()
            ->putJson("/api/officers/{$officer->id}", ['name' => 'Off2'])
            ->assertOk()
            ->assertJsonMissingPath('officer.google_id')
            ->assertJsonMissingPath('officer.avatar');
    }

    public function test_map_settings_rejects_inverted_bounds(): void
    {
        $this->adminSession()
            ->putJson('/api/map/settings', ['min_lat' => 2, 'max_lat' => 1, 'min_lng' => 1, 'max_lng' => 2])
            ->assertStatus(422);
    }

    public function test_search_strips_like_wildcards(): void
    {
        $this->resident(['name' => 'Wildcard Victim']);

        $res = $this->adminSession()
            ->getJson('/api/search?q=%25')
            ->assertOk();

        $this->assertSame([], $res->json('results'));
    }

    public function test_deleting_family_detaches_households(): void
    {
        $family = Family::create(['family_name' => 'Dela Cruz']);
        $house = Household::create(['block_lot_number' => 'Block 7 Lot 7', 'family_id' => $family->id]);

        $this->adminSession()
            ->deleteJson("/api/families/{$family->id}")
            ->assertOk();

        $this->assertNull($house->fresh()->family_id);
        $this->assertDatabaseMissing('families', ['id' => $family->id]);
    }
}
