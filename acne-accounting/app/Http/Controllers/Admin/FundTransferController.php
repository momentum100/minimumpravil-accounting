<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Services\FundTransferService; // We'll create this later
use App\Services\TransactionService; // <<< Add TransactionService
use App\Http\Requests\Admin\StoreFundTransferRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\FundTransfer;
use App\Models\TransactionLine;
use Illuminate\Support\Facades\Validator;

class FundTransferController extends Controller
{
    protected TransactionService $transactionService; // <<< Add property

    // Inject TransactionService via constructor
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Show the form for creating a new fund transfer.
     */
    public function create(): View
    {
        // Get the System user
        $systemUser = User::where('is_virtual', true)->first();
        if (!$systemUser) {
            abort(500, 'System user not found.');
        }

        // Get non-virtual users suitable for transfers (for the 'To' dropdown)
        // Sort buyers separately for To dropdown
        $buyers = User::where('is_virtual', false)
                     ->where('role', 'buyer')
                     ->orderBy('name')
                     ->get(['id', 'name']);
        
        $agencies = User::where('is_virtual', false)
                       ->where('role', 'agency')
                       ->orderBy('name')
                       ->get(['id', 'name']);
        
        // Merge agencies first, then buyers for To dropdown
        $toUsers = $agencies->merge($buyers);

        // Get non-virtual users with role and terms for the 'From' dropdown
        $agenciesData = User::where('is_virtual', false)
                           ->where('role', 'agency')
                           ->orderBy('name')
                           ->get(['id', 'name', 'role', 'terms']);
        
        $buyersData = User::where('is_virtual', false)
                         ->where('role', 'buyer')
                         ->orderBy('name')
                         ->get(['id', 'name', 'role', 'terms']);

        // Create system user for dropdown
        $systemUserForDropdown = (object) [
            'id' => $systemUser->id,
            'name' => $systemUser->name . ' (System)',
            'role' => 'System',
            'terms' => 0
        ];

        // Create separator for dropdown
        $separator = (object) [
            'id' => '',
            'name' => '------',
            'role' => 'separator',
            'terms' => 0
        ];

        // Build the sorted fromUsers collection: System -> Agencies -> Separator -> Buyers
        $fromUsers = collect([$systemUserForDropdown])
                    ->merge($agenciesData)
                    ->push($separator)
                    ->merge($buyersData);
        
        // Merge all data for Alpine (without separator)
        $fromUsersData = collect([$systemUserForDropdown])
                        ->merge($agenciesData)
                        ->merge($buyersData);

        // Pass the system user ID as a default
        $defaultFromUserId = $systemUser->id;

        // Fetch recent fund transfer transactions
        $recentTransfers = Transaction::where('operation_type', FundTransfer::class)
            ->with(['operation.fromAccount', 'operation.toAccount']) // Eager load details via FundTransfer
            ->latest('transaction_date') // Order by date descending
            ->paginate(10); // Paginate results, 10 per page

        return view('admin.fund-transfers.create', compact(
            'fromUsers',
            'toUsers',
            'defaultFromUserId',
            'recentTransfers', // Pass transactions to the view
             'fromUsersData' // Pass the raw data for Alpine
        ));
    }

