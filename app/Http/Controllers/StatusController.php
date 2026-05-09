<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Delinquent;
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

        $status = Status::create([
            'house_id' => $request->house_id,
            'status' => $request->status,
            'reason' => $request->reason
        ]);

        if ($request->status === 'Delinquent') {
            Delinquent::firstOrCreate(
                ['house_id' => $request->house_id],
                [
                    'reason' => $request->reason ?? 'Status marked delinquent',
                    'date_flagged' => now(),
                ]
            );
        }

        return redirect('/manage_users');
    }

    public function update(Request $request, $id)
    {
        $status = Status::findOrFail($id);

        $status->update([
            'status' => $request->status,
            'reason' => $request->reason
        ]);

        if ($request->status === 'Delinquent') {
            Delinquent::firstOrCreate(
                ['house_id' => $status->house_id],
                [
                    'reason' => $request->reason ?? 'Status marked delinquent',
                    'date_flagged' => now(),
                ]
            );
        }

        if ($request->status === 'Active') {
            Delinquent::where('house_id', $status->house_id)->delete();
        }

        return redirect('/manage_users');
    }

    public function destroy($id)
    {
        Status::findOrFail($id)->delete();
        return redirect('/manage_users');
    }
}