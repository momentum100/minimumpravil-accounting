<?php

namespace App\Observers;

use App\Models\DailyExpense;

class DailyExpenseObserver
{
    /**
     * Handle the DailyExpense "created" event.
     */
    public function created(DailyExpense $dailyExpense): void
    {
        //
    }

    /**
     * Handle the DailyExpense "updated" event.
     */
    public function updated(DailyExpense $dailyExpense): void
    {
        //
    }

    /**
     * Handle the DailyExpense "deleted" event.
     */
    public function deleted(DailyExpense $dailyExpense): void
    {
        //
    }

    /**
     * Handle the DailyExpense "restored" event.
     */
    public function restored(DailyExpense $dailyExpense): void
    {
        //
    }

    /**
     * Handle the DailyExpense "force deleted" event.
     */
    public function forceDeleted(DailyExpense $dailyExpense): void
    {
        //
    }
}
