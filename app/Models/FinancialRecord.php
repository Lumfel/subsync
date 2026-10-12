<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $resident_id
 * @property int|null $admin_id
 * @property string $record_type
 * @property string|null $description
 * @property numeric $amount
 * @property Carbon $record_date
 * @property string $created_at
 * @property-read Admin|null $admin
 * @property-read Resident $resident
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord query()
 *
 * @mixin \Eloquent
 */
class FinancialRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resident_id', 'admin_id', 'record_type', 'description', 'amount', 'record_date',
        'assessment_id', 'period', 'due_date', 'source',
        'gcash_transaction_id', 'gcash_reference', 'payment_gateway',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'record_date' => 'date',
            'due_date' => 'date',
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

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    /** Charges increase the balance; payments/credits reduce it. */
    public function getSignedAmountAttribute(): float
    {
        $amount = (float) $this->amount;

        return in_array($this->record_type, ['Payment', 'Credit'], true) ? -$amount : $amount;
    }
}
