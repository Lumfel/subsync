<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $uploader_id
 * @property string $uploader_type
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property string $file_name
 * @property string $file_path
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUploaderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUploaderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Media extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uploader_id', 'uploader_type', 'entity_type', 'entity_id', 'file_name', 'file_path',
    ];
}
