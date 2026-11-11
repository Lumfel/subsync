<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class DashboardCacheObserver
{
    public function created($model): void
    {
        $this->clearDashboardCache();
    }

    public function updated($model): void
    {
        $this->clearDashboardCache();
    }

    public function deleted($model): void
    {
        $this->clearDashboardCache();
    }

    protected function clearDashboardCache(): void
    {
        foreach (self::cacheKeys() as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Every cache key this system uses for aggregated / hot GET data.
     * Centralised here so any model change can invalidate everything cheaply.
     */
    public static function cacheKeys(): array
    {
        return [
            'admin.dashboard.data',
            'resident.stats',
            'hoa.expenses',
            'hoa.policies',
            'map.all',
            'officers.list',
            'announcements.Active',
            'announcements.all',
            'announcements.Archived',
            'announcements.Draft',
        ];
    }
}
