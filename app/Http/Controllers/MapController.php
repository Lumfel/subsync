<?php

namespace App\Http\Controllers;

use App\Events\SubSyncEvent;
use App\Models\Facility;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\MapSetting;
use App\Models\SseEvent;
use App\Models\SubdivisionBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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

    /** PUT /api/map/settings — admin customizes the fixed map area and overlay */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'min_lat' => 'nullable|numeric|between:-90,90',
            'max_lat' => 'nullable|numeric|between:-90,90',
            'min_lng' => 'nullable|numeric|between:-180,180',
            'max_lng' => 'nullable|numeric|between:-180,180',
            'center_lat' => 'nullable|numeric|between:-90,90',
            'center_lng' => 'nullable|numeric|between:-180,180',
            'zoom' => 'nullable|integer|between:1,22',
            'overlay_url' => 'nullable|string|max:255',
            'overlay_min_lat' => 'nullable|numeric|between:-90,90',
            'overlay_max_lat' => 'nullable|numeric|between:-90,90',
            'overlay_min_lng' => 'nullable|numeric|between:-180,180',
            'overlay_max_lng' => 'nullable|numeric|between:-180,180',
            'overlay_opacity' => 'nullable|numeric|between:0,1',
            'overlay_rotation' => 'nullable|numeric|between:-360,360',
            'overlay_enabled' => 'nullable|boolean',
        ]);

        if (isset($data['min_lat'], $data['max_lat']) && $data['max_lat'] <= $data['min_lat']) {
            return response()->json(['message' => 'max_lat must be greater than min_lat'], 422);
        }
        if (isset($data['min_lng'], $data['max_lng']) && $data['max_lng'] <= $data['min_lng']) {
            return response()->json(['message' => 'max_lng must be greater than min_lng'], 422);
        }

        $setting = MapSetting::current();
        $setting->update($data);
        Cache::forget('map.all');

        return response()->json(['success' => true, 'settings' => $setting->fresh()]);
    }

    /** POST /api/map/overlay/upload — upload a new blueprint/site plan image */
    public function uploadOverlay(Request $request)
    {
        $request->validate([
            'overlay' => 'required|image|max:12288',
        ]);

        $path = $request->file('overlay')->store('map', 'public');
        $url = Storage::url($path);

        $setting = MapSetting::current();
        $setting->update(['overlay_url' => $url]);
        Cache::forget('map.all');

        return response()->json([
            'success' => true,
            'overlay_url' => $url,
            'settings' => $setting->fresh(),
        ]);
    }

    /** GET /api/map/blocks — list all block partitions with polygon coordinates and counts */
    public function blocks()
    {
        $blocks = SubdivisionBlock::orderByRaw('CAST(block_number AS UNSIGNED) ASC')->get();
        $households = Household::select('id', 'block_lot_number', 'status', 'latitude', 'longitude')->get();

        $enriched = $blocks->map(function ($b) use ($households) {
            $matchingHouseholds = $households->filter(function ($h) use ($b) {
                $parsed = SubdivisionBlock::parseBlockLot($h->block_lot_number);

                return $parsed['block'] !== null && (string) $parsed['block'] === (string) $b->block_number;
            });

            return [
                'id' => $b->id,
                'block_number' => $b->block_number,
                'name' => $b->name,
                'color' => $b->color,
                'lot_range' => $b->lot_range,
                'total_lots' => (int) ($b->total_lots ?? 0),
                'start_label' => $b->start_label,
                'start_lat' => $b->start_lat,
                'start_lng' => $b->start_lng,
                'label_lat' => $b->label_lat,
                'label_lng' => $b->label_lng,
                'coordinates' => $b->coordinates,
                'description' => $b->description,
                'households_count' => $matchingHouseholds->count(),
                'occupied_count' => $matchingHouseholds->where('status', 'Occupied')->count(),
                'vacant_count' => $matchingHouseholds->where('status', 'Vacant')->count(),
            ];
        });

        return response()->json($enriched);
    }

    /** PUT /api/map/block/{id} — update a single block polygon coordinates or styling / labels */
    public function updateBlock(Request $request, $id)
    {
        $block = SubdivisionBlock::findOrFail($id);
        $data = $request->validate([
            'coordinates' => 'nullable|array|min:3',
            'name' => 'nullable|string|max:80',
            'color' => 'nullable|string|max:30',
            'lot_range' => 'nullable|string|max:80',
            'total_lots' => 'nullable|integer|min:0',
            'start_label' => 'nullable|string|max:50',
            'start_lat' => 'nullable|numeric|between:-90,90',
            'start_lng' => 'nullable|numeric|between:-180,180',
            'label_lat' => 'nullable|numeric|between:-90,90',
            'label_lng' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
        ]);

        $block->update(array_filter($data, fn ($v) => $v !== null));
        Cache::forget('map.all');

        return response()->json(['success' => true, 'block' => $block->fresh()]);
    }

    /** PUT /api/map/blocks — bulk update all block coordinates and positions */
    public function bulkUpdateBlocks(Request $request)
    {
        $data = $request->validate([
            'blocks' => 'required|array',
            'blocks.*.id' => 'required|integer|exists:subdivision_blocks,id',
            'blocks.*.coordinates' => 'required|array|min:3',
            'blocks.*.start_lat' => 'nullable|numeric',
            'blocks.*.start_lng' => 'nullable|numeric',
            'blocks.*.label_lat' => 'nullable|numeric',
            'blocks.*.label_lng' => 'nullable|numeric',
            'blocks.*.name' => 'nullable|string|max:80',
            'blocks.*.lot_range' => 'nullable|string|max:80',
            'blocks.*.total_lots' => 'nullable|integer|min:0',
            'blocks.*.start_label' => 'nullable|string|max:50',
            'blocks.*.color' => 'nullable|string|max:30',
            'blocks.*.description' => 'nullable|string',
        ]);

        foreach ($data['blocks'] as $b) {
            $upd = ['coordinates' => $b['coordinates']];
            foreach (['start_lat', 'start_lng', 'label_lat', 'label_lng', 'name', 'lot_range', 'total_lots', 'start_label', 'color', 'description'] as $col) {
                if (array_key_exists($col, $b)) {
                    $upd[$col] = $b[$col];
                }
            }
            SubdivisionBlock::where('id', $b['id'])->update($upd);
        }

        Cache::forget('map.all');

        // Broadcast real-time live update over WebSockets (Reverb) and SSE
        SseEvent::emit('subdivision_blocks', 'block', ['action' => 'updated']);
        try {
            broadcast(new SubSyncEvent('block', ['action' => 'updated']));
        } catch (\Throwable $e) {
            // Reverb fallback
        }

        return response()->json(['success' => true]);
    }

    /** GET /api/map/all — map data (respects the configured fixed area) */
    public function all()
    {
        return Cache::remember('map.all', 60, function () {
            $b = self::bounds();
            $blocks = SubdivisionBlock::orderByRaw('CAST(block_number AS UNSIGNED) ASC')->get();
            $colorMap = $blocks->pluck('color', 'block_number')->toArray();

            return [
                'blocks' => $blocks->toArray(),
                'households' => Household::select('id', 'block_lot_number', 'status', 'latitude', 'longitude')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereBetween('latitude', [$b['min_lat'], $b['max_lat']])
                    ->whereBetween('longitude', [$b['min_lng'], $b['max_lng']])
                    ->get()
                    ->map(fn ($h) => $this->enrichHousehold($h, $colorMap))
                    ->all(),
                'facilities' => Facility::select('id', 'name', 'description', 'image_path', 'status', 'latitude', 'longitude')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereBetween('latitude', [$b['min_lat'], $b['max_lat']])
                    ->whereBetween('longitude', [$b['min_lng'], $b['max_lng']])
                    ->get()
                    ->map(fn ($f) => $f->toArray())
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
                    ->get()
                    ->map(fn ($i) => $i->toArray())
                    ->all(),
            ];
        });
    }

    public function households()
    {
        $b = self::bounds();
        $blocks = SubdivisionBlock::orderByRaw('CAST(block_number AS UNSIGNED) ASC')->get();
        $colorMap = $blocks->pluck('color', 'block_number')->toArray();

        return response()->json(
            Household::select('id', 'block_lot_number', 'status', 'latitude', 'longitude')
                ->whereBetween('latitude', [$b['min_lat'], $b['max_lat']])
                ->whereBetween('longitude', [$b['min_lng'], $b['max_lng']])
                ->with('media')
                ->get()
                ->map(fn ($h) => $this->enrichHousehold($h, $colorMap))
        );
    }

    private function enrichHousehold(Household $h, array $colorMap): array
    {
        $parsed = SubdivisionBlock::parseBlockLot($h->block_lot_number);
        $blockNum = $parsed['block'];
        $color = $blockNum && isset($colorMap[$blockNum]) ? $colorMap[$blockNum] : '#2563eb';

        return array_merge($h->toArray(), [
            'parsed_block' => $blockNum,
            'parsed_lot' => $parsed['lot'],
            'block_name' => $blockNum ? "Block {$blockNum}" : 'Unassigned',
            'lot_name' => $parsed['lot'] ? "Lot {$parsed['lot']}" : null,
            'formatted_address' => $parsed['display'],
            'block_color' => $color,
        ]);
    }

    public function facilities()
    {
        $b = self::bounds();

        return response()->json(
            Facility::select('id', 'name', 'description', 'image_path', 'status', 'latitude', 'longitude')
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
            IssueReport::select('id', 'title', 'category', 'status', 'priority', 'latitude', 'longitude', 'created_at')
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
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'sometimes|in:Active,Inactive',
            'image' => 'nullable|image|max:8192',
        ]);

        if (! self::withinArea($data['latitude'], $data['longitude'])) {
            return response()->json(['success' => false, 'message' => 'Facilities must be located within the fixed subdivision area.'], 422);
        }

        unset($data['image']);
        $data['admin_id'] = session('admin_id');
        $data['status'] = $data['status'] ?? 'Active';

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('facilities', 'public');
        }

        $facility = Facility::create($data);
        Cache::forget('map.all');

        return response()->json(['success' => true, 'facility' => $facility->fresh()]);
    }

    public function updateFacility(Request $request, int $id)
    {
        $facility = Facility::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string|max:500',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'status' => 'sometimes|in:Active,Inactive',
            'image' => 'nullable|image|max:8192',
            'remove_image' => 'nullable|boolean',
        ]);

        if (isset($data['latitude'], $data['longitude']) && ! self::withinArea($data['latitude'], $data['longitude'])) {
            return response()->json(['success' => false, 'message' => 'Facilities must be located within the fixed subdivision area.'], 422);
        }

        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            if ($facility->image_path) {
                Storage::disk('public')->delete($facility->image_path);
            }
            $data['image_path'] = $request->file('image')->store('facilities', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($facility->image_path) {
                Storage::disk('public')->delete($facility->image_path);
            }
            $data['image_path'] = null;
        }

        $facility->update($data);
        Cache::forget('map.all');

        return response()->json(['success' => true, 'facility' => $facility->fresh()]);
    }

    public function deleteFacility(int $id)
    {
        $facility = Facility::findOrFail($id);
        if ($facility->image_path) {
            Storage::disk('public')->delete($facility->image_path);
        }
        $facility->delete();
        Cache::forget('map.all');

        return response()->json(['success' => true]);
    }

    public function updateHouseholdLocation(Request $request, int $id)
    {
        $household = Household::findOrFail($id);

        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if (! self::withinArea($data['latitude'], $data['longitude'])) {
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
                'min_lat' => (float) $s->min_lat,
                'max_lat' => (float) $s->max_lat,
                'min_lng' => (float) $s->min_lng,
                'max_lng' => (float) $s->max_lng,
                'center_lat' => (float) $s->center_lat,
                'center_lng' => (float) $s->center_lng,
                'zoom' => (int) $s->zoom,
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
