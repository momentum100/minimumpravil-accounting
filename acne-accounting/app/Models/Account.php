<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'account_type',
        'description',
        'currency',
    ];

    /**
     * Get the user that owns the account.
     */
    public function user(): BelongsTo
    {
        // Note: user_id is nullable in the migration
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction lines for the account.
     */
    public function transactionLines(): HasMany
    {
        return $this->hasMany(TransactionLine::class);
    }

    // TODO: Implement getBalance() method or accessor as per Task 3.3
    // This will likely involve summing transactionLines debit/credit
}
