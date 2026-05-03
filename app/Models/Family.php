<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $fillable = [
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