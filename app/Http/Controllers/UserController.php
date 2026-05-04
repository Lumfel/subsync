<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);

        return redirect('/manage_users');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect('/manage_users');
    }
}