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

        $expiredEvents = Announcement::where('status', 'Active')
            ->whereNotNull('event_date')
            ->where('event_date', '<', $eventCutoff)
            ->update(['status' => 'Archived']);

        $oldNotices = Announcement::where('status', 'Active')
            ->whereNull('event_date')
            ->where('created_at', '<', $ageCutoff)
            ->update(['status' => 'Archived']);

        $this->info('Archived '.($expiredEvents + $oldNotices).' stale announcement(s).');

        return self::SUCCESS;
    }
}
