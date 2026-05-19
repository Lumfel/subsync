<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $officer_id
 * @property string $officer_name
 * @property string $original_name
 * @property string $category
 * @property string|null $period
 * @property string|null $notes
 * @property int $size_bytes
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Officer|null $officer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereOfficerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereSizeBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class OfficerFile extends Model
{
    protected $fillable = [
        'officer_id', 'officer_name', 'original_name',
        'category', 'period', 'notes', 'size_bytes', 'file_path',
    ];

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }
}
