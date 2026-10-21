<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmergencyReportTest extends TestCase
{
    use RefreshDatabase;

    private function resident(): Resident
    {
        return Resident::create(['name' => 'Juan Dela Cruz', 'email' => 'j@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    public function test_emergency_report_is_created_as_critical(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->postJson('/api/issues', [
                'category' => 'Emergency',
                'title' => 'Gas leak near clubhouse',
                'description' => 'Strong smell of gas along the walkway.',
            ])
            ->assertOk()
            ->assertJsonPath('issue.priority', 'Critical');

        $this->assertDatabaseHas('issue_reports', [
            'category' => 'Emergency',
            'title' => 'Gas leak near clubhouse',
            'priority' => 'Critical',
            'status' => 'Pending',
        ]);
    }

    public function test_emergency_reports_surface_in_the_urgent_check(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->postJson('/api/issues', [
                'category' => 'Emergency',
                'title' => 'Live wire on sidewalk',
                'description' => 'Exposed wire sparking near block 3.',
            ])
            ->assertOk();

        $admin = Admin::create(['name' => 'A', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => $admin->name])
            ->getJson('/api/issues/urgent-check')
            ->assertOk();
    }

    public function test_regular_categories_keep_their_category_priority(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->postJson('/api/issues', [
                'category' => 'Noise Complaint',
                'title' => 'Loud videoke',
                'description' => 'Late-night karaoke next door.',
            ])
            ->assertOk()
            ->assertJsonPath('issue.priority', 'Low');
    }
}
