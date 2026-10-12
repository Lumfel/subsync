<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueSlaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Test Admin', 'email' => 'admin@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function officer(): Officer
    {
        return Officer::create(['name' => 'Officer One', 'email' => 'officer@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function resident(string $email = 'resident@example.test'): Resident
    {
        return Resident::create(['name' => 'Test Resident', 'email' => $email, 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function issue(Resident $resident, array $attrs = []): IssueReport
    {
        return IssueReport::create(array_merge([
            'resident_id' => $resident->id, 'category' => 'Road / Pavement',
            'title' => 'Pothole', 'description' => 'Big pothole', 'status' => 'Pending',
        ], $attrs));
    }

    public function test_admin_can_assign_an_officer_and_due_date(): void
    {
        $admin = $this->admin();
        $officer = $this->officer();
        $issue = $this->issue($this->resident());

        $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->putJson('/api/issues/'.$issue->id.'/assign', [
                'assigned_officer_id' => $officer->id,
                'due_date' => '2026-11-01',
            ])
            ->assertOk()
            ->assertJsonPath('assigned_officer', 'Officer One');

        $this->assertDatabaseHas('issue_reports', [
            'id' => $issue->id, 'assigned_officer_id' => $officer->id,
        ]);
        $this->assertSame('2026-11-01', $issue->fresh()->due_date->format('Y-m-d'));
    }

    public function test_issue_list_exposes_assignment_and_overdue(): void
    {
        $admin = $this->admin();
        $officer = $this->officer();
        $issue = $this->issue($this->resident(), ['due_date' => '2000-01-01', 'status' => 'Pending']);
        $issue->update(['assigned_officer_id' => $officer->id]);

        $res = $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/issues');

        $row = collect($res->json())->firstWhere('id', $issue->id);
        $this->assertSame('Officer One', $row['assigned_officer']);
        $this->assertTrue($row['overdue']);
    }

    public function test_resident_sees_timeline_for_own_issue_only(): void
    {
        $admin = $this->admin();
        $officer = $this->officer();
        $resident = $this->resident();
        $issue = $this->issue($resident);

        $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->putJson('/api/issues/'.$issue->id.'/assign', ['assigned_officer_id' => $officer->id]);
        $this->flushSession();

        $res = $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/issues/my/'.$issue->id.'/timeline')
            ->assertOk()
            ->assertJsonPath('issue.assigned_officer', 'Officer One');

        $this->assertNotEmpty($res->json('timeline'));

        $this->flushSession();
        $other = $this->resident('other@example.test');
        $this->actingAs($other, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/issues/my/'.$issue->id.'/timeline')
            ->assertNotFound();
    }

    public function test_overdue_flag_reflects_due_date_and_status(): void
    {
        $resident = $this->resident();
        $past = $this->issue($resident, ['due_date' => '2000-01-01']);
        $future = $this->issue($resident, ['due_date' => '2999-01-01']);
        $resolved = $this->issue($resident, ['due_date' => '2000-01-01', 'status' => 'Resolved']);

        $this->assertTrue($past->fresh()->is_overdue);
        $this->assertFalse($future->fresh()->is_overdue);
        $this->assertFalse($resolved->fresh()->is_overdue);
    }
}
