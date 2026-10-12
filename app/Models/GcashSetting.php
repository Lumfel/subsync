<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $account_name
 * @property string|null $account_number
 * @property string|null $instructions
 * @property string|null $qr_image_path
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcashSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcashSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcashSetting query()
 *
 * @mixin \Eloquent
 */
class GcashSetting extends Model
{
    protected $fillable = [
        'account_name', 'account_number', 'instructions', 'qr_image_path', 'updated_by',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
