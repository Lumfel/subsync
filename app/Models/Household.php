<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $block_lot_number
 * @property string $status
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, HouseholdMember> $householdMembers
 * @property-read int|null $household_members_count
 * @property-read Collection<int, Resident> $residents
 * @property-read int|null $residents_count
 *
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
 *
 * @mixin \Eloquent
 */
class Household extends Model
{
    protected $fillable = [
        'block_lot_number', 'family_id', 'status', 'latitude', 'longitude',
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class, 'house_id');
    }

    public function householdMembers()
    {
        return $this->hasMany(HouseholdMember::class, 'house_id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'entity', 'entity_type', 'entity_id');
    }

    /**
     * Find a household by block/lot (case-insensitive, whitespace-normalized) or create it.
     * Prevents duplicate households from free-text input (e.g. "Block 3 Lot 12" vs "block 3  lot 12").
     */
    public static function findOrCreateByBlockLot(string $value): self
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value));

        $existing = static::whereRaw('LOWER(block_lot_number) = ?', [mb_strtolower($normalized)])->first();

        return $existing ?: static::create(['block_lot_number' => $normalized]);
    }
}
