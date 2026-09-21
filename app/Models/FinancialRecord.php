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
            'amount' => 'decimal:2',
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