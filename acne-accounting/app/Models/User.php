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
        'email',          // Nullable based on role
        'password',       // Nullable based on role
        'is_virtual',
        'telegram_id',    // Corrected name
        'terms',          // Added
        'role',           // Added
        'team_id',        // Added, Nullable
        'sub2',           // Added (for tags), Nullable
        'contact_info',   // Added, Nullable
        'active',         // Added
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_virtual' => 'boolean',
            'terms' => 'float',           // Keep
            'sub2' => 'array',           // Added (existing field)
            'active' => 'boolean',         // Added (existing field)
        ];
    }

    /**
     * Get the team that the user belongs to (primarily for buyers).
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
     * Get the daily expenses associated with the user (creator).
     */
    public function dailyExpenses(): HasMany
    {
        return $this->hasMany(DailyExpense::class, 'created_by');
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
