<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    /** GET /api/budgets */
    public function index()
    {
        return response()->json(Budget::orderBy('category')->get());
    }

    /** POST /api/budgets */
    public function store(Request $request)
    {
        $data = $this->validateBudget($request);

        return response()->json(['success' => true, 'budget' => Budget::create($data)]);
    }

    /** PUT /api/budgets/{id} */
    public function update(Request $request, int $id)
    {
        $budget = Budget::findOrFail($id);
        $budget->update($this->validateBudget($request));

        return response()->json(['success' => true, 'budget' => $budget->fresh()]);
    }

    /** DELETE /api/budgets/{id} */
    public function destroy(int $id)
    {
        Budget::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    private function validateBudget(Request $request): array
    {
        return $request->validate([
            'category' => 'required|string|max:100',
            'fund' => 'required|in:Operating,Reserve',
            'amount' => 'required|numeric|min:0|max:100000000',
            'period' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
        ]);
    }
}
