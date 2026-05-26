<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    private function residentId(Request $request): ?int
    {
        return Auth::guard('resident')->id() ?? $request->session()->get('resident_id');
    }

    /** GET /api/recommendations — all (admin) */
    public function index()
    {
        $recs = Recommendation::with('resident:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'resident'    => $r->resident?->name ?? '—',
                'category'    => $r->category,
                'title'       => $r->title,
                'description' => $r->description,
                'status'      => $r->status,
                'created_at'  => $r->created_at?->format('M d, Y'),
            ]);

        return response()->json($recs);
    }

    /** GET /api/recommendations/my — resident's own */
    public function myRecs(Request $request)
    {
        $residentId = $this->residentId($request);

        if (!$residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $recs = Recommendation::where('resident_id', $residentId)
            ->orderByDesc('created_at')
            ->get(['id', 'category', 'title', 'description', 'status', 'created_at']);

        return response()->json($recs);
    }

    /** POST /api/recommendations — resident submits */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'required|string',
            'category'    => 'nullable|string|max:100',
        ]);

        $residentId = $this->residentId($request);

        if (!$residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $data['resident_id'] = $residentId;
        $data['status']      = 'Pending';
        $data['created_at']  = now();

        $rec = Recommendation::create($data);

        return response()->json(['success' => true, 'recommendation' => [
            'id'          => $rec->id,
            'category'    => $rec->category,
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
