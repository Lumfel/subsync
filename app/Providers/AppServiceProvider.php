<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Facility;
use App\Models\FinancialRecord;
use App\Models\HoaExpense;
use App\Models\HoaPolicy;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\IssueReport;
use App\Models\Media;
use App\Models\Officer;
use App\Models\PaymentStatus;
use App\Models\Recommendation;
use App\Models\Resident;
use App\Observers\DashboardCacheObserver;
use App\Observers\SsePublishingObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Dev only: surface N+1 queries + silent attribute issues immediately
        // so slow queries get fixed instead of accumulating silently.
        if ($this->app->isLocal()) {
            Model::shouldBeStrict();
        }

        // Apply SQLite performance PRAGMAs (WAL, NORMAL sync) as a safety net
        // even if config is cached with older values.
        if (getenv('PRAGMA_WAL') === '1') {
            try {
                DB::connection('sqlite')->statement('PRAGMA journal_mode = wal');
                DB::connection('sqlite')->statement('PRAGMA synchronous = normal');
                DB::connection('sqlite')->statement('PRAGMA busy_timeout = 5000');
            } catch (\Throwable $e) {
                // non-critical
            }
        }

        $models = [
            Resident::class,
            Officer::class,
            Household::class,
            HouseholdMember::class,
            IssueReport::class,
            FinancialRecord::class,
            Announcement::class,
            Recommendation::class,
            PaymentStatus::class,
            HoaExpense::class,
            HoaPolicy::class,
            Facility::class,
            Media::class,
        ];

        foreach ($models as $model) {
            $model::observe(DashboardCacheObserver::class);
        }

        // Publish real-time SSE events for the hot tables the frontends listen to.
        $sseModels = [
            Announcement::class,
            IssueReport::class,
            HoaPolicy::class,
            HoaExpense::class,
        ];

        foreach ($sseModels as $model) {
            $model::observe(SsePublishingObserver::class);
        }
    }
}