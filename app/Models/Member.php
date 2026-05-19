<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Family|null $family
 * @property-read \App\Models\Household|null $household
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Member extends Model
{
    protected $fillable = [
        'user_id',
        'house_id',
        'member_type',
        'date_added'
    ];

  public function user()
{
    return $this->belongsTo(User::class);
}

public function household()
{
    return $this->belongsTo(Household::class, 'house_id', 'id');
}

    
public function family()
{
    return $this->belongsTo(Family::class, 'family_id');
}
}