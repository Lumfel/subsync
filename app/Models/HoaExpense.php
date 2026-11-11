<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property float $amount
 * @property string $expense_date
 * @property string $category
 * @property string $status
 * @property string|null $receipt_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HoaExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HoaExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HoaExpense query()
 *
 * @mixin \Eloquent
 */
class HoaExpense extends Model
{
    protected $fillable = [
        'title', 'description', 'amount', 'expense_date', 'category', 'status', 'fund', 'receipt_path',
        'issue_id', 'expense_request_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(IssueReport::class, 'issue_id');
    }

    public function expenseRequest(): BelongsTo
    {
        return $this->belongsTo(ExpenseRequest::class, 'expense_request_id');
    }
}
