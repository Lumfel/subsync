<?php

namespace App\Http\Controllers;

use App\Models\HoaPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HoaPolicyController extends Controller
{
    /** GET /api/hoa-policies — list policies (all roles). */
    public function index()
    {
        return Cache::remember('hoa.policies', 30, function () {
            return HoaPolicy::orderByDesc('created_at')
                ->get()
                ->map(fn ($policy) => [
                    'id' => $policy->id,
                    'title' => $policy->title,
                    'description' => $policy->description,
                    'category' => $policy->category,
                    'effective_date' => $policy->effective_date?->format('Y-m-d'),
                    'status' => $policy->status,
                ])->all();
        });
    }

    /** POST /api/hoa-policies — create (admin). */
    public function store(Request $request)
    {
        $policy = HoaPolicy::create($this->validateData($request));
        Cache::forget('hoa.policies');

        return response()->json(['success' => true, 'policy' => $policy]);
    }

    /** PUT /api/hoa-policies/{id} — update (admin). */
    public function update(Request $request, int $id)
    {
        $policy = HoaPolicy::findOrFail($id);
        $policy->update($this->validateData($request));
        Cache::forget('hoa.policies');

        return response()->json(['success' => true, 'policy' => $policy->fresh()]);
    }

    /** DELETE /api/hoa-policies/{id} — archive (soft) instead of destroying history. */
    public function destroy(int $id)
    {
        $policy = HoaPolicy::findOrFail($id);
        $policy->update(['status' => 'Archived']);
        Cache::forget('hoa.policies');

        return response()->json(['success' => true, 'archived' => true]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'effective_date' => 'required|date',
            'status' => 'nullable|in:Active,Inactive,Archived',
        ]);
    }
}
