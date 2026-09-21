<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportTemplate;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Get all report templates (admin/officer only)
     */
    public function index(Request $request)
    {
        // Only admin and officer can access report templates
        if (!Auth::guard('admin')->check() && !Auth::guard('officer')->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $templates = ReportTemplate::all()->map(fn($t) => [
            'id'         => $t->id,
            'name'       => $t->name,
            'type'       => $t->type,
            'subject_template' => $t->subject_template,
            'body_template'  => $t->body_template,
            'variables'  => json_decode($t->variables, true),
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
        if (!Auth::guard('admin')->check() && !Auth::guard('officer')->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'type'             => 'required|string|max:50',
            'subject_template' => 'required|string',
            'body_template'    => 'required|string',
            'variables'        => 'sometimes|array',
        ]);

        $data['variables'] = $data['variables'] ? json_encode($data['variables']) : null;

        $template = ReportTemplate::create($data);

        return response()->json(['success' => true, 'template' => [
            'id'         => $template->id,
            'name'       => $template->name,
            'type'       => $template->type,
            'subject_template' => $template->subject_template,
            'body_template'  => $template->body_template,
            'variables'  => json_decode($template->variables, true),
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
        if (!Auth::guard('admin')->check() && !Auth::guard('officer')->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $template = ReportTemplate::findOrFail($id);

        $data = $request->validate([
            'name'             => 'sometimes|required|string|max:100',
            'type'             => 'sometimes|required|string|max:50',
            'subject_template' => 'sometimes|required|string',
            'body_template'    => 'sometimes|required|string',
            'variables'        => 'sometimes|array',
        ]);

        if (isset($data['variables'])) {
            $data['variables'] = json_encode($data['variables']);
        }

        $template->update($data);

        return response()->json(['success' => true, 'template' => [
            'id'         => $template->id,
            'name'       => $template->name,
            'type'       => $template->type,
            'subject_template' => $template->subject_template,
            'body_template'  => $template->body_template,
            'variables'  => json_decode($template->variables, true),
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
        if (!Auth::guard('admin')->check() && !Auth::guard('officer')->check()) {
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
        if (!$resident) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $template = ReportTemplate::findOrFail($templateId);

        // Validate required variables are provided
        $variables = json_decode($template->variables, true) ?? [];
        $providedVariables = $request->input('variables', []);

        // Check if all required variables are provided
        foreach ($variables as $var) {
            if (!isset($providedVariables[$var])) {
                return response()->json([
                    'error' => "Missing required variable: {$var}"
                ], 400);
            }
        }

        // Render the template with provided variables
        $rendered = $template->render($providedVariables);

        // Store the generated report
        $report = Report::create([
            'resident_id'   => $resident->id,
            'template_id'   => $template->id,
            'subject'       => $rendered['subject'],
            'content'       => $rendered['body'],
            'status'        => 'Generated',
        ]);

        return response()->json(['success' => true, 'report' => [
            'id'         => $report->id,
            'subject'    => $report->subject,
            'content'    => $report->content,
            'status'     => $report->status,
            'created_at' => $report->created_at?->format('M d, Y'),
        ]]);
    }

    /**
     * Get user's generated reports
     */
    public function myReports(Request $request)
    {
        $resident = Auth::guard('resident')->user();
        if (!$resident) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $reports = Report::where('resident_id', $resident->id)
            ->with('template:id,name,type')
            ->get()
            ->map(fn($r) => [
                'id'         => $r->id,
                'template_id'=> $r->template_id,
                'template_name' => $r->template?->name,
                'template_type' => $r->template?->type,
                'subject'    => $r->subject,
                'content'    => $r->content,
                'status'     => $r->status,
                'created_at' => $r->created_at?->format('M d, Y'),
            ]);

        return response()->json($reports);
    }
}
