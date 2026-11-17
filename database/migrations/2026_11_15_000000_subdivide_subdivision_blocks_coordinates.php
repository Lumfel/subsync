<?php

use App\Models\SubdivisionBlock;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $blocks = SubdivisionBlock::all();

        foreach ($blocks as $block) {
            $coords = $block->coordinates;
            if (! is_array($coords) || count($coords) < 3) {
                continue;
            }

            $newCoords = self::subdividePolygon($coords);
            $block->update(['coordinates' => $newCoords]);
        }
    }

    public function down(): void
    {
        // Rollback is safe: coordinates are preserved as valid polygons
    }

    public static function subdividePolygon(array $pts): array
    {
        $n = count($pts);
        $result = [];

        for ($i = 0; $i < $n; $i++) {
            $p1 = $pts[$i];
            $p2 = $pts[($i + 1) % $n];
            $result[] = $p1;

            $d = sqrt(pow($p1[0] - $p2[0], 2) + pow($p1[1] - $p2[1], 2)) * 111000;
            $steps = 1;
            if ($d >= 150) {
                $steps = 5;
            } elseif ($d >= 90) {
                $steps = 4;
            } elseif ($d >= 50) {
                $steps = 3;
            } elseif ($d >= 25) {
                $steps = 2;
            }

            for ($s = 1; $s < $steps; $s++) {
                $t = $s / $steps;
                $result[] = [
                    round($p1[0] + $t * ($p2[0] - $p1[0]), 7),
                    round($p1[1] + $t * ($p2[1] - $p1[1]), 7),
                ];
            }
        }

        return $result;
    }
};
