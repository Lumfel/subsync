<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = [
        'house_id',
        'status',
        'reason'
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'house_id');
    }
    
}