<?php

namespace App\Http\Controllers;

use App\Models\IssueReport;
use App\Models\SseEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StreamController extends Controller
{
    /**
     * GET /api/stream/updates
     *
     * Server-Sent Events endpoint. Holds the HTTP connection open and pushes a
     * small JSON event to the browser the moment something changes — no polling
     * of the source tables. Observers append compact events to the `sse_events`
     * bus, and this endpoint simply tails that bus (1 tiny indexed query/sec).
     *
     * Query params (sent by JS on connection):
     *   lastEventId  — highest sse_events.id the client already knows about
     *
     * Backward-compatible params (still accepted, used to seed lastEventId):
     *   lastAnnId / lastIssueId / lastPolicyId
     */
    public function updates(Request $request)
    {
        $lastEventId = (int) $request->query('lastEventId', 0);

        // Backward compatibility: if a fresh client connects without lastEventId,
        // it will get a "sync" event telling it to load fresh data once.
        $freshConnect = $lastEventId === 0;

        // Role-aware visibility (same rules as the private WebSocket channel):
        // residents receive community events plus their own records only.
        $me = [
            'role' => session('admin_id') ? 'admin' : (Auth::guard('officer')->check() ? 'officer' : (Auth::guard('resident')->check() ? 'resident' : 'guest')),
            'id' => (int) (Auth::guard('officer')->id() ?? Auth::guard('resident')->id() ?? 0),
        ];

        // Tests (and flaky clients) may shorten the hold window: ?max=1
        $maxSeconds = min(5, max(1, (int) $request->query('max', 5)));

        // CRITICAL: Save and release the Laravel session lock immediately!
        // If we don't do this, the session driver will lock the session for the
        // entire duration of this SSE connection, blocking the user from loading
        // any other pages or making API calls in other tabs.
        $request->session()->save();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return response()->stream(
            function () use ($lastEventId, $freshConnect, $me, $maxSeconds) {
                set_time_limit(0);
                ignore_user_abort(false);

                while (ob_get_level() > 0) {
                    $status = ob_get_status();

                    // Only drain PHP's own SAPI buffering (ini output_buffering,
                    // handler name "default output handler"). Never pop callback
                    // buffers — the test harness wraps the stream in one and
                    // expects to collect it afterwards.
                    if (! $status || ($status['name'] ?? '') !== 'default output handler') {
                        break;
                    }

                    ob_end_clean();
                }

                // Send an immediate sync so brand-new tabs load fresh data right away.
                if ($freshConnect) {
                    echo "event: sync\n";
                    echo 'data: '.json_encode(['reason' => 'fresh'])."\n\n";
                    flush();
                }

                $elapsed = 0;
                $lastId = $lastEventId;

                while ($elapsed < $maxSeconds) {
                    if (connection_aborted()) {
                        break;
                    }

                    try {
                        // Single lightweight, indexed query on the tiny event bus.
                        $events = SseEvent::where('id', '>', $lastId)
                            ->orderBy('id')
                            ->limit(100)
                            ->get(['id', 'channel', 'event', 'data']);

                        foreach ($events as $evt) {
                            $payload = $evt->data ?? [];

                            // Skip events this role must not see (still advance the cursor).
                            if (! $this->deliverable($evt->event, is_array($payload) ? $payload : [], $me)) {
                                $lastId = $evt->id;

                                continue;
                            }

                            if ($evt->channel === 'issue_reports' && $evt->event === 'issue') {
                                // Include urgent flag without a 4th query: recompute only
                                // on issue events, and only for unresolved high/critical.
                                $payload['urgent'] = $this->isUrgentIssue((int) ($payload['latestId'] ?? 0));
                            }

                            echo "event: {$evt->event}\n";
                            echo 'data: '.json_encode($payload)."\n\n";

                            $lastId = $evt->id;
                        }
                    } catch (\Throwable $e) {
                        // DB hiccup — skip this tick silently
                    }

                    echo ": ping\n\n";
                    flush();

                    sleep(1);
                    $elapsed++;
                }

                // Tell the client to reconnect immediately (passing the last event ID).
                echo "event: reconnect\n";
                echo 'data: '.json_encode(['lastEventId' => $lastId])."\n\n";
                flush();
            },
            200,
            [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'X-Accel-Buffering' => 'no',
                'Connection' => 'keep-alive',
            ]
        );
    }

    /**
     * Role-aware visibility for bus events (mirrors the private WS channel).
     * Residents get community events plus their OWN records; staff get all;
     * connections without a session get nothing.
     */
    protected function deliverable(string $event, array $payload, array $me): bool
    {
        if ($me['role'] === 'admin' || $me['role'] === 'officer') {
            return true;
        }

        if ($me['role'] !== 'resident') {
            return false;
        }

        return match ($event) {
            'officer', 'officer-file' => false,
            'payment', 'recommendation' => (int) ($payload['residentId'] ?? 0) === $me['id'],
            'resident' => (int) ($payload['latestId'] ?? 0) === $me['id'],
            default => true,
        };
    }

    /**
     * Cheap check — only called when an issue event actually fires, so the
     * extra query cost is negligible compared to the old per-second poll.
     */
    protected function isUrgentIssue(int $issueId): bool
    {
        if ($issueId <= 0) {
            return false;
        }

        return IssueReport::where('id', $issueId)
            ->whereIn('priority', ['Critical', 'High'])
            ->where('status', '!=', 'Resolved')
            ->exists();
    }
}
