<?php

namespace App\Http\Controllers;

use App\Models\OfficerFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OfficerFileController extends Controller
{
    /** GET /api/officer-files — all files (admin view) / own files (officer view) */
    public function index()
    {
        $query = OfficerFile::where('archived', request()->boolean('archived'))
            ->orderByDesc('created_at');

        // Officers only see the files they sent; admins see everything.
        $officer = Auth::guard('officer')->user();
        if ($officer && ! Auth::guard('admin')->check() && ! session('admin_id')) {
            $query->where('officer_id', $officer->id);
        }

        return response()->json($query->get());
    }

    /** POST /api/officer-files — officer uploads a file */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,pdf,doc,docx,png,jpg,jpeg,gif,webp|max:10240',
            'category' => 'required|string|max:100',
            'period' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $officer = Auth::guard('officer')->user();
        $uploaded = $request->file('file');
        $path = $uploaded->store('officer-files', 'local');

        $record = OfficerFile::create([
            'officer_id' => $officer?->id,
            'officer_name' => $officer?->name ?? session('admin_name', 'Admin'),
            'original_name' => $uploaded->getClientOriginalName(),
            'category' => $request->input('category'),
            'period' => $request->input('period'),
            'notes' => $request->input('notes'),
            'size_bytes' => $uploaded->getSize(),
            'file_path' => $path,
        ]);

        return response()->json(['success' => true, 'file' => $record]);
    }

    /** Officers may only access files they sent; admins may access any. */
    private function assertCanAccess(OfficerFile $file): void
    {
        $officer = Auth::guard('officer')->user();
        if ($officer && ! Auth::guard('admin')->check() && ! session('admin_id')
            && (int) $file->officer_id !== (int) $officer->id) {
            abort(403, 'You can only access the files you sent.');
        }
    }

    /** Remove the stored file from whichever disk holds it (private or legacy public). */
    private function deleteStoredFile(string $path): void
    {
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    /** GET /api/officer-files/{id}/download — stream the file to its sender or an admin. */
    public function download($id)
    {
        $file = OfficerFile::findOrFail($id);
        $this->assertCanAccess($file);

        $disk = Storage::disk('local')->exists($file->file_path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($file->file_path), 404);

        return response()->download(Storage::disk($disk)->path($file->file_path), $file->original_name);
    }

    /** DELETE /api/officer-files/{id} */
    public function destroy($id)
    {
        $file = OfficerFile::findOrFail($id);
        $this->assertCanAccess($file);
        $this->deleteStoredFile($file->file_path);
        $file->delete();

        return response()->json(['success' => true]);
    }

    public function archive($id)
    {
        $file = OfficerFile::findOrFail($id);
        $this->assertCanAccess($file);
        $file->update(['archived' => ! $file->archived]);

        return response()->json(['success' => true, 'archived' => $file->archived]);
    }
}
