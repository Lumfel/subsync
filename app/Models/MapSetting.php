<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property float $min_lat
 * @property float $max_lat
 * @property float $min_lng
 * @property float $max_lng
 * @property float $center_lat
 * @property float $center_lng
 * @property int $zoom
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting whereCenterLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting whereCenterLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting whereMaxLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting whereMaxLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting whereMinLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting whereMinLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MapSetting whereZoom($value)
 *
 * @mixin \Eloquent
 */
class MapSetting extends Model
{
    protected $fillable = [
        'min_lat', 'max_lat', 'min_lng', 'max_lng', 'center_lat', 'center_lng', 'zoom',
        'overlay_url', 'overlay_min_lat', 'overlay_max_lat', 'overlay_min_lng', 'overlay_max_lng',
        'overlay_opacity', 'overlay_rotation', 'overlay_enabled',
    ];

    protected $casts = [
        'min_lat' => 'float',
        'max_lat' => 'float',
        'min_lng' => 'float',
        'max_lng' => 'float',
        'center_lat' => 'float',
        'center_lng' => 'float',
        'zoom' => 'int',
        'overlay_min_lat' => 'float',
        'overlay_max_lat' => 'float',
        'overlay_min_lng' => 'float',
        'overlay_max_lng' => 'float',
        'overlay_opacity' => 'float',
        'overlay_rotation' => 'float',
        'overlay_enabled' => 'bool',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
