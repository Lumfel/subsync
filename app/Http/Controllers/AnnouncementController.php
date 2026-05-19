<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /** GET /api/announcements — all announcements (newest first) */
    public function index()
    {
        $announcements = Announcement::with(['admin:id,name', 'officer:id,name'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($a) => [
                'id'         => $a->id,
                'title'      => $a->title,
                'content'    => $a->content,
                'tag'        => $a->tag ?? 'notice',
                'posted_by'  => $a->admin?->name ?? $a->officer?->name ?? 'Admin',
                'created_at' => $a->created_at?->format('M d, Y'),
            ]);

        return response()->json($announcements);
    }

    /** POST /api/announcements — create (admin or officer) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:200',
            'content' => 'required|string',
            'tag'     => 'sometimes|in:notice,urgent,event',
        ]);

        $data['tag'] = $data['tag'] ?? 'notice';

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
            'id'         => $announcement->id,
            'title'      => $announcement->title,
            'content'    => $announcement->content,
            'tag'        => $announcement->tag,
            'posted_by'  => $posterName,
            'created_at' => $announcement->created_at->format('M d, Y'),
        ]]);
    }

    /** DELETE /api/announcements/{id} */
    public function destroy(int $id)
    {
        Announcement::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /** POST /api/announcements/{id}/view — mark as viewed by resident */
    public function markViewed(int $id)
    {
        if (Auth::guard('resident')->check()) {
            AnnouncementView::firstOrCreate([
                'resident_id'     => Auth::guard('resident')->id(),
                'announcement_id' => $id,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
