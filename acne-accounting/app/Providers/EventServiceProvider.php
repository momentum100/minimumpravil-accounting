<?php

namespace App\Providers;

// Add Model and Observer imports
use App\Models\DailyExpense;
use App\Observers\DailyExpenseObserver;
use App\Models\Adjustment;
use App\Observers\AdjustmentObserver;
use App\Models\FundTransfer;
use App\Observers\FundTransferObserver;

// Keep existing imports
use Illuminate\Support\ServiceProvider;
// Depending on Laravel version, you might need Foundation Support Provider
// use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider // Adjust base class if needed based on imports
{
    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        DailyExpense::class => [DailyExpenseObserver::class],
        Adjustment::class => [AdjustmentObserver::class],
        FundTransfer::class => [FundTransferObserver::class],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Observers are often registered automatically via the $observers property.
        // If using an older Laravel version or explicit registration is preferred:
        // DailyExpense::observe(DailyExpenseObserver::class);
        // Adjustment::observe(AdjustmentObserver::class);
        // FundTransfer::observe(FundTransferObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // Or true if using event discovery
    }
}
