<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::with(['user', 'household'])->get();
        return view('layouts.members', compact('members'));
    }

public function store(Request $request)
{
    $request->validate([
        'house_id' => 'required|integer',
        'users' => 'required|array',
        'member_type' => 'required|string',
        'family_id' => 'nullable|integer'
    ]);

    foreach ($request->users as $userId) {
        Member::firstOrCreate(
            [
                'user_id' => $userId,
                'house_id' => $request->house_id
            ],
            [
                'family_id' => $request->family_id,
                'member_type' => $request->member_type,
                'date_added' => now()
            ]
        );
    }

    return response()->json([
        'success' => true
    ]);
}
    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

      $member->update([
        'user_id' => $request->user_id,
        'house_id' => $request->house_id,
        'family_id' => $request->family_id,
        'member_type' => $request->member_type
        ]);

        return redirect('/manage_users');
    }

    public function destroy($id)
    {
        Member::findOrFail($id)->delete();
        return redirect('/manage_users');
    }
}