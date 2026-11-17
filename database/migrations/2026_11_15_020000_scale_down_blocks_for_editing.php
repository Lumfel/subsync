<?php

use App\Models\SubdivisionBlock;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $factor = 0.72; // 28% smaller to leave clear road space between blocks

        $blocks = SubdivisionBlock::where('block_number', '!=', '3')->get();
        foreach ($blocks as $b) {
            $coords = $b->coordinates;
            if (! is_array($coords) || count($coords) < 3) {
                continue;
            }

            $n = count($coords);
            $lats = array_column($coords, 0);
            $lngs = array_column($coords, 1);
            $cLat = array_sum($lats) / $n;
            $cLng = array_sum($lngs) / $n;

            $newCoords = [];
            foreach ($coords as $pt) {
                $newCoords[] = [
                    round($cLat + $factor * ($pt[0] - $cLat), 7),
                    round($cLng + $factor * ($pt[1] - $cLng), 7),
                ];
            }

            $upd = ['coordinates' => $newCoords];
            if ($b->start_lat && $b->start_lng) {
                $upd['start_lat'] = round($cLat + $factor * ($b->start_lat - $cLat), 7);
                $upd['start_lng'] = round($cLng + $factor * ($b->start_lng - $cLng), 7);
            }
            if ($b->label_lat && $b->label_lng) {
                $upd['label_lat'] = round($cLat + $factor * ($b->label_lat - $cLat), 7);
                $upd['label_lng'] = round($cLng + $factor * ($b->label_lng - $cLng), 7);
            }

            $b->update($upd);
        }
    }

    public function down(): void
    {
        $inverse = 1 / 0.72;

        $blocks = SubdivisionBlock::where('block_number', '!=', '3')->get();
        foreach ($blocks as $b) {
            $coords = $b->coordinates;
            if (! is_array($coords) || count($coords) < 3) {
                continue;
            }

            $n = count($coords);
            $lats = array_column($coords, 0);
            $lngs = array_column($coords, 1);
            $cLat = array_sum($lats) / $n;
            $cLng = array_sum($lngs) / $n;

            $newCoords = [];
            foreach ($coords as $pt) {
                $newCoords[] = [
                    round($cLat + $inverse * ($pt[0] - $cLat), 7),
                    round($cLng + $inverse * ($pt[1] - $cLng), 7),
                ];
            }

            $upd = ['coordinates' => $newCoords];
            if ($b->start_lat && $b->start_lng) {
                $upd['start_lat'] = round($cLat + $inverse * ($b->start_lat - $cLat), 7);
                $upd['start_lng'] = round($cLng + $inverse * ($b->start_lng - $cLng), 7);
            }
            if ($b->label_lat && $b->label_lng) {
                $upd['label_lat'] = round($cLat + $inverse * ($b->label_lat - $cLat), 7);
                $upd['label_lng'] = round($cLng + $inverse * ($b->label_lng - $cLng), 7);
            }

            $b->update($upd);
        }
    }
};
