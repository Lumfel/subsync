<?php

namespace App\Http\Controllers;

use App\Models\IssueReport;
use App\Models\IssueResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IssueController extends Controller
{
    /** GET /api/issues — all issues (admin/officer) */
    public function index(Request $request)
    {
        $query = IssueReport::with('resident:id,name,house_id', 'resident.household:id,block_lot_number')
            ->orderByDesc('created_at');

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
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
            'resident'   => $i->resident?->name ?? '—',
            'block_lot'  => $i->resident?->household?->block_lot_number ?? '—',
            'responses'  => $i->responses->map(fn($r) => [
                'id'       => $r->id,
                'content'  => $r->response_content,
                'type'     => $r->responder_type,
            ]),
        ]);

        return response()->json($issues);
    }

    /** GET /api/issues/my — resident's own issues */
    public function myIssues()
    {
        $residentId = Auth::guard('resident')->id();

        $issues = IssueReport::where('resident_id', $residentId)
            ->with('responses')
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
                'response'    => $i->responses->first()?->response_content,
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
            'priority'    => 'nullable|in:Low,Medium,High,Critical',
        ]);

        $data['resident_id'] = Auth::guard('resident')->id();
        $data['status']      = 'Pending';

        $issue = IssueReport::create($data);

        return response()->json(['success' => true, 'issue' => [
            'id'          => $issue->id,
            'category'    => $issue->category,
            'title'       => $issue->title,
            'description' => $issue->description,
            'status'      => $issue->status,
            'priority'    => $issue->priority,
            'created_at'  => $issue->created_at->format('M d, Y'),
            'response'    => null,
        ]]);
    }

    /** PUT /api/issues/{id}/status — admin updates status */
    public function updateStatus(Request $request, int $id)
    {
        $issue = IssueReport::findOrFail($id);
        $data  = $request->validate(['status' => 'required|in:Pending,In Progress,Resolved']);
        $issue->update($data);

        return response()->json(['success' => true]);
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
}
