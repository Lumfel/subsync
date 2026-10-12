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
use App\Models\Recommendation;
use App\Models\Resident;
use App\Observers\DashboardCacheObserver;
use App\Observers\SsePublishingObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Route id params are always numeric DB keys; constrain them so a
        // malformed id yields 404 instead of a 500 TypeError from int-typed
        // controller args (e.g. /api/officers/abc).
        Route::pattern('id', '[0-9]+');
        Route::pattern('residentId', '[0-9]+');
        Route::pattern('convId', '[0-9]+');
        Route::pattern('templateId', '[0-9]+');

        // Dev only: surface N+1 queries + silent attribute issues immediately
        // so slow queries get fixed instead of accumulating silently.
        if ($this->app->isLocal()) {
            Model::shouldBeStrict();
        }

        // Apply SQLite performance PRAGMAs (WAL, NORMAL sync, busy timeout) so concurrent
        // reads/writes don't block. No-op/ignored for non-SQLite or :memory: databases.
        if (config('database.default') === 'sqlite') {
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
