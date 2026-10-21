<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupAndAuditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Audit Admin', 'email' => 'audit@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function resident(): Resident
    {
        return Resident::create(['name' => 'Juan Dela Cruz', 'email' => 'j@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    public function test_admin_can_list_backups(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => $admin->name])
            ->getJson('/api/backups')
            ->assertOk()
            ->assertJsonStructure(['backups']);
    }

    public function test_admin_can_view_activity_logs(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => $admin->name])
            ->getJson('/api/activity-logs')
            ->assertOk()
            ->assertJsonStructure(['logs']);
    }

    public function test_system_tools_are_admin_only(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')->getJson('/api/backups')->assertStatus(401);
        $this->actingAs($resident, 'resident')->getJson('/api/activity-logs')->assertStatus(401);
        $this->actingAs($resident, 'resident')->postJson('/api/backups/run')->assertStatus(401);
    }

    public function test_creating_an_announcement_is_logged(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => $admin->name])
            ->postJson('/api/announcements', ['title' => 'Audit check', 'content' => 'Hello'])
            ->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'actor_type' => 'admin',
            'actor_name' => 'Audit Admin',
            'action' => 'created',
            'subject_type' => 'Announcement',
            'description' => 'Created announcement: Audit check',
        ]);
    }

    public function test_updating_a_resident_is_logged(): void
    {
        $admin = $this->admin();
        $resident = $this->resident();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => $admin->name])
            ->putJson('/api/residents/'.$resident->id, ['name' => 'Renamed Resident'])
            ->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'updated',
            'subject_type' => 'Resident',
            'subject_id' => $resident->id,
        ]);
    }

    public function test_backup_run_returns_json_without_server_error(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => $admin->name])
            ->postJson('/api/backups/run')
            ->assertOk()
            ->assertJsonStructure(['success', 'message']);
    }
}
