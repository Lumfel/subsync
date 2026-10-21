<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceiptMail;
use App\Models\Budget;
use App\Models\FinancialRecord;
use App\Models\HoaExpense;
use App\Models\PaymentSubmission;
use App\Support\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    private function residentId(Request $request): ?int
    {
        return Auth::guard('resident')->id() ?? $request->session()->get('resident_id');
    }

    private function authorizeStaff(): void
    {
        if (! session('admin_id') && ! Auth::guard('officer')->check()) {
            abort(403, 'Only administrators or officers may manage payments.');
        }
    }

    /** POST /api/payments — resident submits a GCash payment with a receipt screenshot. */
    public function store(Request $request)
    {
        $residentId = $this->residentId($request);
        if (! $residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000',
            'reference_no' => 'nullable|string|max:100',
            'gcash_sender_name' => 'nullable|string|max:150',
            'gcash_number' => 'nullable|string|max:40',
            'paid_at' => 'nullable|date',
            'receipt' => 'required|image|max:8192',
        ]);

        $path = $request->file('receipt')->store('payment-receipts', 'local');

        $payment = PaymentSubmission::create([
            'resident_id' => $residentId,
            'amount' => $data['amount'],
            'method' => 'gcash',
            'reference_no' => $data['reference_no'] ?? null,
            'gcash_sender_name' => $data['gcash_sender_name'] ?? null,
            'gcash_number' => $data['gcash_number'] ?? null,
            'paid_at' => $data['paid_at'] ?? null,
            'receipt_path' => $path,
            'status' => 'Pending',
        ]);

        return response()->json(['success' => true, 'payment' => $this->map($payment)]);
    }

    /** GET /api/payments/my — the resident's own submissions. */
    public function my(Request $request)
    {
        $residentId = $this->residentId($request);
        if (! $residentId) {
            return response()->json([]);
        }

        return PaymentSubmission::where('resident_id', $residentId)
            ->orderByDesc('created_at')->get()->map(fn ($p) => $this->map($p));
    }

    /** GET /api/payments — validation queue (admin/officer). */
    public function index(Request $request)
    {
        $this->authorizeStaff();

        $query = PaymentSubmission::with('resident:id,name');
        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        return $query->orderByDesc('created_at')->get()->map(fn ($p) => $this->map($p));
    }

    /** POST /api/payments/{id}/validate — admin approves/rejects a submission. */
    public function validatePayment(Request $request, int $id)
    {
        if (! session('admin_id')) {
            abort(403, 'Only administrators may validate payments.');
        }

        $payment = PaymentSubmission::findOrFail($id);
        $payment->load('resident');

        $data = $request->validate([
            'action' => 'required|in:approve,reject,needs_info',
            'admin_response' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:500',
            'official_receipt' => 'nullable|image|max:8192',
        ]);

        if ($data['action'] === 'approve') {
            if ($request->hasFile('official_receipt')) {
                if ($payment->official_receipt_path) {
                    Storage::disk('local')->delete($payment->official_receipt_path);
                }
                $payment->official_receipt_path = $request->file('official_receipt')->store('payment-receipts/official', 'local');
                $payment->acknowledged_at = now();
            }

            // Record the validated payment in the ledger once, then reconcile the balance.
            if (! $payment->financial_record_id) {
                $record = FinancialRecord::create([
                    'resident_id' => $payment->resident_id,
                    'record_type' => 'Payment',
                    'description' => 'GCash payment'.($payment->reference_no ? ' ('.$payment->reference_no.')' : ''),
                    'amount' => $payment->amount,
                    'record_date' => now()->toDateString(),
                    'period' => now()->format('Y-m'),
                    'source' => 'payment',
                    'payment_gateway' => 'gcash',
                    'gcash_reference' => $payment->reference_no,
                ]);
                $payment->financial_record_id = $record->id;

                // Derive the balance from the ledger (overpayments become a credit).
                Ledger::reconcile((int) $payment->resident_id);
            }

            $payment->status = 'Validated';
        } elseif ($data['action'] === 'reject') {
            $payment->status = 'Rejected';
            $payment->rejection_reason = $data['rejection_reason'] ?? null;
        } else {
            $payment->status = 'Needs Info';
        }

        $payment->admin_response = $data['admin_response'] ?? null;
        $payment->validated_by = session('admin_id');
        $payment->validated_at = now();
        $payment->save();

        // Email the resident a receipt once the payment is validated.
        if ($payment->status === 'Validated' && $payment->resident?->email) {
            try {
                Mail::to($payment->resident->email)->send(new PaymentReceiptMail($payment));
            } catch (\Throwable $e) {
                // Mail transport unavailable — validation still succeeds.
            }
        }

        return response()->json(['success' => true, 'payment' => $this->map($payment->fresh('resident'))]);
    }

    /** GET /api/payments/{id}/receipt — stream the resident's receipt (owner or staff). */
    public function receipt(Request $request, int $id)
    {
        return $this->streamReceipt($request, $id, 'receipt_path');
    }

    /** GET /api/payments/{id}/official-receipt — stream the official receipt (owner or staff). */
    public function officialReceipt(Request $request, int $id)
    {
        return $this->streamReceipt($request, $id, 'official_receipt_path');
    }

    /** GET /api/transparency/summary — collected vs spent + where it went (resident). */
    public function transparency(Request $request)
    {
        // Period scope (defaults to year-to-date).
        $from = $request->query('from') ?: now()->startOfYear()->toDateString();
        $to = $request->query('to') ?: now()->toDateString();

        $paidInPeriod = PaymentSubmission::where('status', 'Validated')
            ->whereRaw('date(COALESCE(validated_at, created_at)) between ? and ?', [$from, $to]);

        $collected = (float) (clone $paidInPeriod)->sum('amount');
        $paymentsCount = (clone $paidInPeriod)->count();

        $expensesInPeriod = HoaExpense::whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to);

        $spent = (float) (clone $expensesInPeriod)->sum('amount');
        $operating = (float) (clone $expensesInPeriod)->where('fund', 'Operating')->sum('amount');
        $reserve = (float) (clone $expensesInPeriod)->where('fund', 'Reserve')->sum('amount');

        // Budgets: recurring (period = null) plus any month within the range.
        $catBudget = [];
        foreach (Budget::whereNull('period')
            ->orWhereBetween('period', [substr($from, 0, 7), substr($to, 0, 7)])
            ->get() as $b) {
            $catBudget[$b->category] = ($catBudget[$b->category] ?? 0) + (float) $b->amount;
        }

        $categories = (clone $expensesInPeriod)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'category' => $r->category ?: 'Uncategorized',
                'total' => (float) $r->total,
                'budget' => round($catBudget[$r->category ?: 'Uncategorized'] ?? 0, 2),
            ])
            ->all();

        return response()->json([
            'period' => ['from' => $from, 'to' => $to],
            'collected' => $collected,
            'spent' => $spent,
            'operating' => $operating,
            'reserve' => $reserve,
            'net' => $collected - $spent,
            'balance' => $collected - $spent, // backward-compatible key
            'categories' => $categories,
            'payments_count' => $paymentsCount,
        ]);
    }

    private function streamReceipt(Request $request, int $id, string $column)
    {
        $payment = PaymentSubmission::findOrFail($id);
        $isStaff = session('admin_id') || Auth::guard('officer')->check();
        $isOwner = $this->residentId($request) === $payment->resident_id;

        if (! $isStaff && ! $isOwner) {
            abort(403);
        }

        $path = $payment->{$column};
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($path));
    }

    private function map(PaymentSubmission $p): array
    {
        return [
            'id' => $p->id,
            'amount' => (float) $p->amount,
            'reference_no' => $p->reference_no,
            'gcash_sender_name' => $p->gcash_sender_name,
            'gcash_number' => $p->gcash_number,
            'paid_at' => $p->paid_at?->format('Y-m-d'),
            'status' => $p->status,
            'admin_response' => $p->admin_response,
            'rejection_reason' => $p->rejection_reason,
            'has_receipt' => (bool) $p->receipt_path,
            'has_official_receipt' => (bool) $p->official_receipt_path,
            'resident' => $p->resident?->name,
            'created_at' => $p->created_at?->format('M d, Y g:i A'),
        ];
    }
}
