<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $officer_id
 * @property int|null $issue_id
 * @property string $title
 * @property string $description
 * @property float $amount
 * @property string $expense_date
 * @property string $category
 * @property string $fund
 * @property string|null $receipt_path
 * @property string $status
 * @property string|null $admin_notes
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewed_by
 * @property int|null $hoa_expense_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ExpenseRequest extends Model
{
    protected $fillable = [
        'officer_id',
        'issue_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'category',
        'fund',
        'receipt_path',
        'status',
        'admin_notes',
        'reviewed_at',
        'reviewed_by',
        'hoa_expense_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(IssueReport::class, 'issue_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function hoaExpense(): BelongsTo
    {
        return $this->belongsTo(HoaExpense::class, 'hoa_expense_id');
    }
}
