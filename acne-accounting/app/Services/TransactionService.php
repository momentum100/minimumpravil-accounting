<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionService
{
    public function recordOperationTransaction(
        Model $operation,
        int $debitAccountId,
        float $debitAmount,
        int $creditAccountId,
        float $creditAmount,
        ?string $debitDescription = null,
        ?string $creditDescription = null,
        ?string $mainDescription = null
    ): ?Transaction
    {
        // 1. Start DB transaction.
        DB::beginTransaction();
        try {
            // 2. Create Transaction model linked to the $operation (morphTo).
            $transaction = $operation->transaction()->create([
                'description' => $mainDescription ?? 'Transaction for ' . class_basename($operation) . ' #' . $operation->id,
                // Determine transaction_date, accounting_period from $operation if possible, or use now()
                'transaction_date' => $operation->transfer_date ?? $operation->expense_date ?? $operation->adjustment_date ?? now(),
                'status' => 'completed', // Assuming completion, might need adjustment based on operation
                'accounting_period' => ($operation->transfer_date ?? $operation->expense_date ?? $operation->adjustment_date ?? now())->format('Y-m'),
            ]);
            Log::info('Transaction record created via Service', ['transaction_id' => $transaction->id, 'operation_type' => get_class($operation), 'operation_id' => $operation->id]);

            // 3. Create debit TransactionLine.
            TransactionLine::create([
                'transaction_id' => $transaction->id,
                'account_id' => $debitAccountId,
                'debit' => $debitAmount,
                'credit' => 0,
                'description' => $debitDescription ?? 'Debit',
            ]);
            Log::info('Debit Transaction Line created via Service', ['transaction_id' => $transaction->id, 'account_id' => $debitAccountId, 'amount' => $debitAmount]);

            // 4. Create credit TransactionLine.
            TransactionLine::create([
                'transaction_id' => $transaction->id,
                'account_id' => $creditAccountId,
                'debit' => 0,
                'credit' => $creditAmount,
                'description' => $creditDescription ?? 'Credit',
            ]);
             Log::info('Credit Transaction Line created via Service', ['transaction_id' => $transaction->id, 'account_id' => $creditAccountId, 'amount' => $creditAmount]);


            // 5. Commit DB transaction.
            DB::commit();
            Log::info('Transaction committed via Service', ['transaction_id' => $transaction->id]);

            // 6. Return created Transaction
            return $transaction;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('TransactionService::recordOperationTransaction failed', [
                'operation_type' => get_class($operation),
                'operation_id' => $operation->id,
                'error' => $e->getMessage(),
                'exception' => $e
            ]);
            // Re-throw the exception to be handled by the calling controller
            throw $e;
        }
    }
}