<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $resident_id
 * @property string $category
 * @property string $title
 * @property string $description
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Resident $resident
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IssueResponse> $responses
 * @property-read int|null $responses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class IssueReport extends Model
{
    protected $fillable = [
        'resident_id', 'category', 'title', 'description', 'latitude', 'longitude', 'status',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function responses()
    {
        return $this->hasMany(IssueResponse::class, 'issue_id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'entity', 'entity_type', 'entity_id');
    }
}
