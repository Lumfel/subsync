<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $channel
 * @property string $event
 * @property string|null $data
 * @property \Illuminate\Support\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SseEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SseEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SseEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SseEvent whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SseEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SseEvent whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SseEvent whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SseEvent whereId($value)
 * @mixin \Eloquent
 */
class SseEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['channel', 'event', 'data'];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Append a compact event to the bus. Cheap single INSERT — much faster
     * than letting every SSE client poll the source tables every second.
     */
    public static function emit(string $channel, string $event, array $data = []): void
    {
        static::create([
            'channel' => $channel,
            'event'   => $event,
            'data'    => $data,
        ]);

        // Prune the bus at most once per minute to keep the DELETE cheap.
        if (!\Illuminate\Support\Facades\Cache::has('sse.bus.pruned')) {
            static::where('created_at', '<', now()->subMinutes(10))->delete();
            \Illuminate\Support\Facades\Cache::put('sse.bus.pruned', true, 60);
        }
    }
}