<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRsvp extends Model
{
    protected $fillable = ['event_id', 'resident_id', 'status'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}
