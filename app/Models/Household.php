<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $block_lot_number
 * @property string $status
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HouseholdMember> $householdMembers
 * @property-read int|null $household_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Resident> $residents
 * @property-read int|null $residents_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereBlockLotNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Household extends Model
{
    protected $fillable = [
        'block_lot_number', 'status', 'latitude', 'longitude',
    ];

    public function residents()
    {
        return $this->hasMany(Resident::class, 'house_id');
    }

    public function householdMembers()
    {
        return $this->hasMany(HouseholdMember::class, 'house_id');
    }
}