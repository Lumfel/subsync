<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    protected $signature = 'subsync:prune-activity-logs {--days=180 : Retention window in days} {--dry-run : Report what would be deleted}';

    protected $description = 'Prune activity-log entries older than the retention window (RA 10173 data minimization).';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $query = ActivityLog::where('created_at', '<', $cutoff);

        if ($this->option('dry-run')) {
            $this->info("Would prune {$query->count()} entr".($query->count() === 1 ? 'y' : 'ies')." older than {$cutoff->toDateString()}.");

            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Pruned {$deleted} activity-log entr".($deleted === 1 ? 'y' : 'ies')." older than {$cutoff->toDateString()}.");

        return self::SUCCESS;
    }
}
