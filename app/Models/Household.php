<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    protected $fillable = [
        'location',
        'family_id',
        'members',
        'image'
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function statuses()
    {
        return $this->hasMany(Status::class, 'house_id');
    }

    public function delinquents()
    {
        return $this->hasMany(Delinquent::class, 'house_id');
    }

    public function householdMembers()
    {
        return $this->hasMany(Member::class, 'house_id');
    }
    
}