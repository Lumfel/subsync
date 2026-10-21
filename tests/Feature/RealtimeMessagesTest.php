<?php

namespace Tests\Feature;

use App\Events\SubSyncEvent;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Messages must be pushed, not left at rest: a new message has to land on the
 * shared event bus (SSE table + Reverb broadcast), and every portal script must
 * treat the WebSocket as the primary channel with SSE as the fallback.
 */
class RealtimeMessagesTest extends TestCase
{
    use RefreshDatabase;

    private function resident(): Resident
    {
        return Resident::create(['name' => 'Res', 'email' => 'res@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function officer(): Officer
    {
        return Officer::create(['name' => 'Off', 'email' => 'off@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    public function test_new_message_publishes_sse_and_websocket_events(): void
    {
        Event::fake([SubSyncEvent::class]);

        $officer = $this->officer();
        $resident = $this->resident();

        $start = $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/messages/start', [
                'title' => 'Gate pass request',
                'recipient_type' => 'officer',
                'recipient_id' => $officer->id,
            ])->assertOk();

        $convId = $start->json('conversation.id');

        $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson("/api/messages/{$convId}", ['content' => 'Hello officer'])
            ->assertOk();

        $this->assertDatabaseHas('sse_events', [
            'channel' => 'messages',
            'event' => 'message',
        ]);

        Event::assertDispatched(
            SubSyncEvent::class,
            fn (SubSyncEvent $e) => $e->eventName === 'message'
                && $e->payload['senderName'] === 'Res'
                && $e->payload['senderType'] === 'resident'
                && $e->payload['senderId'] === $resident->id
        );
    }

    public function test_portal_scripts_are_websocket_first_with_sse_fallback(): void
    {
        foreach (['residents.js', 'officer-portal.js', 'admin-dashboard.js'] as $file) {
            $js = file_get_contents(public_path('js/'.$file));

            // Realtime state: WS-primary flag + tracked fallback EventSource.
            $this->assertStringContainsString('_rtWsReady', $js, "{$file} must track the WebSocket state.");
            $this->assertStringContainsString('_rtSse', $js, "{$file} must track the fallback EventSource.");

            // The fallback must refuse to run while the socket is live.
            $this->assertStringContainsString('if(_rtWsReady) return;', $js, "{$file} must skip SSE while WS is connected.");

            // Messages ride the socket too, not only the 10s poll.
            $this->assertStringContainsString(".listen('.message'", $js, "{$file} must listen for message events.");
            $this->assertStringContainsString('String(evt.conversationId)', $js, "{$file} must match the pushed conversation id.");

            // Incoming messages announce who is contacting you.
            $this->assertStringContainsString("'New message from '", $js, "{$file} must name the sender on new messages.");
            $this->assertStringContainsString('senderName', $js, "{$file} must use the pushed sender name.");

            // A poll is still allowed, but only as fallback — never a bare
            // setInterval that fetches unconditionally.
            $this->assertStringContainsString('fallback', strtolower($js), "{$file} must document/use the fallback poll.");
        }
    }
}
