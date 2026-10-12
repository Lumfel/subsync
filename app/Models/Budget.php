<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A budget line: planned spend for a category (optionally per month and fund).
 */
class Budget extends Model
{
    protected $fillable = ['category', 'fund', 'amount', 'period'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
