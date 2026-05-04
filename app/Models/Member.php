<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'user_id',
        'house_id',
        'member_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function household()
    {
        return $this->belongsTo(Household::class, 'house_id');
    }

    
}