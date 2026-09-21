<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\MapSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MapController extends Controller
{
    /** Defaults used when no settings row exists yet. */
    private const DEFAULT_BOUNDS = [
        'min_lat' => 10.6195,
        'max_lat' => 10.6260,
        'min_lng' => 122.9580,
        'max_lng' => 122.9650,
        'center_lat' => 10.62269,
        'center_lng' => 122.96134,
        'zoom' => 17,
    ];

    /** GET /api/map/settings — current fixed map area + center + zoom (admin can edit) */
    public function settings()
    {
        return response()->json(MapSetting::current());
    }

    /** PUT /api/map/settings — admin customizes the fixed map area */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'min_lat'    => 'required|numeric|between:-90,90',
            'max_lat'    => 'required|numeric|between:-90,90',
            'min_lng'    => 'required|numeric|between:-180,180',
            'max_lng'    => 'required|numeric|between:-180,180',
            'center_lat' => 'nullable|numeric|between:-90,90',
            'center_lng' => 'nullable|numeric|between:-180,180',
            'zoom'       => 'nullable|integer|between:1,22',
        ]);

        $setting = MapSetting::current();
        $setting->update($data);
        Cache::forget('map.all');

        return response()->json(['success' => true, 'settings' => $setting->fresh()]);
    }

    /** GET /api/map/all — map data (respects the configured fixed area) */
    public function all()
    {
        return Cache::remember('map.all', 60, function () {
            $b = self::bounds();
            return [
                'households' => Household::select('id', 'block_lot_number', 'status', 'latitude', 'longitude')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereBetween('latitude', [$b['min_lat'], $b['max_lat']])
                    ->whereBetween('longitude', [$b['min_lng'], $b['max_lng']])
                    ->with('media')
                    ->get()
                    ->map(fn($h) => $h->toArray())
                    ->all(),
                'facilities' => Facility::select('id', 'name', 'description', 'status', 'latitude', 'longitude')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereBetween('latitude', [$b['min_lat'], $b['max_lat']])
                    ->whereBetween('longitude', [$b['min_lng'], $b['max_lng']])
                    ->with('media')
                    ->get()
                    ->map(fn($f) => $f->toArray())
                    ->all(),
                'issues' => IssueReport::select('id', 'title', 'category', 'status', 'latitude', 'longitude', 'created_at')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereBetween('latitude', [$b['min_lat'], $b['max_lat']])
                    ->whereBetween('longitude', [$b['min_lng'], $b['max_lng']])
                    ->where(function ($q) {
                        $q->where('status', '!=', 'Resolved')
                          ->orWhere('updated_at', '>=', now()->subDays(7));
                    })
                    ->with('media')
                    ->get()
                    ->map(fn($i) => $i->toArray())
                    ->all(),
            ];
        });
    }

    public function households()
    {
        $b = self::bounds();
        return response()->json(
            Household::select('id', 'block_lot_number', 'status', 'latitude', 'longitude')
                ->whereBetween('latitude', [$b['min_lat'], $b['max_lat']])
                ->whereBetween('longitude', [$b['min_lng'], $b['max_lng']])
                ->with('media')
                ->get()
        );
    }

    public function facilities()
    {
        $b = self::bounds();
        return response()->json(
            Facility::select('id', 'name', 'description', 'status', 'latitude', 'longitude')
                ->whereBetween('latitude', [$b['min_lat'], $b['max_lat']])
                ->whereBetween('longitude', [$b['min_lng'], $b['max_lng']])
                ->with('media')
                ->get()
        );
    }

    public function issues()
    {
        $b = self::bounds();
        return response()->json(
            IssueReport::select('id', 'title', 'category', 'status', 'latitude', 'longitude', 'created_at')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereBetween('latitude', [$b['min_lat'], $b['max_lat']])
                ->whereBetween('longitude', [$b['min_lng'], $b['max_lng']])
                ->where(function ($q) {
                    $q->where('status', '!=', 'Resolved')
                      ->orWhere('updated_at', '>=', now()->subDays(7));
                })
                ->with('media')
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

        if (!self::withinArea($data['latitude'], $data['longitude'])) {
            return response()->json(['success' => false, 'message' => 'Facilities must be located within the fixed subdivision area.'], 422);
        }

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

        if (isset($data['latitude'], $data['longitude']) && !self::withinArea($data['latitude'], $data['longitude'])) {
            return response()->json(['success' => false, 'message' => 'Facilities must be located within the fixed subdivision area.'], 422);
        }

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

        if (!self::withinArea($data['latitude'], $data['longitude'])) {
            return response()->json(['success' => false, 'message' => 'Households must be located within the fixed subdivision area.'], 422);
        }

        $household->update($data);

        return response()->json(['success' => true]);
    }

    /** Bounds array from the editable settings (fallback to defaults). */
    public static function bounds(): array
    {
        try {
            $s = MapSetting::current();
            return [
                'min_lat'    => (float) $s->min_lat,
                'max_lat'    => (float) $s->max_lat,
                'min_lng'    => (float) $s->min_lng,
                'max_lng'    => (float) $s->max_lng,
                'center_lat' => (float) $s->center_lat,
                'center_lng' => (float) $s->center_lng,
                'zoom'       => (int) $s->zoom,
            ];
        } catch (\Throwable $e) {
            return self::DEFAULT_BOUNDS;
        }
    }

    private static function withinArea(float|int $latitude, float|int $longitude): bool
    {
        $b = self::bounds();
        return $latitude >= $b['min_lat']
            && $latitude <= $b['max_lat']
            && $longitude >= $b['min_lng']
            && $longitude <= $b['max_lng'];
    }
}