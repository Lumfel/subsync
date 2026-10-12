<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $family_name
 * @property string|null $family_head
 * @property string|null $members
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Household> $households
 * @property-read int|null $households_count
 *
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
 *
 * @mixin \Eloquent
 */
class Family extends Model
{
    protected $fillable = [
        'family_name',
        'family_head',
        'members',
    ];

    public function households()
    {
        return $this->hasMany(Household::class);
    }
}
