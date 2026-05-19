<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $admin_id
 * @property int|null $officer_id
 * @property string $title
 * @property string $content
 * @property string $tag
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $admin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Officer|null $officer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnnouncementView> $views
 * @property-read int|null $views_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Announcement extends Model
{
    protected $fillable = ['admin_id', 'officer_id', 'title', 'content', 'tag'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function views()
    {
        return $this->hasMany(AnnouncementView::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'entity', 'entity_type', 'entity_id');
    }
}
