<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A recurring assessment (dues) that generates charges on residents' ledgers.
 */
class Assessment extends Model
{
    protected $fillable = [
        'name', 'amount', 'frequency', 'due_day', 'start_date', 'end_date', 'target', 'is_active', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_day' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function records()
    {
        return $this->hasMany(FinancialRecord::class);
    }
}
