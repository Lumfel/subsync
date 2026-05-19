<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Household;

/**
 * @property int $id
 * @property int $house_id
 * @property string|null $reason
 * @property string|null $date_flagged
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Household $household
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereDateFlagged($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Delinquent extends Model
{
    protected $fillable = [
        'house_id',
        'reason',
        'date_flagged'
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'house_id');
    }
}