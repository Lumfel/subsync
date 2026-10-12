<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /** Admin (session) or officer (guard) staff check. */
    private function isStaff(): bool
    {
        return (bool) session('admin_id') || Auth::guard('officer')->check();
    }

    /** Name of the acting staff member (or null for resident self-service). */
    private function staffName(): ?string
    {
        if (session('admin_id')) {
            return session('admin_name', 'Admin');
        }
        if (Auth::guard('officer')->check()) {
            return Auth::guard('officer')->user()->name;
        }

        return null;
    }

    /** Sequential, human-readable reference like CLR-2026-00042. */
    private function referenceNumber(?string $type): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $type) ?: 'RPT');
        $prefix = substr($prefix, 0, 4) ?: 'RPT';
        $count = Report::whereYear('created_at', now()->year)->count() + 1;

        return sprintf('%s-%d-%05d', $prefix, now()->year, $count);
    }

    /**
     * Get all report templates (all roles — residents need them to generate reports).
     */
    public function index(Request $request)
    {
        $templates = ReportTemplate::all()->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'type' => $t->type,
            'subject_template' => $t->subject_template,
            'body_template' => $t->body_template,
            'variables' => json_decode($t->variables, true),
            'created_at' => $t->created_at?->format('M d, Y'),
            'updated_at' => $t->updated_at?->format('M d, Y'),
        ]);

        return response()->json($templates);
    }

    /**
     * Store a new report template (admin/officer only)
     */
    public function store(Request $request)
    {
        // Only admin and officer can create report templates
        if (! $this->isStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'subject_template' => 'required|string',
            'body_template' => 'required|string',
            'variables' => 'sometimes|array',
        ]);

        $data['variables'] = ! empty($data['variables']) ? json_encode($data['variables']) : null;

        $template = ReportTemplate::create($data);

        return response()->json(['success' => true, 'template' => [
            'id' => $template->id,
            'name' => $template->name,
            'type' => $template->type,
            'subject_template' => $template->subject_template,
            'body_template' => $template->body_template,
            'variables' => json_decode($template->variables, true),
            'created_at' => $template->created_at?->format('M d, Y'),
            'updated_at' => $template->updated_at?->format('M d, Y'),
        ]]);
    }

    /**
     * Update an existing report template (admin/officer only)
     */
    public function update(Request $request, $id)
    {
        // Only admin and officer can update report templates
        if (! $this->isStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $template = ReportTemplate::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|string|max:50',
            'subject_template' => 'sometimes|required|string',
            'body_template' => 'sometimes|required|string',
            'variables' => 'sometimes|array',
        ]);

        if (isset($data['variables'])) {
            $data['variables'] = json_encode($data['variables']);
        }

        $template->update($data);

        return response()->json(['success' => true, 'template' => [
            'id' => $template->id,
            'name' => $template->name,
            'type' => $template->type,
            'subject_template' => $template->subject_template,
            'body_template' => $template->body_template,
            'variables' => json_decode($template->variables, true),
            'created_at' => $template->created_at?->format('M d, Y'),
            'updated_at' => $template->updated_at?->format('M d, Y'),
        ]]);
    }

    /**
     * Delete a report template (admin/officer only)
     */
    public function destroy($id)
    {
        // Only admin and officer can delete report templates
        if (! $this->isStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        ReportTemplate::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Generate a report from a template (resident can request reports based on templates)
     */
    public function generate(Request $request, $templateId)
    {
        // Residents can generate reports from templates
        $resident = Auth::guard('resident')->user();
        if (! $resident) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $template = ReportTemplate::findOrFail($templateId);

        // Validate required variables are provided
        $variables = json_decode($template->variables, true) ?? [];
        $providedVariables = (array) $request->input('variables', []);

        // Check if all required variables are provided
        foreach ($variables as $var) {
            if (! isset($providedVariables[$var])) {
                return response()->json([
                    'error' => "Missing required variable: {$var}",
                ], 400);
            }
        }

        // Render the template with provided variables
        $rendered = $template->render($providedVariables);

        // Store the generated report
        $report = Report::create([
            'reference_no' => $this->referenceNumber($template->type),
            'resident_id' => $resident->id,
            'template_id' => $template->id,
            'subject' => $rendered['subject'],
            'content' => $rendered['body'],
            'status' => 'Generated',
        ]);

        return response()->json(['success' => true, 'report' => [
            'id' => $report->id,
            'reference_no' => $report->reference_no,
            'subject' => $report->subject,
            'content' => $report->content,
            'status' => $report->status,
            'created_at' => $report->created_at?->format('M d, Y'),
        ]]);
    }

    /**
     * Get user's generated reports
     */
    public function myReports(Request $request)
    {
        $resident = Auth::guard('resident')->user();
        if (! $resident) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $reports = Report::where('resident_id', $resident->id)
            ->with('template:id,name,type')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'reference_no' => $r->reference_no,
                'template_id' => $r->template_id,
                'template_name' => $r->template?->name,
                'template_type' => $r->template?->type,
                'subject' => $r->subject,
                'content' => $r->content,
                'status' => $r->status,
                'issued_by' => $r->issued_by,
                'created_at' => $r->created_at?->format('M d, Y'),
            ]);

        return response()->json($reports);
    }

    /**
     * POST /api/reports/issue — staff issues a certificate/report for a resident.
     */
    public function issue(Request $request)
    {
        if (! $this->isStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'template_id' => 'required|integer|exists:report_templates,id',
            'resident_id' => 'required|integer|exists:residents,id',
            'variables' => 'nullable|array',
        ]);

        $template = ReportTemplate::findOrFail($data['template_id']);
        $provided = (array) ($data['variables'] ?? []);

        foreach (json_decode($template->variables, true) ?? [] as $var) {
            if (! isset($provided[$var]) || $provided[$var] === '') {
                return response()->json(['error' => "Missing required variable: {$var}"], 400);
            }
        }

        $rendered = $template->render($provided);

        $report = Report::create([
            'reference_no' => $this->referenceNumber($template->type),
            'resident_id' => $data['resident_id'],
            'template_id' => $template->id,
            'subject' => $rendered['subject'],
            'content' => $rendered['body'],
            'status' => 'Issued',
            'issued_by' => $this->staffName(),
        ]);

        return response()->json(['success' => true, 'report' => [
            'id' => $report->id,
            'reference_no' => $report->reference_no,
            'subject' => $report->subject,
            'content' => $report->content,
            'status' => $report->status,
            'issued_by' => $report->issued_by,
            'created_at' => $report->created_at?->format('M d, Y'),
        ]]);
    }

    /**
     * GET /api/reports — staff list of issued/generated documents.
     */
    public function staffIndex()
    {
        if (! $this->isStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(
            Report::with(['resident:id,name', 'template:id,name,type'])
                ->orderByDesc('id')
                ->limit(200)
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'reference_no' => $r->reference_no,
                    'subject' => $r->subject,
                    'resident' => $r->resident?->name ?? '-',
                    'resident_id' => $r->resident_id,
                    'template' => $r->template?->name ?? '-',
                    'status' => $r->status,
                    'issued_by' => $r->issued_by,
                    'created_at' => $r->created_at?->format('M d, Y'),
                ])
        );
    }

    /**
     * GET /reports/{id}/print — printable document (owner resident or staff).
     */
    public function printView(int $id)
    {
        $report = Report::with(['resident:id,name,house_id', 'template:id,name,type'])->findOrFail($id);

        $isOwner = Auth::guard('resident')->id() === $report->resident_id;
        if (! $this->isStaff() && ! $isOwner) {
            abort(403);
        }

        return view('reports.print', [
            'report' => $report,
            'resident' => $report->resident,
            'template' => $report->template,
        ]);
    }
}
