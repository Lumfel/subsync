<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $house_id
 * @property string $name
 * @property string|null $relationship
 * @property string|null $contact_number
 * @property string $created_at
 * @property-read \App\Models\Household $household
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class HouseholdMember extends Model
{
    public $timestamps = false;

    protected $fillable = ['house_id', 'name', 'relationship', 'contact_number'];

    public function household()
    {
        return $this->belongsTo(Household::class, 'house_id');
    }
}
