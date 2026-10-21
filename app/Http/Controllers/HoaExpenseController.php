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
            return HoaExpense::orderByDesc('expense_date')->orderByDesc('id')
                ->get()
                ->map(fn ($e) => $this->map($e))
                ->all();
        });
    }

    /** POST /api/hoa-expenses — admin records an expense. */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('hoa-expenses', 'public');
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
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('hoa-expenses', 'public');
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
            Storage::disk('public')->delete($expense->receipt_path);
        }
        $expense->delete();
        Cache::forget('hoa.expenses');

        return response()->json(['success' => true]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01|max:100000000',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:100',
            'status' => 'nullable|string|max:50',
            'fund' => 'nullable|in:Operating,Reserve',
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
            'has_receipt' => (bool) $e->receipt_path,
            'receipt_url' => $e->receipt_path ? Storage::url($e->receipt_path) : null,
            'created_at' => $e->created_at?->format('M d, Y'),
        ];
    }
}
