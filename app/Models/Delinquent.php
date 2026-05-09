<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Household;

class Delinquent extends Model
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