<?php

namespace App\Console\Commands;

use App\Models\IssueReport;
use App\Models\Media;
use Illuminate\Console\Command;

class PurgeResolvedIssues extends Command
{
    protected $signature = 'issues:purge-resolved';

    protected $description = 'Archive issues that have been resolved for more than 30 days.';

    public function handle(): int
    {
        $cutoff = now()->subDays(30);
        // resolved_at is the authority for "resolved for more than 30 days";
        // using updated_at let any later edit (status, priority, assignment,
        // due date) reset the clock and stop an issue from ever archiving.
        $expired = IssueReport::where('status', 'Resolved')
            ->whereNull('archived_at')
            ->where('resolved_at', '<', $cutoff)
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No resolved issues older than 30 days to archive.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($expired as $issue) {
            // Keep the full record (responses, media) — just move it out of the working list.
            $issue->update(['archived_at' => now()]);
            $count++;
        }

        $this->info("Archived {$count} resolved issue(s) older than 30 days.");

        return self::SUCCESS;
    }
}
