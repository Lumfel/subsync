<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subdivision_blocks', function (Blueprint $table) {
            $table->string('lot_range', 80)->nullable()->after('color');
            $table->unsignedSmallInteger('total_lots')->default(0)->after('lot_range');
        });

        // Exact lot ranges and counts matching the surveyor blueprint
        $blockLots = [
            '1' => ['lot_range' => 'Lots 1–10', 'total_lots' => 10],
            '2' => ['lot_range' => 'Clubhouse & Pool (Lot 15)', 'total_lots' => 1],
            '3' => ['lot_range' => 'Perimeter Lots 1–74', 'total_lots' => 74],
            '4' => ['lot_range' => 'Lots 1–40', 'total_lots' => 40],
            '5' => ['lot_range' => 'Lots 1–34', 'total_lots' => 34],
            '6' => ['lot_range' => 'Lots 1–28', 'total_lots' => 28],
            '7' => ['lot_range' => 'Lots 1–24', 'total_lots' => 24],
            '8' => ['lot_range' => 'Lots 1–19', 'total_lots' => 19],
            '9' => ['lot_range' => 'Lots 1–20', 'total_lots' => 20],
            '10' => ['lot_range' => 'Lots 1–18', 'total_lots' => 18],
            '11' => ['lot_range' => 'Lots 1–18', 'total_lots' => 18],
            '12' => ['lot_range' => 'Lots 1–22', 'total_lots' => 22],
            '13' => ['lot_range' => 'Lots 1–16', 'total_lots' => 16],
            '14' => ['lot_range' => 'Lots 1–14', 'total_lots' => 14],
        ];

        foreach ($blockLots as $blockNum => $meta) {
            DB::table('subdivision_blocks')
                ->where('block_number', (string) $blockNum)
                ->update([
                    'lot_range' => $meta['lot_range'],
                    'total_lots' => $meta['total_lots'],
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('subdivision_blocks', function (Blueprint $table) {
            $table->dropColumn(['lot_range', 'total_lots']);
        });
    }
};
