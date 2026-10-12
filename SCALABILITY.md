# SubSync — Scalability Assessment & Plan

Status: **not yet implemented** — logged for later. Prepared 2026-10-03.

## Current architecture (facts)
- Single machine: **Apache (winnt MPM, `ThreadsPerChild 150`) + mod_php + SQLite**.
- `.env`: `DB_CONNECTION=sqlite`, `SESSION_DRIVER=database`, `CACHE_STORE=database`,
  `QUEUE_CONNECTION=database`, `BROADCAST_DRIVER=pusher` (placeholder/unused).
- WAL + `busy_timeout` PRAGMAs are gated behind `PRAGMA_WAL=1` in
  `app/Providers/AppServiceProvider.php` — **that env var is NOT set**, so they don't run.
- Real-time: PHP SSE (`app/Http/Controllers/StreamController.php`), `$maxSeconds = 10`
  (holds an Apache worker/thread per connected client for the whole cycle).
- Clients refresh on `reconnect` (admin/officer/resident) so updates still land when a
  proxy (ngrok) buffers intermediate SSE chunks.

## Realistic capacity
- Current: **~10–30 concurrent active users** (single subdivision) — fine for the capstone demo.
- Not designed for hundreds/thousands concurrent.

## Bottlenecks (ranked)
1. **SQLite + DB-backed session/cache/queue.** One writer at a time; every request adds
   session read/write + cache hits on the same file. WAL/busy-timeout disabled → risk of
   `database is locked` under concurrent writes.
2. **SSE on mod_php ties a worker thread per client for ~10s.** With `ThreadsPerChild 150`,
   ~150 concurrent streams (fewer when mixed with normal requests) saturate Apache and queue
   new requests. Classic PHP long-poll/SSE wall.
3. **10s refresh cycle** = each client hits a heavy endpoint (`/api/dashboard-data`) every ~10s.
   ~10 req/s at 100 clients, against SQLite.
4. **Single node** — SQLite + local file storage + DB sessions prevent horizontal scaling.

## What's already good
Eager loading + aggregated `/api/dashboard-data` (N+1 handled), cached read endpoints,
indexed queries, small payloads, SSE instead of 1s polling.

---

## Plan (staged)

### Tier 0 — do now, no new infra (biggest bang)  [DONE 2026-10-03]
- [x] **Enable WAL + busy_timeout** unconditionally — removed the `PRAGMA_WAL` gate in `AppServiceProvider`; applied whenever the default connection is SQLite (verified `journal_mode = wal`).
- [x] **Move session + cache off the DB**: `SESSION_DRIVER=file`, `CACHE_STORE=file` in `.env` (verified end-to-end: login + authenticated request; session files written).
- Expected: **~2–3× headroom** (~50–100 concurrent lightly-active users).
- Note: switching the session driver logs out existing sessions once (browsers must sign in again). Tests unaffected (phpunit overrides drivers to `array`).

### Tier 1 — one small VPS
- [ ] MySQL/MariaDB (or Postgres) instead of SQLite (concurrent writes).
- [ ] Redis for session, cache, queue.
- [ ] nginx + PHP-FPM instead of mod_php (better worker management). (SSE still ties a worker.)

### Tier 2 — real-time at scale (the real fix for SSE)  [REVERB ADDED 2026-10-03]
- [x] **Laravel Reverb installed** (`laravel/reverb`), wired end-to-end: a generic
      `App\Events\SubSyncEvent` (public `subsync` channel) is dispatched from `SsePublishingObserver`
      for every announcement/issue/policy/hoa-expense change; the front-end loads `window.Echo`
      (built via Vite, `@vite('resources/js/app.js')`) and each portal refreshes on events.
      Verified end-to-end (WS client subscribed to `subsync` and received a live event).
- [ ] Retire the SSE endpoint + the reconnect-refresh fallback once Reverb is the sole real-time path
      (kept for now as a fallback — SSE still works if Reverb is offline).
- Run the WS server with `php artisan reverb:start` (now part of `composer run dev`). Prod: `reverb:start`
  via a supervisor + `REVERB_SCALING_ENABLED=true` + Redis for multi-node scaling.

### Tier 3 — horizontal scale
- [ ] Multiple stateless app servers behind a load balancer.
- [ ] Redis for shared session/cache; DB primary + read replicas.
- [ ] Object storage (S3) for uploads; CDN for assets.

---

## Bottom line
For a single HOA / capstone demo, the current setup is fine. For public/real use:
**Tier 0 now → Tier 1 + Reverb when moving to a VPS.**

## Next action when resuming
**Tier 0 is done.** Next: **Tier 1** (MySQL/Postgres + Redis + PHP-FPM on a VPS), then **Tier 2**
(replace PHP SSE with Laravel Reverb / Soketi over Redis pub/sub).
