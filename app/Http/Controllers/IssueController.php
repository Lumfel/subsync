<?php

namespace App\Http\Controllers;

use App\Models\IssueAuditLog;
use App\Models\IssueReport;
use App\Models\IssueResponse;
use App\Models\Media;
use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IssueController extends Controller
{
    private function residentId(Request $request): ?int
    {
        return Auth::guard('resident')->id() ?? $request->session()->get('resident_id');
    }

    /**
     * Normalize optional date-range filters. Malformed values are ignored and a
     * reversed range (from > to) is swapped so the filter always makes sense.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function dateRange(Request $request): array
    {
        $from = $request->query('date_from');
        $to = $request->query('date_to');

        $valid = fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) === 1;

        if (! $valid($from)) {
            $from = null;
        }
        if (! $valid($to)) {
            $to = null;
        }
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    /** Defense-in-depth: only admins (session) or officers (guard) may mutate issues. */
    private function authorizeStaff(): void
    {
        if (! session('admin_id') && ! Auth::guard('officer')->check()) {
            abort(403, 'Only administrators or officers may manage reports.');
        }
    }

    /** Current acting staff identity for the audit trail. */
    private function actor(): array
    {
        if (session('admin_id')) {
            return ['type' => 'Admin', 'id' => session('admin_id')];
        }
        if (Auth::guard('officer')->check()) {
            return ['type' => 'Officer', 'id' => Auth::guard('officer')->id()];
        }

        return ['type' => 'System', 'id' => null];
    }

    /** Append an audit-trail entry for a report change. */
    private function logAudit(int $issueId, string $action, ?string $old, ?string $new): void
    {
        $actor = $this->actor();
        IssueAuditLog::create([
            'issue_id' => $issueId,
            'actor_type' => $actor['type'],
            'actor_id' => $actor['id'],
            'action' => $action,
            'old_value' => $old,
            'new_value' => $new,
        ]);
    }

    /** GET /api/issues — all issues (admin/officer) */
    public function index(Request $request)
    {
        $query = IssueReport::with('resident:id,name,house_id', 'resident.household:id,block_lot_number', 'media', 'responses', 'assignee:id,name', 'hoaExpenses:id,issue_id,amount')
            ->orderByDesc('created_at');

        // Archived records are hidden from the working list unless explicitly requested.
        if ($request->boolean('archived')) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        [$dateFrom, $dateTo] = $this->dateRange($request);
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $issues = $query->get()->map(fn ($i) => [
            'id' => $i->id,
            'category' => $i->category,
            'title' => $i->title,
            'description' => $i->description,
            'status' => $i->status,
            'priority' => $i->priority,
            'latitude' => $i->latitude,
            'longitude' => $i->longitude,
            'created_at' => $i->created_at?->format('M d, Y'),
            'resolved_at' => $i->resolved_at?->format('Y-m-d'),
            'resident' => $i->resident?->name ?? '—',
            'block_lot' => $i->resident?->household?->block_lot_number ?? '—',
            'assigned_officer_id' => $i->assigned_officer_id,
            'assigned_officer' => $i->assignee?->name,
            'due_date' => $i->due_date?->format('Y-m-d'),
            'overdue' => $i->is_overdue,
            'repair_cost' => (float) $i->hoaExpenses->sum('amount'),
            'media' => $i->media->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->file_name,
                'url' => '/api/issues/media/'.$m->id,
            ]),
            // Latest response first so list previews always show the newest reply.
            'responses' => $i->responses->sortByDesc('id')->values()->map(fn ($r) => [
                'id' => $r->id,
                'content' => $r->response_content,
                'type' => $r->responder_type,
            ]),
        ]);

        return response()->json($issues);
    }

    /** GET /api/issues/my — resident's own issues */
    public function myIssues(Request $request)
    {
        $residentId = $this->residentId($request);

        if (! $residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $issues = IssueReport::where('resident_id', $residentId)
            ->with('responses', 'media', 'assignee:id,name', 'hoaExpenses:id,issue_id,amount')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'category' => $i->category,
                'title' => $i->title,
                'description' => $i->description,
                'status' => $i->status,
                'priority' => $i->priority,
                'created_at' => $i->created_at?->format('M d, Y'),
                'resolved_at' => $i->resolved_at?->format('Y-m-d'),
                'archived_at' => $i->archived_at?->format('Y-m-d'),
                'assigned_officer' => $i->assignee?->name,
                'due_date' => $i->due_date?->format('Y-m-d'),
                'overdue' => $i->is_overdue,
                'repair_cost' => (float) $i->hoaExpenses->sum('amount'),
                'response' => $i->responses->sortByDesc('id')->first()?->response_content,
                'media' => $i->media->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->file_name,
                    'url' => '/api/issues/media/'.$m->id,
                ]),
            ]);

        return response()->json($issues);
    }

    /** POST /api/issues — resident submits */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|max:8192',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $residentId = $this->residentId($request);

        if (! $residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        if ($request->filled('latitude') xor $request->filled('longitude')) {
            return response()->json(['success' => false, 'message' => 'Provide both latitude and longitude for the report location.'], 422);
        }

        if ($request->filled('latitude') && $request->filled('longitude') && ! self::withinArea($data['latitude'], $data['longitude'])) {
            return response()->json(['success' => false, 'message' => 'The report location must be within Terra Nova Subdivision.'], 422);
        }

        $data['resident_id'] = $residentId;
        $data['status'] = 'Pending';
        unset($data['images'], $data['documents']);

        $issue = IssueReport::create($data);

        // Handle uploaded images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('issues', 'local');
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

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('issues/documents', 'local');
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
            'id' => $issue->id,
            'category' => $issue->category,
            'title' => $issue->title,
            'description' => $issue->description,
            'status' => $issue->status,
            'priority' => $issue->priority,
            'created_at' => $issue->created_at->format('M d, Y'),
            'response' => null,
            'media' => $issue->media->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->file_name,
                'url' => '/api/issues/media/'.$m->id,
            ]),
        ]]);
    }

    /**
     * POST /api/issues/{id}/details — the owner resident appends extra details,
     * photos, or documents to an open report. The original text stays untouched
     * (audit trail); additions land in the issue history and the media gallery.
     */
    public function addDetails(Request $request, int $id)
    {
        $residentId = $this->residentId($request);
        if (! $residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        // Scoped lookup: other residents' reports 404, never 403 (no probing).
        $issue = IssueReport::where('id', $id)->where('resident_id', $residentId)->firstOrFail();

        if ($issue->status === 'Resolved' || $issue->archived_at) {
            return response()->json(['success' => false, 'message' => 'This report is already closed. Please file a new report instead.'], 422);
        }

        $request->validate([
            'details' => 'nullable|string|max:2000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|max:8192',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $details = trim((string) $request->input('details', ''));
        $imageFiles = $request->file('images') ?? [];
        $documentFiles = $request->file('documents') ?? [];

        if ($details === '' && ! $imageFiles && ! $documentFiles) {
            return response()->json(['success' => false, 'message' => 'Add a note or attach a photo/document first.'], 422);
        }

        $added = 0;
        foreach ((array) $imageFiles as $file) {
            $path = $file->store('issues', 'local');
            Media::create([
                'uploader_id' => $residentId,
                'uploader_type' => 'Resident',
                'entity_type' => 'IssueReport',
                'entity_id' => $issue->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
            ]);
            $added++;
        }
        foreach ((array) $documentFiles as $file) {
            $path = $file->store('issues/documents', 'local');
            Media::create([
                'uploader_id' => $residentId,
                'uploader_type' => 'Resident',
                'entity_type' => 'IssueReport',
                'entity_id' => $issue->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
            ]);
            $added++;
        }

        IssueAuditLog::create([
            'issue_id' => $issue->id,
            'actor_type' => 'Resident',
            'actor_id' => $residentId,
            'action' => $details !== '' ? 'details' : 'attachments',
            'old_value' => null,
            'new_value' => $details !== '' ? $details : ($added.' file(s) added'),
        ]);

        // Touch so the realtime observer pushes an update to the staff portals.
        $issue->touch();
        $issue->load('media');

        return response()->json(['success' => true, 'media' => $issue->media->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->file_name,
            'url' => '/api/issues/media/'.$m->id,
        ])]);
    }

    /**
     * GET /api/issues/media/{id} — stream an issue photo/document. Staff may view
     * any; residents only media that belongs to their own reports. Files live on
     * the private disk (legacy rows on the public disk still resolve).
     */
    public function media(Request $request, int $id)
    {
        $media = Media::findOrFail($id);
        if ($media->entity_type !== 'IssueReport') {
            abort(404);
        }

        $issue = IssueReport::findOrFail($media->entity_id);

        $isStaff = session('admin_id') || Auth::guard('officer')->check();
        if (! $isStaff && (int) $issue->resident_id !== (int) $this->residentId($request)) {
            abort(403, 'You can only view media on your own reports.');
        }

        $disk = Storage::disk('local')->exists($media->file_path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($media->file_path), 404);

        return response()->file(Storage::disk($disk)->path($media->file_path));
    }

    /** PUT /api/issues/{id}/status — admin updates status */
    public function updateStatus(Request $request, int $id)
    {
        $this->authorizeStaff();
        $issue = IssueReport::findOrFail($id);
        $data = $request->validate(['status' => 'required|in:Pending,In Progress,Resolved']);

        $old = $issue->status;
        // Start the 30-day auto-delete timer when an issue is resolved; clear it otherwise.
        $data['resolved_at'] = $data['status'] === 'Resolved' ? now() : null;

        $issue->update($data);

        if ($old !== $data['status']) {
            $this->logAudit($id, 'status', $old, $data['status']);
        }

        return response()->json(['success' => true]);
    }

    /** PUT /api/issues/{id}/priority — admin/officer sets priority */
    public function setPriority(Request $request, int $id)
    {
        $this->authorizeStaff();
        $issue = IssueReport::findOrFail($id);
        $data = $request->validate(['priority' => 'required|in:Low,Medium,High,Critical']);

        $old = $issue->priority;
        $issue->update($data);

        if ($old !== $data['priority']) {
            $this->logAudit($id, 'priority', $old, $data['priority']);
        }

        return response()->json(['success' => true, 'priority' => $issue->priority]);
    }

    /** PUT /api/issues/{id}/assign — assign an officer and/or an SLA due date (admin/officer) */
    public function assign(Request $request, int $id)
    {
        $this->authorizeStaff();
        $issue = IssueReport::findOrFail($id);

        $data = $request->validate([
            'assigned_officer_id' => 'nullable|exists:officers,id',
            'due_date' => 'nullable|date',
        ]);

        $oldOfficerId = $issue->assigned_officer_id;
        $oldDue = $issue->due_date?->format('Y-m-d');

        $issue->update([
            'assigned_officer_id' => $data['assigned_officer_id'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);

        // Compare as ints: DB values arrive as strings on some drivers, which made
        // an unchanged assignee log a spurious audit entry on every save.
        if ((int) $oldOfficerId !== (int) ($data['assigned_officer_id'] ?? 0)) {
            $this->logAudit($id, 'assign', Officer::find($oldOfficerId)?->name, Officer::find($data['assigned_officer_id'] ?? null)?->name);
        }
        if ($oldDue !== ($data['due_date'] ?? null)) {
            $this->logAudit($id, 'due_date', $oldDue, $data['due_date'] ?? null);
        }

        $issue->load('assignee');

        return response()->json([
            'success' => true,
            'assigned_officer_id' => $issue->assigned_officer_id,
            'assigned_officer' => $issue->assignee?->name,
            'due_date' => $issue->due_date?->format('Y-m-d'),
        ]);
    }

    /** GET /api/issues/my/{id}/timeline — status history for the owning resident */
    public function myTimeline(Request $request, int $id)
    {
        $residentId = $this->residentId($request);
        if (! $residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $issue = IssueReport::with('assignee:id,name')
            ->where('id', $id)->where('resident_id', $residentId)->firstOrFail();

        $timeline = IssueAuditLog::where('issue_id', $id)->orderBy('id')->get()->map(fn ($l) => [
            'action' => $l->action,
            'old' => $l->old_value,
            'new' => $l->new_value,
            'actor' => $l->actor_type,
            'at' => $l->created_at?->format('M d, Y g:i A'),
        ]);

        return response()->json([
            'issue' => [
                'id' => $issue->id,
                'title' => $issue->title,
                'status' => $issue->status,
                'priority' => $issue->priority,
                'assigned_officer' => $issue->assignee?->name,
                'due_date' => $issue->due_date?->format('Y-m-d'),
                'overdue' => $issue->is_overdue,
            ],
            'timeline' => $timeline,
        ]);
    }

    /** PATCH /api/issues/{id}/archive — toggle archived state (admin/officer) */
    public function archive(int $id)
    {
        $this->authorizeStaff();
        $issue = IssueReport::findOrFail($id);
        $old = $issue->archived_at ? 'archived' : 'active';
        $issue->update(['archived_at' => $issue->archived_at ? null : now()]);
        $this->logAudit($id, 'archive', $old, $issue->archived_at ? 'archived' : 'active');

        return response()->json(['success' => true, 'archived' => (bool) $issue->archived_at]);
    }

    /** PATCH /api/issues/bulk-archive — archive many reports at once (admin/officer) */
    public function bulkArchive(Request $request)
    {
        $this->authorizeStaff();
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $issues = IssueReport::whereIn('id', $data['ids'])->get();
        $archived = 0;
        foreach ($issues as $issue) {
            if (! $issue->archived_at) {
                $issue->update(['archived_at' => now()]);
                $this->logAudit($issue->id, 'archive', 'active', 'archived');
                $archived++;
            }
        }

        return response()->json(['success' => true, 'archived' => $archived]);
    }

    /** GET /api/issues/{id}/history — audit trail (admin/officer) */
    public function history(int $id)
    {
        $this->authorizeStaff();
        IssueReport::findOrFail($id);

        $logs = IssueAuditLog::where('issue_id', $id)->orderByDesc('id')->get()->map(fn ($l) => [
            'action' => $l->action,
            'old' => $l->old_value,
            'new' => $l->new_value,
            'actor' => $l->actor_type,
            'at' => $l->created_at?->format('M d, Y g:i A'),
        ]);

        return response()->json($logs);
    }

    /** GET /api/issues/common — most-reported categories, ranked by frequency (admin/officer) */
    public function common(Request $request)
    {
        $this->authorizeStaff();

        $query = IssueReport::query();
        if ($request->boolean('archived')) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }
        [$dateFrom, $dateTo] = $this->dateRange($request);
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $rows = $query
            ->selectRaw("category, COUNT(*) as total, SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved, SUM(CASE WHEN status != 'Resolved' THEN 1 ELSE 0 END) as open")
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return response()->json($rows);
    }

    /** GET /api/issues/urgent-check — lightweight poll for critical/high unresolved issues */
    public function urgentCheck()
    {
        $urgent = IssueReport::whereIn('priority', ['Critical', 'High'])
            ->where('status', '!=', 'Resolved')
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'count' => IssueReport::whereIn('priority', ['Critical', 'High'])->where('status', '!=', 'Resolved')->count(),
            'latest_id' => $urgent?->id,
            'latest' => $urgent ? [
                'id' => $urgent->id,
                'title' => $urgent->title,
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
            $responseData['officer_id'] = Auth::guard('officer')->id();
            $responseData['responder_type'] = 'officer';
        } else {
            $responseData['admin_id'] = session('admin_id');
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