    /**
     * Store a newly created fund transfer in storage.
     *
     * @param StoreFundTransferRequest $request
     * @return RedirectResponse
     */
    public function store(StoreFundTransferRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        Log::info('Fund Transfer Store Request Validated:', $validated);

        $addCommission = $request->boolean('add_commission'); // Get boolean value of checkbox
        $commissionRate = 0;
        $originalAmount = (float) $validated['amount'];
        $finalAmount = $originalAmount;
        $fromUser = null;

        // If commission checkbox is checked, calculate final amount
        if ($addCommission) {
            $fromUser = User::find($validated['from_user_id']);
            // Ensure user is agency (case-insensitive) and has terms > 0
            if ($fromUser && strcasecmp($fromUser->role, 'Agency') == 0 && $fromUser->terms > 0) {
                $commissionRate = (float) $fromUser->terms;
                $commissionAmount = $originalAmount * $commissionRate;
                $finalAmount = $originalAmount + $commissionAmount;
                Log::info('Commission added', [
                    'original_amount' => $originalAmount,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commissionAmount,
                    'final_amount' => $finalAmount
                ]);
            } else {
                // Log a warning if checkbox was checked but user wasn't valid for commission
                 Log::warning('Add commission checkbox checked, but From User is not a valid Agency or has no terms.', [
                    'from_user_id' => $validated['from_user_id'],
                    'user_data' => $fromUser?->toArray()
                 ]);
                 // Proceed without commission if user data is invalid for it
                 $addCommission = false; // Ensure commission isn't processed later
            }
        }

        DB::beginTransaction();
        try {
            $fromAccount = Account::findOrFail($validated['from_account_id']);
            $toAccount = Account::findOrFail($validated['to_account_id']);
            // Use finalAmount which includes commission if applicable
            $amountToTransfer = $finalAmount;
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
                'amount' => $amountToTransfer, // Use the potentially adjusted amount
                'currency' => 'USD', // Assuming USD
                'transfer_date' => now(),
                'comment' => $description,
                'created_by' => auth()->id(),
            ]);
            Log::info('FundTransfer record created:', $fundTransfer->toArray());

            // 2. Use TransactionService to record the transaction and lines
            $mainDescription = $description . ($addCommission ? " (incl. " . ($commissionRate * 100) . "% comm.)" : "");
            $debitDesc = 'Transfer to ' . $toAccount->description . ($addCommission ? " (incl. commission)" : "");
            $creditDesc = 'Transfer from ' . $fromAccount->description . ($addCommission ? " (incl. commission)" : "");

            $transaction = $this->transactionService->recordOperationTransaction(
                $fundTransfer,              // Operation model
                $fromAccount->id,          // Debit Account ID
                $amountToTransfer,          // Debit Amount
                $toAccount->id,            // Credit Account ID
                $amountToTransfer,          // Credit Amount
                $debitDesc,                 // Debit Line Description
                $creditDesc,                // Credit Line Description
                $mainDescription            // Main Transaction Description
            );

            DB::commit();
            Log::info('Fund Transfer and associated Transaction Committed Successfully', ['fund_transfer_id' => $fundTransfer->id, 'transaction_id' => $transaction->id]);

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

    /**
     * Show the form for creating bulk fund transfers.
     */
    public function bulkCreate(): View
    {
        Log::info('Accessing bulk fund transfer creation page.');
        // Get the System user
        $systemUser = User::where('is_virtual', true)->firstOrFail();

        // Get non-virtual users with role and terms for the 'From' dropdown
        $agenciesData = User::where('is_virtual', false)
                           ->where('role', 'agency')
                           ->orderBy('name')
                           ->get(['id', 'name', 'role', 'terms']);
        
        $buyersData = User::where('is_virtual', false)
                         ->where('role', 'buyer')
                         ->orderBy('name')
                         ->get(['id', 'name', 'role', 'terms']);

        // Create system user for dropdown
        $systemUserForDropdown = (object) [
            'id' => $systemUser->id,
            'name' => $systemUser->name . ' (System)',
            'role' => 'System',
            'terms' => 0
        ];

        // Create separator for dropdown
        $separator = (object) [
            'id' => '',
            'name' => '------',
            'role' => 'separator',
            'terms' => 0
        ];

        // Build the sorted fromUsers collection: System -> Agencies -> Separator -> Buyers
        $fromUsers = collect([$systemUserForDropdown])
                    ->merge($agenciesData)
                    ->push($separator)
                    ->merge($buyersData);
        
        // Merge all data for Alpine (without separator)
        $fromUsersData = collect([$systemUserForDropdown])
                        ->merge($agenciesData)
                        ->merge($buyersData);

        // Pass the system user ID as a default
        $defaultFromUserId = $systemUser->id;

        // Fetch recent fund transfer transactions (same as in create method)
        $recentTransfers = Transaction::where('operation_type', FundTransfer::class)
            ->with(['operation.fromAccount', 'operation.toAccount'])
            ->latest('transaction_date')
            ->paginate(10);

        return view('admin.fund-transfers.bulk-create', compact(
            'fromUsers',
            'defaultFromUserId',
            'fromUsersData', // Pass the raw data for Alpine (needed for commission check)
            'recentTransfers' // <<< Pass recent transfers to bulk view
        ));
    }

