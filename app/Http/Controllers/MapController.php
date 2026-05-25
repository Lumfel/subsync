<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Household;
use App\Models\IssueReport;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function households()
    {
        return response()->json(
            Household::select('id', 'block_lot_number', 'status', 'latitude', 'longitude')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
        );
    }

    public function facilities()
    {
        return response()->json(
            Facility::select('id', 'name', 'description', 'status', 'latitude', 'longitude')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
        );
    }

    public function issues()
    {
        return response()->json(
            IssueReport::select('id', 'title', 'category', 'status', 'latitude', 'longitude', 'created_at')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where(function ($q) {
                    // Keep all non-resolved issues; only keep resolved ones from the last 7 days
                    $q->where('status', '!=', 'Resolved')
                      ->orWhere('updated_at', '>=', now()->subDays(7));
                })
                ->get()
        );
    }

    public function storeFacility(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'status'      => 'sometimes|in:Active,Inactive',
        ]);

        $data['admin_id'] = session('admin_id');
        $data['status']   = $data['status'] ?? 'Active';

        $facility = Facility::create($data);

        return response()->json(['success' => true, 'facility' => $facility]);
    }

    public function updateFacility(Request $request, int $id)
    {
        $facility = Facility::findOrFail($id);

        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:150',
            'description' => 'nullable|string|max:500',
            'latitude'    => 'sometimes|required|numeric|between:-90,90',
            'longitude'   => 'sometimes|required|numeric|between:-180,180',
            'status'      => 'sometimes|in:Active,Inactive',
        ]);

        $facility->update($data);

        return response()->json(['success' => true, 'facility' => $facility->fresh()]);
    }

    public function deleteFacility(int $id)
    {
        Facility::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function updateHouseholdLocation(Request $request, int $id)
    {
        $household = Household::findOrFail($id);

        $data = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $household->update($data);

        return response()->json(['success' => true]);
    }
}
