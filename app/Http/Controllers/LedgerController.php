<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Support\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LedgerController extends Controller
{
    private function residentId(Request $request): ?int
    {
        return Auth::guard('resident')->id() ?? $request->session()->get('resident_id');
    }

    /** GET /api/ledger/my — the resident's own statement + aging. */
    public function my(Request $request)
    {
        $id = $this->residentId($request);
        if (! $id) {
            return response()->json(['message' => 'Session expired. Please log in again.'], 401);
        }

        $statement = Ledger::statement($id);
        $statement['aging'] = Ledger::agingFor($id);

        return response()->json($statement);
    }

    /** GET /api/ledger/{residentId} — any resident's statement (staff). */
    public function show(int $residentId)
    {
        Resident::findOrFail($residentId);

        $statement = Ledger::statement($residentId);
        $statement['aging'] = Ledger::agingFor($residentId);

        return response()->json($statement);
    }

    /** GET /api/ledger/aging — outstanding balances + aging buckets (staff). */
    public function aging()
    {
        $residents = Resident::with('household:id,block_lot_number')
            ->orderByDesc('current_balance')
            ->get(['id', 'name', 'house_id', 'current_balance']);

        $rows = $residents->map(function (Resident $r) {
            $aging = Ledger::agingFor($r->id);

            return [
                'id' => $r->id,
                'name' => $r->name,
                'block_lot' => $r->household?->block_lot_number ?? '—',
                'balance' => (float) $r->current_balance,
                'buckets' => $aging['buckets'],
                'credit' => $aging['credit'],
            ];
        });

        return response()->json($rows);
    }
}
