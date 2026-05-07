<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'house_id' => 'required|exists:households,id',
            'status' => 'required|string',
            'reason' => 'nullable|string'
        ]);

        Status::create($request->all());

        return redirect('/manage_users');
    }

    public function update(Request $request, $id)
    {
        $status = Status::findOrFail($id);

        $status->update([
            'status' => $request->status,
            'reason' => $request->reason
        ]);

        return redirect('/manage_users');
    }

    public function destroy($id)
    {
        Status::findOrFail($id)->delete();
        return redirect('/manage_users');
    }
}