<?php

namespace App\Http\Controllers;

use App\Models\HoaExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HoaExpenseController extends Controller
{
    /**
     * Display a listing of HOA expenses for residents to view.
     * Residents can see how their HOA fees are being utilized.
     */
    public function index(Request $request)
    {
        // Only residents can view HOA expenses transparency
        $resident = Auth::guard('resident')->user();
        if (!$resident) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return Cache::remember('hoa.expenses', 30, function () {
            return HoaExpense::orderByDesc('created_at')
                ->get()
                ->map(function ($expense) {
                    return [
                        'id' => $expense->id,
                        'title' => $expense->title,
                        'description' => $expense->description,
                        'amount' => $expense->amount,
                        'expense_date' => $expense->expense_date?->format('Y-m-d'),
                        'category' => $expense->category,
                        'status' => $expense->status,
                        'receipt_path' => $expense->receipt_path,
                        'created_at' => $expense->created_at?->format('M d, Y'),
                    ];
                })->all();
        });
    }
}