<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeRecord extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped automatically.
     * We manage created_at (fetch time) manually or via other means.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'income_date',
        // 'created_at' // Usually managed automatically or during fetch
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'income_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // No relationships defined for this model in the tasks
}
