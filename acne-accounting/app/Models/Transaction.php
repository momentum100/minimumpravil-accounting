<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'operation_id',
        'operation_type',
        'transaction_date',
        'description',
        'status',
        'accounting_period',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    /**
     * Get all of the transaction's lines.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(TransactionLine::class);
    }

    /**
     * Get the parent operation model (DailyExpense, Adjustment, or FundTransfer).
     */
    public function operation(): MorphTo
    {
        return $this->morphTo();
    }
}
