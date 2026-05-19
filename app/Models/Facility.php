<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $admin_id
 * @property string $name
 * @property string|null $description
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string $status
 * @property string $created_at
 * @property-read \App\Models\Admin|null $admin
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Facility extends Model
{
    public $timestamps = false;

    protected $fillable = ['admin_id', 'name', 'description', 'latitude', 'longitude', 'status'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
