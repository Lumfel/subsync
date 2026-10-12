<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HoaPolicy;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class Phase4Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(): Resident
    {
        return Resident::create([
            'name' => 'Test Resident', 'email' => 'resident@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    public function test_admin_can_create_update_and_delete_policy(): void
    {
        $admin = $this->admin();

        $create = $this->withSession(['admin_id' => $admin->id])->postJson('/api/hoa-policies', [
            'title' => 'Pet Policy',
            'description' => 'Leash pets in common areas.',
            'category' => 'Community Rules',
            'effective_date' => '2026-01-01',
            'status' => 'Active',
        ])->assertOk()->assertJson(['success' => true]);

        $id = $create->json('policy.id');
        $this->assertDatabaseHas('hoa_policies', ['id' => $id, 'title' => 'Pet Policy']);

        $this->withSession(['admin_id' => $admin->id])->putJson('/api/hoa-policies/'.$id, [
            'title' => 'Pet Policy v2',
            'description' => 'Leash pets at all times.',
            'category' => 'Community Rules',
            'effective_date' => '2026-02-01',
            'status' => 'Active',
        ])->assertOk();

        $this->assertDatabaseHas('hoa_policies', ['id' => $id, 'title' => 'Pet Policy v2']);

        $this->withSession(['admin_id' => $admin->id])->deleteJson('/api/hoa-policies/'.$id)->assertOk();
        // Destroy now archives (soft) instead of deleting, so history is retained.
        $this->assertDatabaseHas('hoa_policies', ['id' => $id, 'status' => 'Archived']);
    }

    public function test_resident_can_view_policies(): void
    {
        HoaPolicy::create([
            'title' => 'Noise Policy', 'description' => 'Quiet hours 10pm.',
            'category' => 'Community Rules', 'effective_date' => '2026-01-01', 'status' => 'Active',
        ]);

        $res = $this->actingAs($this->resident(), 'resident')->getJson('/api/hoa-policies');

        $res->assertOk();
        $this->assertCount(1, $res->json());
    }

    public function test_non_admin_cannot_create_policy(): void
    {
        $this->postJson('/api/hoa-policies', [
            'title' => 'Hacked', 'description' => 'x', 'category' => 'x', 'effective_date' => '2026-01-01',
        ])->assertStatus(401);
    }
}
