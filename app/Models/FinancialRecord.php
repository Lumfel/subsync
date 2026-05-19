<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $resident_id
 * @property int|null $admin_id
 * @property string $record_type
 * @property string|null $description
 * @property numeric $amount
 * @property \Illuminate\Support\Carbon $record_date
 * @property string $created_at
 * @property-read \App\Models\Admin|null $admin
 * @property-read \App\Models\Resident $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereRecordDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @mixin \Eloquent
 */
class FinancialRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resident_id', 'admin_id', 'record_type', 'description', 'amount', 'record_date',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'record_date' => 'date',
        ];
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
