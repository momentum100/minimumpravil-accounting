<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the buyers (users with role 'buyer') associated with the team.
     */
    public function buyers(): HasMany
    {
        // Assuming 'buyer' is the role name stored in the users table
        return $this->hasMany(User::class)->where('role', 'buyer');
    }

    // If you need *all* users associated with a team, regardless of role
    // (though the current schema only links team_id for buyers implicitly)
    // public function users(): HasMany
    // {
    //     return $this->hasMany(User::class);
    // }
}
