<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $resident_id
 * @property int $announcement_id
 * @property string $created_at
 * @property-read \App\Models\Announcement $announcement
 * @property-read \App\Models\Resident $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView whereAnnouncementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class AnnouncementView extends Model
{
    public $timestamps = false;

    protected $fillable = ['resident_id', 'announcement_id'];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
