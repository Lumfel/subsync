<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-user preferences for appearance and notifications.
 * One row per (user_type, user_id). Defaults come from the migration.
 */
class UserSetting extends Model
{
    protected $fillable = [
        'user_type', 'user_id',
        'theme', 'density', 'font_scale',
        'notify_announcements', 'notify_payments', 'notify_issues', 'notify_messages', 'email_notifications',
    ];

    protected function casts(): array
    {
        return [
            'notify_announcements' => 'boolean',
            'notify_payments' => 'boolean',
            'notify_issues' => 'boolean',
            'notify_messages' => 'boolean',
            'email_notifications' => 'boolean',
        ];
    }

    /** Resolve (or lazily create) the settings row for an actor. */
    public static function resolve(?string $type, ?int $id): self
    {
        if (! $type || ! $id) {
            return new self;
        }

        return static::firstOrCreate([
            'user_type' => $type,
            'user_id' => $id,
        ]);
    }

    /** Shape used by the front-end and the layouts. */
    public function toPrefs(): array
    {
        return [
            'theme' => $this->theme ?: 'light',
            'density' => $this->density ?: 'comfortable',
            'font_scale' => $this->font_scale ?: 'default',
            'notify_announcements' => (bool) ($this->notify_announcements ?? true),
            'notify_payments' => (bool) ($this->notify_payments ?? true),
            'notify_issues' => (bool) ($this->notify_issues ?? true),
            'notify_messages' => (bool) ($this->notify_messages ?? true),
            'email_notifications' => (bool) ($this->email_notifications ?? false),
        ];
    }
}
