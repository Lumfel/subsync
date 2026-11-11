<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $resident_id
 * @property string $category
 * @property string $title
 * @property string $description
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Resident $resident
 * @property-read Collection<int, IssueResponse> $responses
 * @property-read int|null $responses_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 *
 * @mixin \Eloquent
 */
class IssueReport extends Model
{
    protected $fillable = [
        'resident_id', 'category', 'title', 'description', 'latitude', 'longitude', 'status', 'priority', 'resolved_at', 'archived_at', 'assigned_officer_id', 'due_date',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'archived_at' => 'datetime',
        'due_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($issue) {
            if (! $issue->priority) {
                $issue->priority = $issue->autoSetPriority();
            }
        });
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function assignee()
    {
        return $this->belongsTo(Officer::class, 'assigned_officer_id');
    }

    /** True when the SLA due date has passed and the issue is still open. */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->status !== 'Resolved'
            && $this->due_date->isPast();
    }

    public function responses()
    {
        return $this->hasMany(IssueResponse::class, 'issue_id');
    }

    public function hoaExpenses(): HasMany
    {
        return $this->hasMany(HoaExpense::class, 'issue_id');
    }

    public function expenseRequests(): HasMany
    {
        return $this->hasMany(ExpenseRequest::class, 'issue_id');
    }

    /** Total verified repair expenses recorded against this issue */
    public function getTotalRepairCostAttribute(): float
    {
        return (float) $this->hoaExpenses()->sum('amount');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'entity', 'entity_type', 'entity_id');
    }

    /**
     * Automatically set priority based on issue category
     */
    public function autoSetPriority()
    {
        $categoryPriorityMap = [
            // Resident-facing categories (see the resident issue form)
            'Emergency' => 'Critical',
            'Security Concern' => 'Critical',
            'Drainage / Flooding' => 'High',
            'Road / Pavement' => 'High',
            'Utilities' => 'High',
            'Street Lighting' => 'Medium',
            'Garbage / Sanitation' => 'Medium',
            'Noise Complaint' => 'Low',
            'Other' => 'Low',
            // Legacy / alternate category names
            'Safety' => 'Critical',
            'Security' => 'Critical',
            'Infrastructure' => 'High',
            'Sanitation' => 'Medium',
            'Maintenance' => 'Medium',
            'Noise' => 'Medium',
            'Parking' => 'Medium',
            'Landscaping' => 'Low',
            'Pet Issues' => 'Low',
            'Administrative' => 'Low',
            // Default fallback
            'default' => 'Low',
        ];

        return $categoryPriorityMap[$this->category] ?? $categoryPriorityMap['default'];
    }
}
