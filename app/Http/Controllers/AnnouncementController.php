<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AnnouncementController extends Controller
{
    private function residentId(Request $request): ?int
    {
        return Auth::guard('resident')->id() ?? $request->session()->get('resident_id');
    }

    /** GET /api/announcements — all announcements (newest first) */
    public function index(Request $request)
    {
        // Only staff may look beyond the Active feed (Archived / all).
        $isStaff = session('admin_id') || Auth::guard('officer')->check();
        $statusFilter = $isStaff ? $request->query('status', 'Active') : 'Active';

        return Cache::remember("announcements.{$statusFilter}", 30, function () use ($statusFilter) {
            $query = Announcement::with(['admin:id,name', 'officer:id,name'])
                ->orderByDesc('created_at')
                ->limit(50);

            // Residents only see Active; admin/officer can pass ?status=all or ?status=Archived
            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }

            return $query->get()->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => $a->content,
                'tag' => $a->tag ?? 'notice',
                'target' => $a->target ?? 'All Residents',
                'priority' => $a->priority ?? 'Normal',
                'event_date' => $a->event_date,
                'status' => $a->status ?? 'Active',
                'posted_by' => $a->admin?->name ?? $a->officer?->name ?? 'Admin',
                'created_at' => $a->created_at?->format('M d, Y g:i A'),
            ])->all();
        });
    }

    /** POST /api/announcements — create (admin or officer) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'tag' => 'sometimes|in:notice,urgent,event',
            'target' => 'sometimes|string|max:50',
            'priority' => 'sometimes|in:Normal,High',
            'event_date' => 'nullable|date',
            'status' => 'sometimes|in:Active,Draft',
        ]);

        $data['tag'] = $data['tag'] ?? 'notice';
        $data['target'] = $data['target'] ?? 'All Residents';
        $data['priority'] = $data['priority'] ?? 'Normal';
        $data['status'] = $data['status'] ?? 'Active';

        // Determine poster: admin or officer
        if (session('admin_id')) {
            $data['admin_id'] = session('admin_id');
        } elseif (Auth::guard('officer')->check()) {
            $data['officer_id'] = Auth::guard('officer')->id();
        }

        $announcement = Announcement::create($data);

        if (Auth::guard('officer')->check()) {
            $posterName = Auth::guard('officer')->user()->name;
        } else {
            $posterName = session('admin_name', 'Admin');
        }

        return response()->json(['success' => true, 'announcement' => [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'content' => $announcement->content,
            'tag' => $announcement->tag,
            'target' => $announcement->target,
            'priority' => $announcement->priority,
            'event_date' => $announcement->event_date,
            'status' => $announcement->status,
            'posted_by' => $posterName,
            'created_at' => $announcement->created_at->format('M d, Y g:i A'),
        ]]);
    }

    /** DELETE /api/announcements/{id} */
    public function destroy(int $id)
    {
        Announcement::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    /** PATCH /api/announcements/{id}/archive — toggle Active ↔ Archived */
    public function archive(int $id)
    {
        $announcement = Announcement::findOrFail($id);
        $newStatus = $announcement->status === 'Active' ? 'Archived' : 'Active';
        $announcement->update(['status' => $newStatus]);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    /** PATCH /api/announcements/{id}/publish — move a Draft onto the live feed */
    public function publish(int $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update(['status' => 'Active']);

        return response()->json(['success' => true, 'status' => 'Active']);
    }

    /** POST /api/announcements/{id}/view — mark as viewed by resident */
    public function markViewed(Request $request, int $id)
    {
        $residentId = $this->residentId($request);

        if ($residentId) {
            AnnouncementView::firstOrCreate([
                'resident_id' => $residentId,
                'announcement_id' => $id,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
