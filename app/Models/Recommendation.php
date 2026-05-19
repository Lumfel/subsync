<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $resident_id
 * @property string $title
 * @property string $description
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Resident $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Recommendation extends Model
{
    public $timestamps = false;

    protected $fillable = ['resident_id', 'title', 'description', 'status', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}
