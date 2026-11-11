<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;

/**
 * Automatically archive finished announcements instead of deleting them:
 *  - community events whose date has passed, and
 *  - old notices created more than 90 days ago.
 * Records are kept (member view history etc.); they're just moved to the
 * Archived tab where staff can restore them.
 */
class ArchiveStaleAnnouncements extends Command
{
    protected $signature = 'announcements:archive-stale';

    protected $description = 'Archive announcements whose event has passed or that are older than 90 days (never deletes).';

    public function handle(): int
    {
        $eventCutoff = now()->subDays(7);
        $ageCutoff = now()->subDays(90);

        // Iterate the models instead of a query-builder update so each change emits
        // its events: activity log, realtime/SSE push, and cache invalidation.
        $stale = Announcement::where('status', 'Active')
            ->where(function ($query) use ($eventCutoff, $ageCutoff) {
                $query->where(fn ($q) => $q->whereNotNull('event_date')->where('event_date', '<', $eventCutoff))
                    ->orWhere(fn ($q) => $q->whereNull('event_date')->where('created_at', '<', $ageCutoff));
            })
            ->get();

        $archived = 0;
        foreach ($stale as $announcement) {
            $announcement->update(['status' => 'Archived']);
            $archived++;
        }

        $this->info("Archived {$archived} stale announcement(s).");

        return self::SUCCESS;
    }
}
