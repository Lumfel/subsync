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

// Archive issues that have been resolved for more than 30 days (never deletes).
Schedule::command('issues:purge-resolved')->daily();
