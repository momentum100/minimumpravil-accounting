<?php

namespace App\Observers;

use App\Models\DailyExpense;
use App\Models\Account;
use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyExpenseObserver
{
    /**
     * Handle the DailyExpense "created" event.
     */
    public function created(DailyExpense $dailyExpense): void
    {
        // Log entry point
        Log::info('[OBSERVER ENTRY] DailyExpenseObserver@created triggered for ID: ' . $dailyExpense->id);

        // Wrap in try-catch
        try {
            DB::transaction(function () use ($dailyExpense) {
                // 1. Find Buyer's USD Account (Explicitly BUYER_MAIN)
                Log::info('[Observer] Finding buyer account for user ID: ' . $dailyExpense->buyer_id);
                $buyerAccount = Account::where('user_id', $dailyExpense->buyer_id)
                                       ->where('currency', 'USD')
                                       ->where('account_type', 'BUYER_MAIN')
                                       ->firstOrFail();
                Log::info('[Observer] Found buyer account ID: ' . $buyerAccount->id);

                // 2. Find System's USD Account (Explicitly SYSTEM_COMPANY - debit from here)
                Log::info('[Observer] Finding system user...');
                $systemUser = User::where('is_virtual', true)->firstOrFail();
                Log::info('[Observer] Found system user ID: ' . $systemUser->id . '. Finding system account...');
                $systemAccount = Account::where('user_id', $systemUser->id)
                                        ->where('currency', 'USD')
                                        ->where('account_type', 'SYSTEM_COMPANY')
                                        ->firstOrFail();
                Log::info('[Observer] Found system account ID: ' . $systemAccount->id);

                 // 3. Create Transaction linked to the DailyExpense
                 Log::info('[Observer] Creating transaction...');
                 $transaction = $dailyExpense->transaction()->create([
                    'description' => 'Daily Expense: ' . $dailyExpense->category . ' for Buyer #' . $dailyExpense->buyer_id,
                    'transaction_date' => $dailyExpense->operation_date,
                    'status' => 'completed',
                    'accounting_period' => $dailyExpense->operation_date->format('Y-m'),
                ]);
                 Log::info('[Observer] Transaction created with ID: ' . $transaction->id);

                // 4. Create Transaction Lines (Debit SYSTEM, Credit BUYER)
                 Log::info('[Observer] Creating debit line for system account ID: ' . $systemAccount->id);
                 TransactionLine::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $systemAccount->id,
                    'debit' => $dailyExpense->total, // DEBIT System Account
                    'credit' => 0,
                    'description' => 'Expense Paid: ' . $dailyExpense->category . ' for Buyer #' . $dailyExpense->buyer_id,
                ]);

                Log::info('[Observer] Creating credit line for buyer account ID: ' . $buyerAccount->id);
                TransactionLine::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $buyerAccount->id,
                    'debit' => 0,
                    'credit' => $dailyExpense->total, // CREDIT Buyer's Account
                    'description' => 'Expense Received: ' . $dailyExpense->category,
                ]);
                Log::info('[Observer] Transaction lines created.');
            });
            Log::info('[Observer] Transaction committed successfully for DailyExpense ID: ' . $dailyExpense->id);

        } catch (\Throwable $e) { // Catch Throwable for broader error catching
            Log::error('[Observer] Failed to create transaction for DailyExpense ID: ' . $dailyExpense->id . ' - Error: ' . $e->getMessage(), [
                 'exception' => $e
            ]);
            // Decide if you want to re-throw or handle differently
        }
    }

    /**
     * Handle the DailyExpense "updated" event.
     */
    public function updated(DailyExpense $dailyExpense): void
    {
        // Logic for updated event if needed
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
