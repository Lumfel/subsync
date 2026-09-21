<?php

namespace App\Console\Commands;

use App\Models\IssueReport;
use App\Models\IssueResponse;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeResolvedIssues extends Command
{
    protected $signature = 'issues:purge-resolved';
    protected $description = 'Delete issues that have been resolved for more than 30 days.';

    public function handle(): int
    {
        $cutoff = now()->subDays(30);
        $expired = IssueReport::where('status', 'Resolved')
            ->where('updated_at', '<', $cutoff)
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No resolved issues older than 30 days to purge.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($expired as $issue) {
            // Delete uploaded media files, then rows
            foreach ($issue->media as $media) {
                if ($media->file_path) {
                    Storage::disk('public')->delete($media->file_path);
                }
            }
            Media::where('entity_type', 'IssueReport')->where('entity_id', $issue->id)->delete();
            IssueResponse::where('issue_id', $issue->id)->delete();
            $issue->delete();
            $count++;
        }

        $this->info("Purged {$count} resolved issue(s) older than 30 days.");

        return self::SUCCESS;
    }
}