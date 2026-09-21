<?php

namespace App\Http\Controllers;

use App\Models\HoaPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HoaPolicyController extends Controller
{
    /**
     * Display a listing of HOA policies for residents to view.
     * Residents can view community rules, regulations, and governing policies.
     */
    public function index(Request $request)
    {
        // Only residents can view HOA policies
        $resident = Auth::guard('resident')->user();
        if (!$resident) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return Cache::remember('hoa.policies', 30, function () {
            return HoaPolicy::orderByDesc('created_at')
                ->get()
                ->map(function ($policy) {
                    return [
                        'id' => $policy->id,
                        'title' => $policy->title,
                        'description' => $policy->description,
                        'category' => $policy->category,
                        'effective_date' => $policy->effective_date?->format('Y-m-d'),
                        'status' => $policy->status,
                    ];
                })->all();
        });
    }
}
