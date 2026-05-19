<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property int|null $house_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $contact_number
 * @property string $status
 * @property numeric $current_balance
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnnouncementView> $announcementViews
 * @property-read int|null $announcement_views_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $conversations
 * @property-read int|null $conversations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FinancialRecord> $financialRecords
 * @property-read int|null $financial_records_count
 * @property-read \App\Models\Household|null $household
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IssueReport> $issueReports
 * @property-read int|null $issue_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recommendation> $recommendations
 * @property-read int|null $recommendations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class Resident extends Authenticatable
{
    protected $fillable = [
        'house_id', 'name', 'email', 'password', 'contact_number', 'status', 'current_balance',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'        => 'hashed',
            'current_balance' => 'decimal:2',
        ];
    }

    public function household()
    {
        return $this->belongsTo(Household::class, 'house_id');
    }

    public function financialRecords()
    {
        return $this->hasMany(FinancialRecord::class);
    }

    public function issueReports()
    {
        return $this->hasMany(IssueReport::class);
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }

    public function announcementViews()
    {
        return $this->hasMany(AnnouncementView::class);
    }

    public function conversations()
    {
        return $this->hasManyThrough(Conversation::class, ConvParticipant::class, 'resident_id', 'id', 'id', 'conversation_id');
    }
}
