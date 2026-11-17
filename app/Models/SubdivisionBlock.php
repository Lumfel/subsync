<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubdivisionBlock extends Model
{
    protected $fillable = [
        'block_number',
        'name',
        'color',
        'lot_range',
        'total_lots',
        'start_label',
        'start_lat',
        'start_lng',
        'label_lat',
        'label_lng',
        'coordinates',
        'description',
    ];

    protected $casts = [
        'total_lots' => 'integer',
        'start_lat' => 'float',
        'start_lng' => 'float',
        'label_lat' => 'float',
        'label_lng' => 'float',
        'coordinates' => 'array',
    ];

    /**
     * Parse a raw block_lot string (e.g. "Blk 11 Lot 18", "Block 4 Lot 2", "blk 6 lot 5")
     * into structured [block, lot].
     *
     * @return array{block: string|null, lot: string|null, display: string}
     */
    public static function parseBlockLot(?string $raw): array
    {
        if (! $raw) {
            return ['block' => null, 'lot' => null, 'display' => '—'];
        }

        $trimmed = trim($raw);
        $block = null;
        $lot = null;

        if (preg_match('/(?:blk|block)\s*(\d+)/i', $trimmed, $m)) {
            $block = $m[1];
        }
        if (preg_match('/(?:lot|lt)\s*(\d+)/i', $trimmed, $m)) {
            $lot = $m[1];
        }

        if ($block && $lot) {
            $display = "Block {$block} Lot {$lot}";
        } elseif ($block) {
            $display = "Block {$block}";
        } elseif ($lot) {
            $display = "Lot {$lot}";
        } else {
            $display = $trimmed;
        }

        return [
            'block' => $block,
            'lot' => $lot,
            'display' => $display,
        ];
    }
}
