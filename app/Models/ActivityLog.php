<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Lightweight, append-only activity trail: who did what, when.
 * Complements the issue-specific audit log with coverage for finance,
 * announcements, documents, templates, and account changes.
 *
 * @property int $id
 * @property string|null $actor_type
 * @property int|null $actor_id
 * @property string|null $actor_name
 * @property string $action
 * @property string $subject_type
 * @property int|null $subject_id
 * @property string $description
 * @property array|null $meta
 * @property Carbon $created_at
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_type', 'actor_id', 'actor_name',
        'action', 'subject_type', 'subject_id',
        'description', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];
}
