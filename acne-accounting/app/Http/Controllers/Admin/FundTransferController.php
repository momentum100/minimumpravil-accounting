<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Services\FundTransferService; // We'll create this later
use App\Http\Requests\Admin\StoreFundTransferRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\FundTransfer;
use App\Models\TransactionLine;

class FundTransferController extends Controller
{
    /**
     * Show the form for creating a new fund transfer.
     */
    public function create(): View
    {
        // Get the System user
        $systemUser = User::where('is_virtual', true)->first(); // Assuming only one virtual/system user
        if (!$systemUser) {
            // Handle case where system user doesn't exist, maybe throw an error or redirect
            abort(500, 'System user not found.'); 
        }

        // Get non-virtual users suitable for transfers (for the 'To' dropdown)
        $toUsers = User::where('is_virtual', false)->orderBy('name')->get();

        // Create the list for the 'From' dropdown: System user + non-virtual users
        $fromUsers = $toUsers->prepend($systemUser);
        
        return view('admin.fund-transfers.create', compact('fromUsers', 'toUsers'));
    }

    /**
     * Store a newly created fund transfer in storage.
     *
     * @param StoreFundTransferRequest $request
     * @param FundTransferService $fundTransferService // Inject service later
     * @return RedirectResponse
     */
    public function store(StoreFundTransferRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $fromAccount = Account::findOrFail($validated['from_account_id']);
            $toAccount = Account::findOrFail($validated['to_account_id']);
            $amount = $validated['amount'];
            $description = $validated['description'];

            // Basic checks
            if ($fromAccount->currency !== 'USD' || $toAccount->currency !== 'USD') {
                throw new \Exception("Only USD transfers are currently supported.");
            }
            if ($fromAccount->id === $toAccount->id) {
                throw new \Exception("Cannot transfer to the same account.");
            }

            // 1. Create FundTransfer record (without transaction_id initially)
            $fundTransfer = FundTransfer::create([
                // 'transaction_id' => null, // Set later
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amount,
                'currency' => 'USD', // Assuming USD - Note: The DB migration doesn't have currency field, should it?
                'transfer_date' => now(), // Or use a specific date if needed
                'comment' => $description, // Use the description from the form as the comment
                'created_by' => auth()->id(), // Set the creator ID
            ]);

            // 2. Create Transaction and associate with FundTransfer
            // The `operation()` relationship automatically sets operation_id and operation_type
            $transaction = $fundTransfer->transaction()->create([
                'description' => $description,
                'transaction_date' => $fundTransfer->transfer_date,
                'status' => 'completed', // Default or set as needed
                 'accounting_period' => now()->format('Y-m'), // Example: Use current month
                // No created_by field in table
            ]);
            
            // 4. Create Transaction Lines (Double Entry)
            TransactionLine::create([
                'transaction_id' => $transaction->id,
                'account_id' => $fromAccount->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Transfer to ' . $toAccount->description,
            ]);

            TransactionLine::create([
                'transaction_id' => $transaction->id,
                'account_id' => $toAccount->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Transfer from ' . $fromAccount->description,
            ]);

            DB::commit();

            return redirect()->route('admin.transactions.show', $transaction)->with('success', 'Fund transfer completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Fund Transfer Failed: " . $e->getMessage(), [
                'validated_data' => $validated,
                'exception' => $e
            ]);
            // Provide a more user-friendly error message
            return back()->with('error', 'Fund transfer failed. Please check the details and try again. Error: ' . $e->getMessage())->withInput(); 
        }
    }

    // API Endpoint to get accounts for a user
    public function getAccountsForUser(User $user)
    {
        // Only return non-system accounts? Or filter by currency? Assuming USD for now.
        $accounts = $user->accounts()->where('currency', 'USD')->get(['id', 'description', 'account_type']);
        return response()->json($accounts);
    }
} 