    /**
     * Store a single fund transfer from the bulk processing interface.
     * Handles AJAX requests.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkStore(Request $request): JsonResponse
    {
        Log::info('Received bulkStore request', $request->all());

        $validator = Validator::make($request->all(), [
            'from_account_id' => 'required|exists:accounts,id',
            'to_username' => 'required|string|exists:users,name', // Validate username exists
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'unique_id' => 'required|string', // Unique ID from the frontend for tracking
        ]);

        if ($validator->fails()) {
            Log::warning('BulkStore validation failed', ['errors' => $validator->errors()->all(), 'input' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'unique_id' => $request->input('unique_id')
            ], 422);
        }

        $validated = $validator->validated();
        $amountToTransfer = (float) $validated['amount'];
        $description = $validated['description'] ?? 'Bulk Transfer'; // Default description

        DB::beginTransaction();
        try {
            // Lock the 'from' account row when fetching it
            $fromAccount = Account::lockForUpdate()->findOrFail($validated['from_account_id']);

            // Balance check removed as per request
            // if ($fromAccount->balance < $amountToTransfer) {
            //     throw new \Exception("Insufficient balance in the 'From' account ({$fromAccount->description}). Required: {$amountToTransfer}, Available: {$fromAccount->balance}");
            // }

            $toUser = User::where('name', $validated['to_username'])->firstOrFail();

            // Find a suitable USD account for the recipient
            // Prioritize 'Primary Operating', then any USD account. Adapt logic as needed.
            $toAccount = $toUser->accounts()
                ->where('currency', 'USD')
                ->orderByRaw("CASE WHEN account_type = 'Primary Operating' THEN 0 ELSE 1 END")
                ->first();

            if (!$toAccount) {
                 throw new \Exception("No suitable USD account found for recipient '{$validated['to_username']}'.");
            }

            // Basic checks
            if ($fromAccount->currency !== 'USD' || $toAccount->currency !== 'USD') {
                throw new \Exception("Only USD transfers are currently supported.");
            }
            if ($fromAccount->id === $toAccount->id) {
                throw new \Exception("Cannot transfer to the same account.");
            }

            // 1. Create FundTransfer record
            $fundTransfer = FundTransfer::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amountToTransfer,
                'currency' => 'USD',
                'transfer_date' => now(),
                'comment' => $description,
                'created_by' => auth()->id(),
            ]);
             Log::info('Bulk FundTransfer record created', ['id' => $fundTransfer->id, 'to_user' => $toUser->name, 'amount' => $amountToTransfer]);

            // 2. Use TransactionService to record the transaction and lines
            $debitDesc = 'Transfer to ' . $toAccount->description;
            $creditDesc = 'Transfer from ' . $fromAccount->description;

            $transaction = $this->transactionService->recordOperationTransaction(
                $fundTransfer,          // Operation model
                $fromAccount->id,      // Debit Account ID
                $amountToTransfer,      // Debit Amount
                $toAccount->id,        // Credit Account ID
                $amountToTransfer,      // Credit Amount
                $debitDesc,             // Debit Line Description
                $creditDesc,            // Credit Line Description
                $description            // Main Transaction Description (use provided/default)
            );

            DB::commit();
            Log::info('Bulk Fund Transfer and associated Transaction Committed Successfully', ['transaction_id' => $transaction->id, 'unique_id' => $request->input('unique_id')]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer completed successfully.',
                'transaction_id' => $transaction->id,
                'unique_id' => $request->input('unique_id')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Bulk Fund Transfer Failed for unique_id: " . $request->input('unique_id'), [
                'input' => $validated ?? $request->all(), // Log validated data if available, otherwise raw input
                'error_message' => $e->getMessage(),
                // 'exception' => $e // Maybe too verbose for standard logs
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage(),
                'unique_id' => $request->input('unique_id')
            ], 500); // Use 500 for server-side errors during processing
        }
    }
} 