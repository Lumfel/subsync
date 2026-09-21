<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $resident_id
 * @property string $title
 * @property string $description
 * @property float $amount
 * @property string $expense_date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseRequest query()
 * @mixin \Eloquent
 */
class ExpenseRequest extends Model
{
    protected $fillable = [
        'resident_id', 'title', 'description', 'amount', 'expense_date', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}