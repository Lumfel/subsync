<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $conversation_id
 * @property int|null $resident_id
 * @property int|null $officer_id
 * @property string $participant_type
 * @property string $created_at
 * @property-read \App\Models\Conversation $conversation
 * @property-read \App\Models\Officer|null $officer
 * @property-read \App\Models\Resident|null $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereParticipantType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class ConvParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = ['conversation_id', 'resident_id', 'officer_id', 'participant_type'];

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
