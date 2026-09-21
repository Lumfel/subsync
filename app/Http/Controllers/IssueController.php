<?php

namespace App\Http\Controllers;

use App\Models\IssueReport;
use App\Models\IssueResponse;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IssueController extends Controller
{
    private function residentId(Request $request): ?int
    {
        return Auth::guard('resident')->id() ?? $request->session()->get('resident_id');
    }

    /** GET /api/issues — all issues (admin/officer) */
    public function index(Request $request)
    {
        $query = IssueReport::with('resident:id,name,house_id', 'resident.household:id,block_lot_number', 'media', 'responses')
            ->orderByDesc('created_at');

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->query('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }

        if ($request->query('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        $issues = $query->get()->map(fn($i) => [
            'id'         => $i->id,
            'category'   => $i->category,
            'title'      => $i->title,
            'description'=> $i->description,
            'status'     => $i->status,
            'priority'   => $i->priority,
            'latitude'   => $i->latitude,
            'longitude'  => $i->longitude,
            'created_at' => $i->created_at?->format('M d, Y'),
            'resolved_at' => $i->resolved_at?->format('Y-m-d'),
            'resident'   => $i->resident?->name ?? '—',
            'block_lot'  => $i->resident?->household?->block_lot_number ?? '—',
            'media'      => $i->media->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->file_name,
                'url' => Storage::url($m->file_path),
            ]),
            'responses'  => $i->responses->map(fn($r) => [
                'id'       => $r->id,
                'content'  => $r->response_content,
                'type'     => $r->responder_type,
            ]),
        ]);

        return response()->json($issues);
    }

    /** GET /api/issues/my — resident's own issues */
    public function myIssues(Request $request)
    {
        $residentId = $this->residentId($request);

        if (!$residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $issues = IssueReport::where('resident_id', $residentId)
            ->with('responses', 'media')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($i) => [
                'id'          => $i->id,
                'category'    => $i->category,
                'title'       => $i->title,
                'description' => $i->description,
                'status'      => $i->status,
                'priority'    => $i->priority,
                'created_at'  => $i->created_at?->format('M d, Y'),
                'resolved_at' => $i->resolved_at?->format('Y-m-d'),
                'response'    => $i->responses->first()?->response_content,
                'media'       => $i->media->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->file_name,
                    'url' => Storage::url($m->file_path),
                ]),
            ]);

        return response()->json($issues);
    }

    /** POST /api/issues — resident submits */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category'    => 'required|string|max:100',
            'title'       => 'required|string|max:200',
            'description' => 'required|string',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|max:4096',
            'documents'   => 'nullable|array|max:5',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $residentId = $this->residentId($request);

        if (!$residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        if (isset($data['latitude'], $data['longitude']) && !self::withinArea($data['latitude'], $data['longitude'])) {
            return response()->json(['success' => false, 'message' => 'The report location must be within Terra Nova Subdivision.'], 422);
        }

        $data['resident_id'] = $residentId;
        $data['status']      = 'Pending';
        unset($data['images'], $data['documents']);

        $issue = IssueReport::create($data);

        // Handle uploaded images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('issues', 'public');
                Media::create([
                    'uploader_id'   => $residentId,
                    'uploader_type' => 'Resident',
                    'entity_type'   => 'IssueReport',
                    'entity_id'     => $issue->id,
                    'file_name'     => $file->getClientOriginalName(),
                    'file_path'     => $path,
                ]);
            }
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('issues/documents', 'public');
                Media::create([
                    'uploader_id' => $residentId,
                    'uploader_type' => 'Resident',
                    'entity_type' => 'IssueReport',
                    'entity_id' => $issue->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                ]);
            }
        }

        $issue->load('media');

        return response()->json(['success' => true, 'issue' => [
            'id'          => $issue->id,
            'category'    => $issue->category,
            'title'       => $issue->title,
            'description' => $issue->description,
            'status'      => $issue->status,
            'priority'    => null,
            'created_at'  => $issue->created_at->format('M d, Y'),
            'response'    => null,
            'media'       => $issue->media->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->file_name,
                'url' => Storage::url($m->file_path),
            ]),
        ]]);
    }

    /** PUT /api/issues/{id}/status — admin updates status */
    public function updateStatus(Request $request, int $id)
    {
        $issue = IssueReport::findOrFail($id);
        $data  = $request->validate(['status' => 'required|in:Pending,In Progress,Resolved']);

        // Start the 30-day auto-delete timer when an issue is resolved; clear it otherwise.
        $data['resolved_at'] = $data['status'] === 'Resolved' ? now() : null;

        $issue->update($data);

        return response()->json(['success' => true]);
    }

    /** PUT /api/issues/{id}/priority — admin/officer sets priority */
    public function setPriority(Request $request, int $id)
    {
        $issue = IssueReport::findOrFail($id);
        $data  = $request->validate(['priority' => 'required|in:Low,Medium,High,Critical']);
        $issue->update($data);

        return response()->json(['success' => true, 'priority' => $issue->priority]);
    }

    /** GET /api/issues/urgent-check — lightweight poll for critical/high unresolved issues */
    public function urgentCheck()
    {
        $urgent = IssueReport::whereIn('priority', ['Critical', 'High'])
            ->where('status', '!=', 'Resolved')
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'count'     => IssueReport::whereIn('priority', ['Critical', 'High'])->where('status', '!=', 'Resolved')->count(),
            'latest_id' => $urgent?->id,
            'latest'    => $urgent ? [
                'id'       => $urgent->id,
                'title'    => $urgent->title,
                'category' => $urgent->category,
                'priority' => $urgent->priority,
                'resident' => $urgent->resident?->name ?? '—',
            ] : null,
        ]);
    }

    /** POST /api/issues/{id}/respond — officer or admin responds */
    public function respond(Request $request, int $id)
    {
        $data = $request->validate([
            'content' => 'required|string',
        ]);

        IssueReport::findOrFail($id); // ensure it exists

        $responseData = ['response_content' => $data['content'], 'issue_id' => $id];

        if (Auth::guard('officer')->check()) {
            $responseData['officer_id']     = Auth::guard('officer')->id();
            $responseData['responder_type'] = 'officer';
        } else {
            $responseData['admin_id']       = session('admin_id');
            $responseData['responder_type'] = 'admin';
        }

        $response = IssueResponse::create($responseData);

        // Auto-update status to In Progress if still Pending
        IssueReport::where('id', $id)->where('status', 'Pending')
            ->update(['status' => 'In Progress']);

        return response()->json(['success' => true, 'response' => $response]);
    }

    private static function withinArea(float|int $latitude, float|int $longitude): bool
    {
        $b = MapController::bounds();
        return $latitude >= $b['min_lat']
            && $latitude <= $b['max_lat']
            && $longitude >= $b['min_lng']
            && $longitude <= $b['max_lng'];
    }
}
