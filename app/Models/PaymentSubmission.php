<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $resident_id
 * @property string $amount
 * @property string $method
 * @property string|null $reference_no
 * @property string|null $gcash_sender_name
 * @property string|null $gcash_number
 * @property Carbon|null $paid_at
 * @property string|null $receipt_path
 * @property string $status
 * @property string|null $admin_response
 * @property string|null $rejection_reason
 * @property string|null $official_receipt_path
 * @property Carbon|null $acknowledged_at
 * @property int|null $validated_by
 * @property Carbon|null $validated_at
 * @property int|null $financial_record_id
 *
 * @mixin \Eloquent
 */
class PaymentSubmission extends Model
{
    protected $fillable = [
        'resident_id', 'amount', 'method', 'reference_no', 'gcash_sender_name', 'gcash_number',
        'paid_at', 'receipt_path', 'status', 'admin_response', 'rejection_reason',
        'official_receipt_path', 'acknowledged_at', 'validated_by', 'validated_at', 'financial_record_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
        'acknowledged_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}
