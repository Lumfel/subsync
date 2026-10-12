<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'location', 'starts_at', 'ends_at', 'created_by_type', 'created_by_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function rsvps()
    {
        return $this->hasMany(EventRsvp::class);
    }
}
