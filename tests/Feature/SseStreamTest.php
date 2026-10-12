<?php

namespace Tests\Feature;

use App\Models\Admin;
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
}
