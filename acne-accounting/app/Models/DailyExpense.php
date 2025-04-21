<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class DailyExpense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'operation_date',
        'buyer_id',
        'category',
        'quantity',
        'tariff',
        'total',
        'comment',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'operation_date' => 'date',
        'quantity' => 'decimal:2',
        'tariff' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the user who incurred the expense (buyer).
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the user who created the expense record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the transaction associated with the daily expense.
     */
    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'operation');
    }
}
