<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Archive past events / old notices instead of deleting them.
Schedule::command('announcements:archive-stale')->daily();

// Generate dues/assessment charges at the start of each month.
Schedule::command('assessments:generate-dues')->monthlyOn(1, '00:05');

// Publish the monthly financial transparency digest to all residents on the 1st of each month.
Schedule::command('subsync:financial-digest')->monthlyOn(1, '06:00');

// Monthly balance reminder to every resident with an outstanding balance —
// a few days after dues post, so the new charge is already included.
Schedule::command('subsync:remind-balances')->monthlyOn(5, '08:00');

// Archive issues that have been resolved for more than 30 days (never deletes).
Schedule::command('issues:purge-resolved')->daily();

// Nightly database backup (storage/app/backups, keeps the last 14).
Schedule::command('subsync:backup-database')->dailyAt('02:00');
Schedule::command('subsync:prune-activity-logs --days=180')->monthlyOn(1, '03:30');
