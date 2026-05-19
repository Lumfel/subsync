<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $conversation_id
 * @property int|null $resident_id
 * @property int|null $officer_id
 * @property string $sender_type
 * @property string $content
 * @property string $created_at
 * @property-read \App\Models\Conversation $conversation
 * @property-read \App\Models\Officer|null $officer
 * @property-read \App\Models\Resident|null $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereSenderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Message extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'conversation_id', 'resident_id', 'officer_id', 'sender_type', 'content', 'created_at',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }
}
