<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Household;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    /** GET /api/families */
    public function index()
    {
        $families = Family::select('id', 'family_name', 'family_head', 'members')
            ->orderBy('family_name')
            ->get();

        return response()->json($families);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'family_name' => 'required|string|max:255',
            'family_head' => 'nullable|string|max:255',
            'members' => 'nullable|string|max:50',
        ]);

        $family = Family::create($data);

        return response()->json(['success' => true, 'family' => $family]);
    }

    public function update(Request $request, $id)
    {
        $family = Family::findOrFail($id);

        $data = $request->validate([
            'family_name' => 'sometimes|required|string|max:255',
            'family_head' => 'nullable|string|max:255',
            'members' => 'nullable|string|max:50',
        ]);

        $family->update($data);

        return response()->json(['success' => true, 'family' => $family->fresh()]);
    }

    public function destroy($id)
    {
        $family = Family::findOrFail($id);

        // Detach households first so no row keeps a dangling family_id.
        Household::where('family_id', $family->id)->update(['family_id' => null]);
        $family->delete();

        return response()->json(['success' => true]);
    }
}
