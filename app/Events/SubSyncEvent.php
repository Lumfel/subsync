<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Generic real-time event pushed to connected clients (Reverb/WebSocket).
 * Mirrors the compact SSE bus payloads so the front-end handlers can be reused.
 * Broadcasts on a PRIVATE channel: only authenticated portal sessions may
 * subscribe (authorization in routes/channels.php).
 */
class SubSyncEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $eventName,
        public readonly array $payload = [],
        public readonly string $channelName = 'subsync',
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel($this->channelName)];
    }

    public function broadcastAs(): string
    {
        return $this->eventName;
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
