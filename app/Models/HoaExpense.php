<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property float $amount
 * @property string $expense_date
 * @property string $category
 * @property string $status
 * @property string|null $receipt_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HoaExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HoaExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HoaExpense query()
 * @mixin \Eloquent
 */
class HoaExpense extends Model
{
    protected $fillable = [
        'title', 'description', 'amount', 'expense_date', 'category', 'status', 'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}