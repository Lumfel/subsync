<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $issue_id
 * @property string|null $actor_type
 * @property int|null $actor_id
 * @property string $action
 * @property string|null $old_value
 * @property string|null $new_value
 * @property Carbon $created_at
 *
 * @mixin \Eloquent
 */
class IssueAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'issue_id', 'actor_type', 'actor_id', 'action', 'old_value', 'new_value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function issue()
    {
        return $this->belongsTo(IssueReport::class, 'issue_id');
    }
}
