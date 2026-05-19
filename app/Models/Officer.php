<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $contact_number
 * @property string $status
 * @property string|null $role_description
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Announcement> $announcements
 * @property-read int|null $announcements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $conversations
 * @property-read int|null $conversations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IssueResponse> $issueResponses
 * @property-read int|null $issue_responses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereRoleDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Officer extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'contact_number', 'status', 'role_description',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function issueResponses()
    {
        return $this->hasMany(IssueResponse::class);
    }

    public function conversations()
    {
        return $this->hasManyThrough(Conversation::class, ConvParticipant::class, 'officer_id', 'id', 'id', 'conversation_id');
    }
}
