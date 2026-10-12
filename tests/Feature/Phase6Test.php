<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\IssueReport;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6Test extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(): Resident
    {
        return Resident::firstOrCreate(
            ['email' => 'resident@example.test'],
            ['name' => 'Test Resident', 'password' => 'Secret@12345', 'status' => 'Active']
        );
    }

    private function issue(string $status = 'Pending'): IssueReport
    {
        return IssueReport::create([
            'resident_id' => $this->resident()->id,
            'category' => 'Other',
            'title' => 'Test issue',
            'description' => 'Something to report.',
            'status' => $status,
        ]);
    }

    public function test_status_change_writes_audit_trail(): void
    {
        $admin = $this->admin();
        $issue = $this->issue('Pending');

        $this->withSession(['admin_id' => $admin->id])
            ->putJson('/api/issues/'.$issue->id.'/status', ['status' => 'In Progress'])
            ->assertOk();

        $this->assertDatabaseHas('issue_audit_logs', [
            'issue_id' => $issue->id,
            'action' => 'status',
            'old_value' => 'Pending',
            'new_value' => 'In Progress',
            'actor_type' => 'Admin',
        ]);

        $history = $this->withSession(['admin_id' => $admin->id])
            ->getJson('/api/issues/'.$issue->id.'/history')
            ->assertOk()
            ->json();

        $this->assertCount(1, $history);
        $this->assertSame('status', $history[0]['action']);
    }

    public function test_priority_change_writes_audit_trail(): void
    {
        $admin = $this->admin();
        $issue = $this->issue();

        $this->withSession(['admin_id' => $admin->id])
            ->putJson('/api/issues/'.$issue->id.'/priority', ['priority' => 'Critical'])
            ->assertOk();

        $this->assertDatabaseHas('issue_audit_logs', [
            'issue_id' => $issue->id,
            'action' => 'priority',
            'new_value' => 'Critical',
        ]);
    }

    public function test_resident_cannot_change_issue_status(): void
    {
        $issue = $this->issue('Pending');

        $this->actingAs($this->resident(), 'resident')
            ->putJson('/api/issues/'.$issue->id.'/status', ['status' => 'Resolved'])
            ->assertStatus(401);

        $this->assertSame('Pending', $issue->fresh()->status);
        $this->assertDatabaseCount('issue_audit_logs', 0);
    }
}
