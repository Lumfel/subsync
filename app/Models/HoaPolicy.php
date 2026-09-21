<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $category
 * @property string $effective_date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HoaPolicy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HoaPolicy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HoaPolicy query()
 * @mixin \Eloquent
 */
class HoaPolicy extends Model
{
    protected $fillable = [
        'title', 'description', 'category', 'effective_date', 'status',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
