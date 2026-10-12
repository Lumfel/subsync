<?php

namespace Tests\Feature;

use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase9Test extends TestCase
{
    use RefreshDatabase;

    private function officer(): Officer
    {
        return Officer::create([
            'name' => 'Test Officer', 'email' => 'officer@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function issue(): IssueReport
    {
        $resident = Resident::create([
            'name' => 'Test Resident', 'email' => 'resident@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);

        return IssueReport::create([
            'resident_id' => $resident->id,
            'category' => 'Other',
            'title' => 'Streetlight out',
            'description' => 'Lamp near the gate is out.',
            'status' => 'Pending',
        ]);
    }

    public function test_officer_can_set_urgency_and_status(): void
    {
        $officer = $this->officer();
        $issue = $this->issue();

        $this->actingAs($officer, 'officer')
            ->putJson('/api/issues/'.$issue->id.'/priority', ['priority' => 'High'])
            ->assertOk()
            ->assertJson(['success' => true, 'priority' => 'High']);

        $this->actingAs($officer, 'officer')
            ->putJson('/api/issues/'.$issue->id.'/status', ['status' => 'In Progress'])
            ->assertOk();

        $this->assertSame('High', $issue->fresh()->priority);
        $this->assertSame('In Progress', $issue->fresh()->status);

        $this->assertDatabaseHas('issue_audit_logs', [
            'issue_id' => $issue->id,
            'action' => 'priority',
            'actor_type' => 'Officer',
            'new_value' => 'High',
        ]);
    }

    public function test_officer_can_read_map_issues(): void
    {
        $this->actingAs($this->officer(), 'officer')
            ->getJson('/api/map/issues')
            ->assertOk();
    }

    public function test_officer_can_read_map_settings(): void
    {
        $this->actingAs($this->officer(), 'officer')
            ->getJson('/api/map/settings')
            ->assertOk();
    }
}
