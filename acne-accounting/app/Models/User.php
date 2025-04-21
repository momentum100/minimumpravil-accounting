<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telegram_id',
        'role',
        'team_id',
        'sub2',
        'active',
        'is_virtual',
        'contact_info',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|object>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'sub2' => 'array', // Cast sub2 JSON to array
            'active' => 'boolean',
            'is_virtual' => 'boolean',
        ];
    }

    /**
     * Get the team that the user belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the accounts associated with the user.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get the daily expenses created by or for the user.
     * Note: Differentiate by foreign key if needed (e.g., dailyExpensesAsBuyer, dailyExpensesAsCreator)
     */
    public function dailyExpenses(): HasMany
    {
        // This relation assumes user_id might link to either buyer or creator in some contexts,
        // or more specific relations are needed depending on usage.
        // For now, linking via 'created_by' as an example.
        // Or use separate relations like buyerDailyExpenses() and creatorDailyExpenses()
        return $this->hasMany(DailyExpense::class, 'created_by'); // Or 'buyer_id' depending on primary usage
    }

    /**
     * Get the adjustments created by or for the user.
     * Note: Similar to dailyExpenses, clarify relation if needed.
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(Adjustment::class, 'created_by'); // Or 'buyer_id'
    }
}
