<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementTemplate;
use Illuminate\Http\Request;

class AnnouncementTemplateController extends Controller
{
    /** GET /api/announcement-templates — list templates (admin/officer). */
    public function index()
    {
        return response()->json(
            AnnouncementTemplate::orderBy('name')->get([
                'id', 'name', 'type', 'subject_template', 'body_template', 'variables',
            ])
        );
    }

    /** POST /api/announcement-templates — create (admin). */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $template = AnnouncementTemplate::create($data);

        return response()->json(['success' => true, 'template' => $template]);
    }

    /** PUT /api/announcement-templates/{id} — update (admin). */
    public function update(Request $request, int $id)
    {
        $template = AnnouncementTemplate::findOrFail($id);
        $template->update($this->validateData($request));

        return response()->json(['success' => true, 'template' => $template->fresh()]);
    }

    /** DELETE /api/announcement-templates/{id} — delete (admin). */
    public function destroy(int $id)
    {
        AnnouncementTemplate::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'subject_template' => 'required|string|max:200',
            'body_template' => 'required|string',
            'variables' => 'nullable|string',
        ]);
    }
}
