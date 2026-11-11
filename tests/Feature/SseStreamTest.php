<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Household;
use App\Models\Resident;
use App\Models\SseEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SseStreamTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    public function test_sse_requires_authentication(): void
    {
        $this->get('/api/stream/updates')->assertStatus(401);
    }

    public function test_sse_endpoint_responds_as_event_stream_quickly(): void
    {
        $start = microtime(true);

        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->get('/api/stream/updates');

        $elapsedMs = (microtime(true) - $start) * 1000;

        $res->assertOk();
        $this->assertStringContainsString('text/event-stream', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('no-cache', $res->headers->get('Cache-Control'));

        // Opening the stream (before draining it) must be fast.
        $this->assertLessThan(1500, $elapsedMs, "SSE setup took {$elapsedMs}ms");
    }

    public function test_resident_stream_hides_staff_and_other_members_events(): void
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);
        $alice = Resident::create(['name' => 'Alice', 'email' => 'alice@example.test', 'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id]);
        $bob = Resident::create(['name' => 'Bob', 'email' => 'bob@example.test', 'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id]);

        SseEvent::emit('payment_submissions', 'payment', ['latestId' => 1, 'residentId' => $bob->id, 'status' => 'Validated']);
        SseEvent::emit('payment_submissions', 'payment', ['latestId' => 2, 'residentId' => $alice->id, 'status' => 'Pending']);
        SseEvent::emit('officer_files', 'officer-file', ['latestId' => 3, 'name' => 'secret-file.xlsx']);
        SseEvent::emit('announcements', 'announcement', ['latestId' => 4, 'title' => 'Community notice']);

        $body = $this->actingAs($alice, 'resident')
            ->get('/api/stream/updates?lastEventId=0&max=1')
            ->assertOk()
            ->streamedContent();

        // Own payment + community events pass…
        $this->assertStringContainsString('"residentId":'.$alice->id, $body);
        $this->assertStringContainsString('Community notice', $body);
        // …other members' payments and staff-only files do not.
        $this->assertStringNotContainsString('"residentId":'.$bob->id, $body);
        $this->assertStringNotContainsString('secret-file.xlsx', $body);
    }
}
