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
        $request->validate([
            'family_name' => 'required|string|max:255'
        ]);

        Family::create([
            'family_name' => $request->family_name
        ]);

        return redirect('/manage_users');
    }

    public function update(Request $request, $id)
    {
        $family = Family::findOrFail($id);

        $family->update([
            'family_name' => $request->family_name,
            'family_head' => $request->family_head,
            'members' => $request->members
        ]);

        return redirect('/manage_users');
    }

    public function destroy($id)
    {
        Family::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}