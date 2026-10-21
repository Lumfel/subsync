<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    /** GET /api/activity-logs — recent activity trail (admin only). */
    public function index()
    {
        if (! session('admin_id')) {
            abort(403);
        }

        $logs = ActivityLog::orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'actor' => $l->actor_name ?: ucfirst($l->actor_type ?: 'System'),
                'action' => $l->action,
                'description' => $l->description,
                'time' => $l->created_at?->diffForHumans(),
                'at' => $l->created_at?->format('M d, Y g:i A'),
            ]);

        return response()->json(['logs' => $logs]);
    }
}
