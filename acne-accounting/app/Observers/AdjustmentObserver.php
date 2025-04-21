<?php

namespace App\Observers;

use App\Models\Adjustment;

class AdjustmentObserver
{
    /**
     * Handle the Adjustment "created" event.
     */
    public function created(Adjustment $adjustment): void
    {
        //
    }

    /**
     * Handle the Adjustment "updated" event.
     */
    public function updated(Adjustment $adjustment): void
    {
        //
    }

    /**
     * Handle the Adjustment "deleted" event.
     */
    public function deleted(Adjustment $adjustment): void
    {
        //
    }

    /**
     * Handle the Adjustment "restored" event.
     */
    public function restored(Adjustment $adjustment): void
    {
        //
    }

    /**
     * Handle the Adjustment "force deleted" event.
     */
    public function forceDeleted(Adjustment $adjustment): void
    {
        //
    }
}
