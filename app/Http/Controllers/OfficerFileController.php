<?php

namespace App\Http\Controllers;

use App\Models\OfficerFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OfficerFileController extends Controller
{
    /** GET /api/officer-files — all files (admin view) */
    public function index()
    {
        $files = OfficerFile::orderByDesc('created_at')->get();
        return response()->json($files);
    }

    /** POST /api/officer-files — officer uploads a file */
    public function store(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|mimes:xlsx,xls,csv,pdf,doc,docx|max:10240',
            'category' => 'required|string|max:100',
            'period'   => 'nullable|string|max:100',
            'notes'    => 'nullable|string|max:500',
        ]);

        $officer   = Auth::guard('officer')->user();
        $uploaded  = $request->file('file');
        $path      = $uploaded->store('officer-files', 'public');

        $record = OfficerFile::create([
            'officer_id'    => $officer?->id,
            'officer_name'  => $officer?->name ?? 'Officer',
            'original_name' => $uploaded->getClientOriginalName(),
            'category'      => $request->input('category'),
            'period'        => $request->input('period'),
            'notes'         => $request->input('notes'),
            'size_bytes'    => $uploaded->getSize(),
            'file_path'     => $path,
        ]);

        return response()->json(['success' => true, 'file' => $record]);
    }

    /** DELETE /api/officer-files/{id} */
    public function destroy($id)
    {
        $file = OfficerFile::findOrFail($id);
        Storage::disk('public')->delete($file->file_path);
        $file->delete();
        return response()->json(['success' => true]);
    }
}
