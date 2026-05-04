<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;

class MemberController extends Controller
{
    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $member->update([
            'user_id' => $request->user_id,
            'house_id' => $request->house_id,
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