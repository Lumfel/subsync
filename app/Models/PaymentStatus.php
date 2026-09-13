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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereDateFlagged($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class PaymentStatus extends Model
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