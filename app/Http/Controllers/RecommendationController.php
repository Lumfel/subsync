<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    /** GET /api/recommendations — all (admin) */
    public function index()
    {
        $recs = Recommendation::with('resident:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'resident'    => $r->resident?->name ?? '—',
                'title'       => $r->title,
                'description' => $r->description,
                'status'      => $r->status,
                'created_at'  => $r->created_at?->format('M d, Y'),
            ]);

        return response()->json($recs);
    }

    /** GET /api/recommendations/my — resident's own */
    public function myRecs()
    {
        $recs = Recommendation::where('resident_id', Auth::guard('resident')->id())
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'description', 'status', 'created_at']);

        return response()->json($recs);
    }

    /** POST /api/recommendations — resident submits */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'required|string',
        ]);

        $data['resident_id'] = Auth::guard('resident')->id();
        $data['status']      = 'Pending';
        $data['created_at']  = now();

        $rec = Recommendation::create($data);

        return response()->json(['success' => true, 'recommendation' => [
            'id'          => $rec->id,
            'title'       => $rec->title,
            'description' => $rec->description,
            'status'      => $rec->status,
            'created_at'  => $rec->created_at?->format('M d, Y') ?? now()->format('M d, Y'),
        ]]);
    }

    /** PUT /api/recommendations/{id}/status — admin updates status */
    public function updateStatus(Request $request, int $id)
    {
        $rec  = Recommendation::findOrFail($id);
        $data = $request->validate(['status' => 'required|in:Pending,Reviewed,Approved,Rejected']);
        $rec->update($data);

        return response()->json(['success' => true]);
    }
}
