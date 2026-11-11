<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    private function isStaff(): bool
    {
        return (bool) session('admin_id') || Auth::guard('officer')->check();
    }

    /** GET /api/events — upcoming events (all roles) with RSVP counts + my RSVP */
    public function index()
    {
        $residentId = Auth::guard('resident')->id();

        // Counts come back with the rows (no per-event queries).
        $events = Event::where('starts_at', '>=', now()->subDay())
            ->withCount([
                'rsvps as going_count' => fn ($q) => $q->where('status', 'going'),
                'rsvps as maybe_count' => fn ($q) => $q->where('status', 'maybe'),
            ])
            ->orderBy('starts_at')
            ->get();

        // One lookup for the resident's own RSVP states.
        $myStatuses = $residentId
            ? EventRsvp::where('resident_id', $residentId)
                ->whereIn('event_id', $events->pluck('id'))
                ->pluck('status', 'event_id')
            : collect();

        return response()->json($events->map(fn ($e) => [
            'id' => $e->id,
            'title' => $e->title,
            'description' => $e->description,
            'location' => $e->location,
            'starts_at' => $e->starts_at?->format('M d, Y g:i A'),
            'ends_at' => $e->ends_at?->format('M d, Y g:i A'),
            'going' => (int) $e->going_count,
            'maybe' => (int) $e->maybe_count,
            'my_status' => $myStatuses[$e->id] ?? null,
        ]));
    }

    /** POST /api/events — staff creates an event */
    public function store(Request $request)
    {
        if (! $this->isStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:200',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['created_by_type'] = session('admin_id') ? 'admin' : 'officer';
        $data['created_by_id'] = session('admin_id') ?: Auth::guard('officer')->id();

        $event = Event::create($data);

        return response()->json(['success' => true, 'event' => ['id' => $event->id, 'title' => $event->title]]);
    }

    /** PUT /api/events/{id} — staff updates an event */
    public function update(Request $request, int $id)
    {
        if (! $this->isStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:200',
            'starts_at' => 'sometimes|required|date',
            'ends_at' => 'nullable|date',
        ]);

        $event->update($data);

        return response()->json(['success' => true]);
    }

    /** DELETE /api/events/{id} */
    public function destroy(int $id)
    {
        if (! $this->isStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Event::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    /** POST /api/events/{id}/rsvp — resident sets their RSVP */
    public function rsvp(Request $request, int $id)
    {
        $resident = Auth::guard('resident')->user();
        if (! $resident) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        Event::findOrFail($id);
        $data = $request->validate(['status' => 'required|in:going,maybe,not_going']);

        EventRsvp::updateOrCreate(
            ['event_id' => $id, 'resident_id' => $resident->id],
            ['status' => $data['status']]
        );

        return response()->json(['success' => true]);
    }
}
