<?php

namespace App\Http\Controllers;

use App\Models\HoaExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HoaExpenseController extends Controller
{
    /** GET /api/hoa-expenses — how HOA funds are utilized (all roles). */
    public function index()
    {
        return Cache::remember('hoa.expenses', 30, function () {
            return HoaExpense::with('issue:id,title')->orderByDesc('expense_date')->orderByDesc('id')
                ->get()
                ->map(fn ($e) => $this->map($e))
                ->all();
        });
    }

    /** GET /api/hoa-expenses/{id}/receipt — stream the stored receipt (all signed-in portals). */
    public function receipt(int $id)
    {
        $expense = HoaExpense::findOrFail($id);
        abort_unless($expense->receipt_path, 404);

        $disk = Storage::disk('local')->exists($expense->receipt_path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($expense->receipt_path), 404);

        return response()->file(Storage::disk($disk)->path($expense->receipt_path));
    }

    /** POST /api/hoa-expenses — admin records an expense. */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('hoa-expenses', 'local');
        }
        unset($data['receipt']);
        $data['status'] = $data['status'] ?? 'Approved';
        $data['fund'] = $data['fund'] ?? 'Operating';

        $expense = HoaExpense::create($data);
        Cache::forget('hoa.expenses');

        return response()->json(['success' => true, 'expense' => $this->map($expense)]);
    }

    /** PUT /api/hoa-expenses/{id} — admin updates an expense. */
    public function update(Request $request, int $id)
    {
        $expense = HoaExpense::findOrFail($id);
        $data = $this->validateData($request);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                $this->deleteStoredReceipt($expense->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('hoa-expenses', 'local');
        }
        unset($data['receipt']);

        $expense->update($data);
        Cache::forget('hoa.expenses');

        return response()->json(['success' => true, 'expense' => $this->map($expense->fresh())]);
    }

    /** DELETE /api/hoa-expenses/{id} — admin deletes an expense. */
    public function destroy(int $id)
    {
        $expense = HoaExpense::findOrFail($id);
        if ($expense->receipt_path) {
            $this->deleteStoredReceipt($expense->receipt_path);
        }
        $expense->delete();
        Cache::forget('hoa.expenses');

        return response()->json(['success' => true]);
    }

    /** Remove a stored receipt from whichever disk holds it (private or legacy public). */
    private function deleteStoredReceipt(string $path): void
    {
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01|max:100000000',
            'expense_date' => 'required|date',
            'category' => 'required|in:Maintenance,Utilities,Security,Landscaping,Amenities,Insurance,Administrative,Events,Other',
            'status' => 'nullable|in:Approved,Pending,Paid',
            'fund' => 'nullable|in:Operating,Reserve',
            'issue_id' => 'nullable|exists:issue_reports,id',
            'receipt' => 'nullable|image|max:8192',
        ]);
    }

    private function map(HoaExpense $e): array
    {
        return [
            'id' => $e->id,
            'title' => $e->title,
            'description' => $e->description,
            'amount' => (float) $e->amount,
            'expense_date' => $e->expense_date?->format('Y-m-d'),
            'category' => $e->category,
            'status' => $e->status,
            'fund' => $e->fund ?: 'Operating',
            'issue_id' => $e->issue_id,
            'issue_title' => $e->issue?->title,
            'has_receipt' => (bool) $e->receipt_path,
            'receipt_url' => $e->receipt_path ? '/api/hoa-expenses/'.$e->id.'/receipt' : null,
            'created_at' => $e->created_at?->format('M d, Y'),
        ];
    }
}
