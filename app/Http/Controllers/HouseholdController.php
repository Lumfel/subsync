<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Household;

class HouseholdController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'location' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('households', 'public');
        }

        Household::create([
            'location' => $request->location,
            'image' => $imagePath,
        ]);

        return redirect('/manage_users')->with('success', 'Household added successfully!');
    }

    public function destroy($id)
    {
        Household::findOrFail($id)->delete();

        return redirect('/manage_users')->with('success', 'Household deleted.');
    }

    public function edit($id)
{
    $household = Household::findOrFail($id);
    return response()->json($household);
}

public function update(Request $request, $id)
{
    $request->validate([
        'location' => 'required|string|max:255',
    ]);

    $household = Household::findOrFail($id);

    $household->update([
        'location' => $request->location,
    ]);

    return redirect('/manage_users')->with('success', 'Household updated.');
}
}

