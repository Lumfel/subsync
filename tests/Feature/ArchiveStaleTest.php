<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\IssueReport;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveStaleTest extends TestCase
{
    use RefreshDatabase;

    private function announcement(array $attrs = []): Announcement
    {
        return Announcement::create(array_merge([
            'title' => 'Notice', 'content' => 'Body', 'tag' => 'notice',
            'status' => 'Active', 'priority' => 'Normal', 'target' => 'All Residents',
        ], $attrs));
    }

    public function test_past_events_are_archived_not_deleted(): void
    {
        $past = $this->announcement(['title' => 'Past Event', 'event_date' => now()->subDays(10)->toDateString()]);

        $this->artisan('announcements:archive-stale')->assertSuccessful();

        $this->assertDatabaseHas('announcements', ['id' => $past->id, 'status' => 'Archived']);
    }

    public function test_upcoming_and_recent_announcements_stay_active(): void
    {
        $future = $this->announcement(['title' => 'Future Event', 'event_date' => now()->addDays(10)->toDateString()]);
        $recent = $this->announcement(['title' => 'Recent Notice']);

        $this->artisan('announcements:archive-stale')->assertSuccessful();

        $this->assertSame('Active', $future->fresh()->status);
        $this->assertSame('Active', $recent->fresh()->status);
    }

    public function test_old_notices_are_archived(): void
    {
        $old = $this->announcement(['title' => 'Old Notice']);
        $old->forceFill(['created_at' => now()->subDays(120)])->saveQuietly();

        $this->artisan('announcements:archive-stale')->assertSuccessful();

        $this->assertDatabaseHas('announcements', ['id' => $old->id, 'status' => 'Archived']);
    }

    public function test_resolved_issues_are_archived_by_purge_command(): void
    {
        $resident = Resident::create(['name' => 'R', 'email' => 'r@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
        $issue = IssueReport::create([
            'resident_id' => $resident->id, 'category' => 'Road / Pavement', 'title' => 'Pothole',
            'description' => 'x', 'status' => 'Resolved',
        ]);
        $issue->forceFill(['updated_at' => now()->subDays(40)])->saveQuietly();

        $this->artisan('issues:purge-resolved')->assertSuccessful();

        $this->assertDatabaseHas('issue_reports', ['id' => $issue->id]);
        $this->assertNotNull($issue->fresh()->archived_at);
    }
}
