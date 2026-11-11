<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AssessmentController extends Controller
{
    /** GET /api/assessments — the dues schedule. */
    public function index()
    {
        return response()->json(
            Assessment::orderByDesc('is_active')->orderBy('name')->get()
        );
    }

    /** POST /api/assessments — create a recurring assessment. */
    public function store(Request $request)
    {
        $data = $this->validateAssessment($request);
        $data['created_by'] = session('admin_id');

        return response()->json(['success' => true, 'assessment' => Assessment::create($data)]);
    }

    /** PUT /api/assessments/{id} */
    public function update(Request $request, int $id)
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->update($this->validateAssessment($request));

        return response()->json(['success' => true, 'assessment' => $assessment->fresh()]);
    }

    /** DELETE /api/assessments/{id} */
    public function destroy(int $id)
    {
        Assessment::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    /** POST /api/assessments/generate — post this period's charges now. */
    public function generate(Request $request)
    {
        // A malformed period used to reach the command and blow up on explode('-')
        // (500); require YYYY-MM up front.
        // Array syntax: the pattern contains "|", which would otherwise be split
        // into separate rules by the string form.
        $data = $request->validate([
            'period' => ['nullable', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        $period = $data['period'] ?? null;
        Artisan::call('assessments:generate-dues', $period ? ['--period' => $period] : []);

        return response()->json([
            'success' => true,
            'message' => trim(Artisan::output()) ?: 'Dues generated.',
        ]);
    }

    private function validateAssessment(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0.01|max:1000000',
            'frequency' => 'required|in:monthly,quarterly,annual,one_time',
            'due_day' => 'nullable|integer|between:1,28',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target' => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);
    }
}
