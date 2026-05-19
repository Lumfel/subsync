<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $issue_id
 * @property int|null $officer_id
 * @property int|null $admin_id
 * @property string $responder_type
 * @property string $response_content
 * @property string $created_at
 * @property-read \App\Models\Admin|null $admin
 * @property-read \App\Models\IssueReport $issue
 * @property-read \App\Models\Officer|null $officer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereIssueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereResponderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereResponseContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class IssueResponse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'issue_id', 'officer_id', 'admin_id', 'responder_type', 'response_content',
    ];

    public function issue()
    {
        return $this->belongsTo(IssueReport::class, 'issue_id');
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
