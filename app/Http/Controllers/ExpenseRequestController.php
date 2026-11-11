<?php

namespace App\Http\Controllers;

use App\Models\ExpenseRequest;
use App\Models\HoaExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseRequestController extends Controller
{
    /**
     * GET /api/expense-requests
     * Admin sees all; officers see their own claims.
     */
    public function index(Request $request)
    {
        $officer = Auth::guard('officer')->user();
        $isAdmin = (bool) (session('admin_id') || Auth::guard('admin')->check());

        if (! $officer && ! $isAdmin) {
            abort(403, 'Unauthorized.');
        }

        $query = ExpenseRequest::with(['officer:id,name', 'issue:id,title,status', 'reviewer:id,name', 'hoaExpense:id'])
            ->orderByDesc('created_at');

        // Officers only see their own submissions
        if ($officer && ! $isAdmin) {
            $query->where('officer_id', $officer->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->get()->map(fn ($r) => $this->map($r)));
    }

    /**
     * POST /api/expense-requests
     * Officer submits an expense claim/request with receipt proof.
     */
    public function store(Request $request)
    {
        $officer = Auth::guard('officer')->user();
        if (! $officer) {
            abort(403, 'Only officers can submit expense requests.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01|max:100000000',
            'expense_date' => 'required|date',
            'category' => 'required|in:Maintenance,Utilities,Security,Landscaping,Amenities,Insurance,Administrative,Events,Other',
            'fund' => 'nullable|in:Operating,Reserve',
            'issue_id' => 'nullable|exists:issue_reports,id',
            'receipt' => 'nullable|image|max:8192',
        ]);

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('expense-requests', 'local');
        }
        unset($data['receipt']);

        $data['officer_id'] = $officer->id;
        $data['fund'] = $data['fund'] ?? 'Operating';
        $data['status'] = 'Pending';

        $expReq = ExpenseRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Expense request submitted for admin approval.',
            'request' => $this->map($expReq->fresh(['officer', 'issue'])),
        ]);
    }

    /**
     * GET /api/expense-requests/{id}/receipt
     * Streams receipt image to staff or the submitting officer.
     */
    public function receipt(int $id)
    {
        $req = ExpenseRequest::findOrFail($id);
        $this->assertCanAccess($req);

        abort_unless($req->receipt_path, 404);

        $disk = Storage::disk('local')->exists($req->receipt_path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($req->receipt_path), 404);

        return response()->file(Storage::disk($disk)->path($req->receipt_path));
    }

    /**
     * POST /api/expense-requests/{id}/approve
     * Admin 1-click approval: automatically converts request into an official HoaExpense.
     */
    public function approve(Request $request, int $id)
    {
        $adminId = session('admin_id') ?? Auth::guard('admin')->id();
        if (! $adminId) {
            abort(403, 'Only administrators can approve expense requests.');
        }

        $input = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'fund' => 'nullable|in:Operating,Reserve',
            'category' => 'nullable|in:Maintenance,Utilities,Security,Landscaping,Amenities,Insurance,Administrative,Events,Other',
        ]);

        $hoaExpense = DB::transaction(function () use ($id, $adminId, $input) {
            $req = ExpenseRequest::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($req->status === 'Approved') {
                abort(422, 'This expense request has already been approved.');
            }

            // Copy receipt over to hoa-expenses bucket if present
            $hoaReceiptPath = null;
            if ($req->receipt_path && Storage::disk('local')->exists($req->receipt_path)) {
                $ext = pathinfo($req->receipt_path, PATHINFO_EXTENSION) ?: 'jpg';
                $hoaReceiptPath = 'hoa-expenses/from_req_'.$req->id.'_'.uniqid().'.'.$ext;
                Storage::disk('local')->copy($req->receipt_path, $hoaReceiptPath);
            }

            // 1. Create the official HoaExpense ledger entry
            $expense = HoaExpense::create([
                'title' => $req->title,
                'description' => $req->description.($req->officer ? " (Filed by Officer {$req->officer->name})" : ''),
                'amount' => $req->amount,
                'expense_date' => $req->expense_date,
                'category' => $input['category'] ?? $req->category,
                'fund' => $input['fund'] ?? $req->fund ?? 'Operating',
                'status' => 'Approved',
                'receipt_path' => $hoaReceiptPath ?: $req->receipt_path,
                'issue_id' => $req->issue_id,
                'expense_request_id' => $req->id,
            ]);

            // 2. Mark the request as Approved and link the resulting HoaExpense
            $req->update([
                'status' => 'Approved',
                'fund' => $input['fund'] ?? $req->fund,
                'category' => $input['category'] ?? $req->category,
                'admin_notes' => $input['admin_notes'] ?? null,
                'reviewed_at' => now(),
                'reviewed_by' => $adminId,
                'hoa_expense_id' => $expense->id,
            ]);

            Cache::forget('hoa.expenses');

            return $expense;
        });

        return response()->json([
            'success' => true,
            'message' => 'Expense request approved and posted to the HOA transparency ledger.',
            'hoa_expense_id' => $hoaExpense->id,
        ]);
    }

    /**
     * POST /api/expense-requests/{id}/reject
     * Admin rejects an officer expense claim.
     */
    public function reject(Request $request, int $id)
    {
        $adminId = session('admin_id') ?? Auth::guard('admin')->id();
        if (! $adminId) {
            abort(403, 'Only administrators can reject expense requests.');
        }

        $input = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $req = ExpenseRequest::findOrFail($id);
        if ($req->status === 'Approved') {
            abort(422, 'Cannot reject an already approved expense.');
        }

        $req->update([
            'status' => 'Rejected',
            'admin_notes' => $input['admin_notes'],
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense request rejected.',
        ]);
    }

    /**
     * DELETE /api/expense-requests/{id}
     * Officer deletes their own pending request, or admin deletes.
     */
    public function destroy(int $id)
    {
        $req = ExpenseRequest::findOrFail($id);
        $this->assertCanAccess($req);

        if ($req->status === 'Approved') {
            abort(422, 'Approved requests cannot be deleted directly. Delete the associated HOA Expense instead.');
        }

        if ($req->receipt_path && Storage::disk('local')->exists($req->receipt_path)) {
            Storage::disk('local')->delete($req->receipt_path);
        }

        $req->delete();

        return response()->json(['success' => true]);
    }

    private function assertCanAccess(ExpenseRequest $req): void
    {
        $officer = Auth::guard('officer')->user();
        $isAdmin = (bool) (session('admin_id') || Auth::guard('admin')->check());

        if ($isAdmin) {
            return;
        }

        if ($officer && (int) $req->officer_id === (int) $officer->id) {
            return;
        }

        abort(403, 'Access denied.');
    }

    private function map(ExpenseRequest $r): array
    {
        return [
            'id' => $r->id,
            'title' => $r->title,
            'description' => $r->description,
            'amount' => (float) $r->amount,
            'expense_date' => $r->expense_date?->format('Y-m-d'),
            'category' => $r->category,
            'fund' => $r->fund ?: 'Operating',
            'status' => $r->status,
            'admin_notes' => $r->admin_notes,
            'has_receipt' => (bool) $r->receipt_path,
            'receipt_url' => $r->receipt_path ? '/api/expense-requests/'.$r->id.'/receipt' : null,
            'officer' => $r->officer?->name ?? '—',
            'officer_id' => $r->officer_id,
            'issue_id' => $r->issue_id,
            'issue_title' => $r->issue?->title,
            'issue_status' => $r->issue?->status,
            'hoa_expense_id' => $r->hoa_expense_id,
            'reviewed_at' => $r->reviewed_at?->format('M d, Y h:i A'),
            'reviewed_by' => $r->reviewer?->name,
            'created_at' => $r->created_at?->format('M d, Y'),
        ];
    }
}
