<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $family_name
 * @property string|null $family_head
 * @property string|null $members
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $head
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Household> $households
 * @property-read int|null $households_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereFamilyHead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereFamilyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Family extends Model
{
    protected $fillable = [
    'family_name',
    'family_head',
    'members'
];

    public function head()
    {
        return $this->belongsTo(User::class, 'family_head');
    }

    public function households()
    {
        return $this->hasMany(Household::class);
    }
}