<?php

namespace App\Observers;

use App\Models\FundTransfer;

class FundTransferObserver
{
    /**
     * Handle the FundTransfer "created" event.
     */
    public function created(FundTransfer $fundTransfer): void
    {
        //
    }

    /**
     * Handle the FundTransfer "updated" event.
     */
    public function updated(FundTransfer $fundTransfer): void
    {
        //
    }

    /**
     * Handle the FundTransfer "deleted" event.
     */
    public function deleted(FundTransfer $fundTransfer): void
    {
        //
    }

    /**
     * Handle the FundTransfer "restored" event.
     */
    public function restored(FundTransfer $fundTransfer): void
    {
        //
    }

    /**
     * Handle the FundTransfer "force deleted" event.
     */
    public function forceDeleted(FundTransfer $fundTransfer): void
    {
        //
    }
}
