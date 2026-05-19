<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $month
 * @property float $previous_balance
 * @property array<array-key, mixed> $collections
 * @property array<array-key, mixed> $expenses
 * @property int|null $admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport wherePreviousBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class FinancialReport extends Model
{
    protected $fillable = [
        'month',
        'previous_balance',
        'collections',
        'expenses',
        'admin_id',
    ];

    protected $casts = [
        'collections'      => 'array',
        'expenses'         => 'array',
        'previous_balance' => 'float',
    ];
}
