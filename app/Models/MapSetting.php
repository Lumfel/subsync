<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property float $min_lat
 * @property float $max_lat
 * @property float $min_lng
 * @property float $max_lng
 * @property float $center_lat
 * @property float $center_lng
 * @property int $zoom
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
 * @mixin \Eloquent
 */
class MapSetting extends Model
{
    protected $fillable = [
        'min_lat', 'max_lat', 'min_lng', 'max_lng', 'center_lat', 'center_lng', 'zoom',
    ];

    protected $casts = [
        'min_lat' => 'float',
        'max_lat' => 'float',
        'min_lng' => 'float',
        'max_lng' => 'float',
        'center_lat' => 'float',
        'center_lng' => 'float',
        'zoom' => 'int',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}