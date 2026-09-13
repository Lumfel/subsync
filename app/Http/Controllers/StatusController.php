<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\PaymentStatus;
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

        if ($request->status === 'Non-Compliant') {
            // Apply subdivision policy validation before marking as delinquent
            if (!$this->validateSubdivisionPolicy($request->house_id, $request->reason)) {
                return redirect('/manage_users')
                    ->with('error', 'Cannot mark household as delinquent: Subdivision policy requirements not met.');
            }

            PaymentStatus::firstOrCreate(
                ['house_id' => $request->house_id],
                [
                    'reason' => $request->reason ?? 'Status marked delinquent',
                    'date_flagged' => now(),
                ]
            );
        }

        return redirect('/manage_users');
    }

    /**
     * Validate subdivision policy before marking household as delinquent
     */
    private function validateSubdivisionPolicy(int $houseId, string $reason): bool
    {
        $household = \App\Models\Household::find($houseId);

        if (!$household) {
            return false;
        }

        // Get the resident associated with this household
        $resident = \App\Models\Resident::where('house_id', $houseId)->first();

        if (!$resident) {
            return false;
        }

        // Subdivision policy rules:
        // 1. Household must have a minimum balance threshold (e.g., $1000) to be considered for delinquency
        // 2. Must be delinquent for at least 60 days (based on record_date of oldest unpaid dues)
        // 3. Must have received at least 2 payment reminders

        $minBalanceThreshold = 1000.00; // Minimum balance to be considered for delinquency
        $minDaysDelinquent = 60; // Minimum days overdue
        $minPaymentReminders = 2; // Minimum payment reminders sent

        // Check balance threshold
        if ($resident->current_balance < $minBalanceThreshold) {
            return false;
        }

        // Check days delinquent - find oldest unpaid Due/Penalty record
        $oldestUnpaid = \App\Models\FinancialRecord::where('resident_id', $resident->id)
            ->whereIn('record_type', ['Due', 'Penalty'])
            ->orderBy('record_date', 'asc')
            ->first();

        if ($oldestUnpaid) {
            $daysDelinquent = $oldestUnpaid->record_date->diffInDays(\Illuminate\Support\Carbon::now());
            if ($daysDelinquent < $minDaysDelinquent) {
                return false;
            }
        }

        // Check payment reminders (simplified - count of FinancialRecord with type 'Adjustment' for reminders)
        $paymentRemindersCount = \App\Models\FinancialRecord::where('resident_id', $resident->id)
            ->where('record_type', 'Adjustment')
            ->where('description', 'like', '%reminder%')
            ->count();

        if ($paymentRemindersCount < $minPaymentReminders) {
            return false;
        }

        return true;
    }

    public function update(Request $request, $id)
    {
        $status = Status::findOrFail($id);

        $status->update([
            'status' => $request->status,
            'reason' => $request->reason
        ]);

        if ($request->status === 'Non-Compliant') {
            // Apply subdivision policy validation before marking as delinquent
            if (!$this->validateSubdivisionPolicy($status->house_id, $request->reason)) {
                return redirect('/manage_users')
                    ->with('error', 'Cannot mark household as delinquent: Subdivision policy requirements not met.');
            }

            PaymentStatus::firstOrCreate(
                ['house_id' => $status->house_id],
                [
                    'reason' => $request->reason ?? 'Status marked delinquent',
                    'date_flagged' => now(),
                ]
            );
        }

        if ($request->status === 'Active') {
            PaymentStatus::where('house_id', $status->house_id)->delete();
        }

        return redirect('/manage_users');
    }

    public function destroy($id)
    {
        Status::findOrFail($id)->delete();
        return redirect('/manage_users');
    }
}