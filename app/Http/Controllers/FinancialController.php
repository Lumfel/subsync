<?php

namespace App\Http\Controllers;

use App\Models\FinancialRecord;
use App\Models\FinancialReport;
use App\Models\Resident;
use App\Models\Delinquent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialController extends Controller
{
    private function residentId(Request $request): ?int
    {
        return Auth::guard('resident')->id() ?? $request->session()->get('resident_id');
    }

    /** GET /api/financial — all records (admin) */
    public function index()
    {
        $records = FinancialRecord::with('resident:id,name,house_id', 'resident.household:id,block_lot_number')
            ->orderByDesc('record_date')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'resident_id' => $r->resident_id,
                'resident'    => $r->resident?->name ?? '—',
                'block_lot'   => $r->resident?->household?->block_lot_number ?? '—',
                'record_type' => $r->record_type,
                'description' => $r->description,
                'amount'      => $r->amount,
                'record_date' => $r->record_date,
            ]);

        return response()->json($records);
    }

    /** GET /api/financial/my — resident's own records */
    public function myRecords(Request $request)
    {
        $residentId = $this->residentId($request);

        if (!$residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $records = FinancialRecord::where('resident_id', $residentId)
            ->orderByDesc('record_date')
            ->get(['id', 'record_type', 'description', 'amount', 'record_date']);

        return response()->json($records);
    }

    /** POST /api/financial — admin adds a record */
    public function store(Request $request)
    {
        $data = $request->validate([
            'resident_id'  => 'required|exists:residents,id',
            'record_type'  => 'required|in:Due,Payment,Penalty,Adjustment',
            'description'  => 'nullable|string|max:255',
            'amount'       => 'required|numeric',
            'record_date'  => 'required|date',
        ]);

        $data['admin_id'] = session('admin_id');

        $record = FinancialRecord::create($data);

        // Update resident balance: payments decrease, dues/penalties increase
        $resident = Resident::findOrFail($data['resident_id']);
        if ($data['record_type'] === 'Payment') {
            $resident->decrement('current_balance', abs($data['amount']));
        } else {
            $resident->increment('current_balance', abs($data['amount']));
        }

        return response()->json(['success' => true, 'record' => $record]);
    }

    /** GET /api/financial/summary — totals for dashboard */
    public function summary()
    {
        $totalDues     = FinancialRecord::whereIn('record_type', ['Due', 'Penalty'])->sum('amount');
        $totalPayments = FinancialRecord::where('record_type', 'Payment')->sum('amount');
        $totalBalance  = Resident::sum('current_balance');
        $delinquentCount = Delinquent::count();

        return response()->json([
            'total_collected' => $totalPayments,
            'total_dues'      => $totalDues,
            'total_balance'   => $totalBalance,
            'delinquent_count'=> $delinquentCount,
        ]);
    }

    /** GET /api/financial/payments — payment status per household */
    public function payments()
    {
        $residents = Resident::with('household:id,block_lot_number')
            ->select('id', 'name', 'house_id', 'current_balance', 'status')
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'name'            => $r->name,
                'block_lot'       => $r->household?->block_lot_number ?? '—',
                'current_balance' => $r->current_balance,
                'pay_status'      => $r->current_balance <= 0 ? 'Paid'
                                   : ($r->current_balance > 0 && $r->current_balance < 1000 ? 'Partial' : 'Unpaid'),
            ]);

        return response()->json($residents);
    }

    /** DELETE /api/financial/{id} */
    public function destroy($id)
    {
        $record = FinancialRecord::findOrFail($id);
        $resident = Resident::where('id', $record->resident_id)->first();
        if ($resident) {
            // Reverse the balance effect
            if ($record->record_type === 'Payment') {
                $resident->increment('current_balance', abs($record->amount));
            } else {
                $resident->decrement('current_balance', abs($record->amount));
            }
        }
        $record->delete();
        return response()->json(['success' => true]);
    }

    /** GET /api/financial-reports */
    public function reportIndex()
    {
        $reports = FinancialReport::orderByDesc('month')->get();
        return response()->json($reports);
    }

    /** POST /api/financial-reports */
    public function reportStore(Request $request)
    {
        $data = $request->validate([
            'month'            => 'required|string|max:7',
            'previous_balance' => 'required|numeric',
            'collections'      => 'required|array',
            'expenses'         => 'required|array',
        ]);
        $data['admin_id'] = session('admin_id');
        $report = FinancialReport::create($data);
        return response()->json(['success' => true, 'report' => $report]);
    }

    /** DELETE /api/financial-reports/{id} */
    public function reportDestroy($id)
    {
        FinancialReport::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
