<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Family;

class FamilyController extends Controller
{
    /** GET /api/families */
    public function index()
    {
        try {
            $families = Family::select('id', 'family_name', 'family_head', 'members')
                ->orderBy('family_name')
                ->get();
            return response()->json($families);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'family_name' => 'required|string|max:255',
            'family_head' => 'nullable|string|max:255',
            'members'     => 'nullable|string|max:50',
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
            'members'     => 'nullable|string|max:50',
        ]);

        $family->update($data);
        return response()->json(['success' => true, 'family' => $family->fresh()]);
    }

    public function destroy($id)
    {
        Family::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}