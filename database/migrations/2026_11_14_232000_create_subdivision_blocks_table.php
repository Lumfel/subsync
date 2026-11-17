<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subdivision_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('block_number', 20)->unique();
            $table->string('name', 80);
            $table->string('color', 30)->default('#2563eb');
            $table->string('start_label', 50)->nullable()->default('Lot 1');
            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lng', 10, 7)->nullable();
            $table->json('coordinates');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed 14 initial subdivision blocks calibrated to Terra Nova Panaad / Alijis coordinates
        $blocks = [
            [
                'block_number' => '1',
                'name' => 'Block 1',
                'color' => '#0284c7', // Sky Blue
                'start_label' => 'Lot 1',
                'start_lat' => 10.6256,
                'start_lng' => 122.9635,
                'coordinates' => json_encode([
                    [10.6258, 122.9632],
                    [10.6256, 122.9644],
                    [10.6253, 122.9642],
                    [10.6254, 122.9631],
                ]),
                'description' => 'Northern access along Sampaguita St',
            ],
            [
                'block_number' => '2',
                'name' => 'Block 2 (Swimming Pool)',
                'color' => '#8b5cf6', // Purple
                'start_label' => 'Clubhouse Gate',
                'start_lat' => 10.6229,
                'start_lng' => 122.9625,
                'coordinates' => json_encode([
                    [10.6231, 122.9624],
                    [10.6231, 122.9637],
                    [10.6223, 122.9636],
                    [10.6223, 122.9623],
                ]),
                'description' => 'Community Open Space, Clubhouse & Swimming Pool',
            ],
            [
                'block_number' => '3',
                'name' => 'Block 3 (Perimeter)',
                'color' => '#ef4444', // Red
                'start_label' => 'Lot 1 (Main Gate)',
                'start_lat' => 10.6200,
                'start_lng' => 122.9587,
                'coordinates' => json_encode([
                    [10.6198, 122.9585],
                    [10.6256, 122.9585],
                    [10.6258, 122.9645],
                    [10.6208, 122.9646],
                    [10.6198, 122.9628],
                ]),
                'description' => 'Outer Perimeter Boundary Lots 1 to 74',
            ],
            [
                'block_number' => '4',
                'name' => 'Block 4',
                'color' => '#f97316', // Orange
                'start_label' => 'Lot 1',
                'start_lat' => 10.6251,
                'start_lng' => 122.9638,
                'coordinates' => json_encode([
                    [10.6252, 122.9610],
                    [10.6251, 122.9638],
                    [10.6247, 122.9637],
                    [10.6248, 122.9608],
                ]),
                'description' => 'Top curved residential strip',
            ],
            [
                'block_number' => '5',
                'name' => 'Block 5',
                'color' => '#a855f7', // Plum/Violet
                'start_label' => 'Lot 1',
                'start_lat' => 10.6245,
                'start_lng' => 122.9638,
                'coordinates' => json_encode([
                    [10.6246, 122.9605],
                    [10.6245, 122.9639],
                    [10.6240, 122.9638],
                    [10.6241, 122.9604],
                ]),
                'description' => 'Second curved residential strip',
            ],
            [
                'block_number' => '6',
                'name' => 'Block 6',
                'color' => '#16a34a', // Emerald Green
                'start_label' => 'Lot 1',
                'start_lat' => 10.6239,
                'start_lng' => 122.9637,
                'coordinates' => json_encode([
                    [10.6240, 122.9602],
                    [10.6239, 122.9638],
                    [10.6234, 122.9636],
                    [10.6235, 122.9600],
                ]),
                'description' => 'Third curved residential strip',
            ],
            [
                'block_number' => '7',
                'name' => 'Block 7',
                'color' => '#d97706', // Amber / Brown
                'start_label' => 'Lot 1',
                'start_lat' => 10.6233,
                'start_lng' => 122.9635,
                'coordinates' => json_encode([
                    [10.6234, 122.9604],
                    [10.6233, 122.9636],
                    [10.6228, 122.9634],
                    [10.6229, 122.9603],
                ]),
                'description' => 'Fourth curved strip directly north of pool',
            ],
            [
                'block_number' => '8',
                'name' => 'Block 8',
                'color' => '#84cc16', // Lime Green
                'start_label' => 'Lot 1',
                'start_lat' => 10.6253,
                'start_lng' => 122.9592,
                'coordinates' => json_encode([
                    [10.6254, 122.9590],
                    [10.6253, 122.9602],
                    [10.6247, 122.9604],
                    [10.6248, 122.9591],
                ]),
                'description' => 'North-west entrance block',
            ],
            [
                'block_number' => '9',
                'name' => 'Block 9',
                'color' => '#f43f5e', // Rose Red
                'start_label' => 'Lot 1',
                'start_lat' => 10.6245,
                'start_lng' => 122.9589,
                'coordinates' => json_encode([
                    [10.6246, 122.9588],
                    [10.6246, 122.9593],
                    [10.6215, 122.9591],
                    [10.6215, 122.9587],
                ]),
                'description' => 'West outer vertical residential strip',
            ],
            [
                'block_number' => '10',
                'name' => 'Block 10',
                'color' => '#ec4899', // Magenta / Pink
                'start_label' => 'Lot 1',
                'start_lat' => 10.6245,
                'start_lng' => 122.9596,
                'coordinates' => json_encode([
                    [10.6246, 122.9594],
                    [10.6246, 122.9599],
                    [10.6215, 122.9597],
                    [10.6215, 122.9593],
                ]),
                'description' => 'West inner vertical residential strip',
            ],
            [
                'block_number' => '11',
                'name' => 'Block 11',
                'color' => '#6366f1', // Indigo / Purple
                'start_label' => 'Lot 1',
                'start_lat' => 10.6202,
                'start_lng' => 122.9597,
                'coordinates' => json_encode([
                    [10.6206, 122.9596],
                    [10.6206, 122.9634],
                    [10.6200, 122.9632],
                    [10.6200, 122.9597],
                ]),
                'description' => 'Southern strip (includes Lot 18)',
            ],
            [
                'block_number' => '12',
                'name' => 'Block 12',
                'color' => '#fb7185', // Pink / Ruby
                'start_label' => 'Lot 1',
                'start_lat' => 10.6212,
                'start_lng' => 122.9598,
                'coordinates' => json_encode([
                    [10.6214, 122.9598],
                    [10.6213, 122.9632],
                    [10.6208, 122.9631],
                    [10.6208, 122.9598],
                ]),
                'description' => 'Horizontal strip directly above Block 11',
            ],
            [
                'block_number' => '13',
                'name' => 'Block 13',
                'color' => '#eab308', // Gold / Yellow
                'start_label' => 'Lot 1',
                'start_lat' => 10.6226,
                'start_lng' => 122.9606,
                'coordinates' => json_encode([
                    [10.6227, 122.9604],
                    [10.6222, 122.9616],
                    [10.6215, 122.9614],
                    [10.6220, 122.9603],
                ]),
                'description' => 'Curved strip adjacent to Block 14',
            ],
            [
                'block_number' => '14',
                'name' => 'Block 14',
                'color' => '#3b82f6', // Blue
                'start_label' => 'Lot 1',
                'start_lat' => 10.6223,
                'start_lng' => 122.9620,
                'coordinates' => json_encode([
                    [10.6223, 122.9617],
                    [10.6222, 122.9626],
                    [10.6215, 122.9624],
                    [10.6216, 122.9616],
                ]),
                'description' => 'Curved strip directly below the pool',
            ],
        ];

        foreach ($blocks as $b) {
            DB::table('subdivision_blocks')->insert(array_merge($b, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subdivision_blocks');
    }
};
