<?php
declare(strict_types=1);

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionLine;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\FundTransfer;

class StatementController extends Controller
{
    /**
     * Display the general statement for the authenticated buyer.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        Log::info('Accessing Buyer Statement Index', ['user_id' => $user->id, 'request_data' => $request->all()]);

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        // No period shortcuts needed for buyer view unless requested

        $transactions = null;
        $totalAmount = 0;

        // Find the account linked to the logged-in buyer
        $buyerAccount = Account::where('user_id', $user->id)->first();

        if ($buyerAccount) {
            Log::debug('Found buyer account', ['user_id' => $user->id, 'account_id' => $buyerAccount->id]);

            // Determine date range (default to this month if none provided? or show nothing?)
            $startDate = null;
            $endDate = null;
            if ($dateFrom && $dateTo) {
                try {
                    $startDate = Carbon::parse($dateFrom)->startOfDay();
                    $endDate = Carbon::parse($dateTo)->endOfDay();
                    Log::debug('Applying custom date filter', ['from' => $dateFrom, 'to' => $dateTo]);
                } catch (\Exception $e) {
                    Log::error('Invalid date format', ['from' => $dateFrom, 'to' => $dateTo, 'error' => $e->getMessage()]);
                    // Optionally flash error message
                }
            } else {
                 // Default: Let's default to the current month for the buyer view
                 $startDate = Carbon::now()->startOfMonth();
                 $endDate = Carbon::now()->endOfMonth();
                 Log::debug('Defaulting to current month date filter', ['from' => $startDate, 'to' => $endDate]);
            }

            // Query transactions if we have a valid account
            $transactionQuery = Transaction::query();

            // Filter transactions based on the existence of a line for the buyer's account
            $transactionQuery->whereHas('lines', function ($q) use ($buyerAccount) {
                $q->where('account_id', $buyerAccount->id);
            });

            // Apply date filter if valid dates are present
            if ($startDate && $endDate) {
                $transactionQuery->whereBetween('transaction_date', [$startDate, $endDate]);
            }

            // Eager load lines and related data for the view
            $transactionQuery->with(['lines.account.user']); // Keep this for consistency, though user might always be the logged-in one
            
            // Order and paginate
            $transactions = $transactionQuery->orderBy('transaction_date', 'desc')->paginate(25);
            Log::info('Fetched transactions for buyer account', ['count' => $transactions->count()]);

            // Calculate Total Amount for the period
            if ($startDate && $endDate) {
                 $totalAmount = TransactionLine::where('account_id', $buyerAccount->id)
                    ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('transaction_date', [$startDate, $endDate]);
                    })
                    ->sum('credit');
                
                Log::info('Calculated total amount for buyer', [
                    'account_id' => $buyerAccount->id,
                    'from' => $startDate,
                    'to' => $endDate,
                    'total' => $totalAmount
                ]);
            }

        } else {
            Log::warning('Could not find account for authenticated buyer', ['user_id' => $user->id]);
            $transactions = new LengthAwarePaginator([], 0, 25);
        }

        // Pass buyerAccountId to the view for row amount calculation
        $buyerAccountId = $buyerAccount?->id;

        return view('buyer.statement.index', [
            'viewMode' => 'general',
            'transactions' => $transactions,
            'totalAmount' => $totalAmount,
            'dateFrom' => $dateFrom ?? ($startDate ? $startDate->format('Y-m-d') : null),
            'dateTo' => $dateTo ?? ($endDate ? $endDate->format('Y-m-d') : null),
            'buyerAccountId' => $buyerAccountId,
        ]);
    }

    /**
     * Display transfers (charges) from agencies to the authenticated buyer.
     */
    public function agencyTransfers(Request $request): View
    {
        $user = Auth::user();
        Log::info('Accessing Buyer Agency Transfers', ['user_id' => $user->id, 'request_data' => $request->all()]);

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $transactions = null;
        $totalAmount = 0;
        $startDate = null;
        $endDate = null;

        $buyerAccount = Account::where('user_id', $user->id)->first();

        if ($buyerAccount) {
            Log::debug('Found buyer account for agency transfer view', ['user_id' => $user->id, 'account_id' => $buyerAccount->id]);

            // Determine date range (default to current month)
            if ($dateFrom && $dateTo) {
                try {
                    $startDate = Carbon::parse($dateFrom)->startOfDay();
                    $endDate = Carbon::parse($dateTo)->endOfDay();
                } catch (\Exception $e) {
                    Log::error('Invalid date format for agency transfers', ['from' => $dateFrom, 'to' => $dateTo, 'error' => $e->getMessage()]);
                }
            } else {
                 $startDate = Carbon::now()->startOfMonth();
                 $endDate = Carbon::now()->endOfMonth();
            }

            // --- Query for Agency Transfers (Expenses charged via Agency) --- 
            $transactionQuery = Transaction::query()
                // 1. Must be a FundTransfer operation
                ->where('operation_type', FundTransfer::class)
                // 2. Must have a line CREDITING the buyer's account (money OUT / expense recognized)
                ->whereHas('lines', function ($lineQuery) use ($buyerAccount) {
                    $lineQuery->where('account_id', $buyerAccount->id)
                              ->where('credit', '>', 0); // Switched from debit to credit
                })
                // 3. The FundTransfer operation must involve a debit to an Agency user's account
                 //    (This implicitly means the agency is the 'from' account in the fund transfer)
                ->whereHasMorph(
                    'operation', // Relationship name
                    [FundTransfer::class], // Allowed morph types
                    function ($fundTransferQuery) { // Query on FundTransfer model
                        // Check the user associated with the FROM account (which corresponds to the debit line)
                        $fundTransferQuery->whereHas('fromAccount.user', function ($userQuery) {
                            $userQuery->where('role', 'agency');
                        });
                    }
                );
                
            // Apply date filter to transaction date
            if ($startDate && $endDate) {
                $transactionQuery->whereBetween('transaction_date', [$startDate, $endDate]);
                 Log::debug('Applying date filter to agency transfers', ['from' => $startDate, 'to' => $endDate]);
            }
            
            // Eager load necessary data for display
            $transactionQuery->with([
                'operation.fromAccount.user', // Still useful to show agency name
                'lines' // To display amount (credit to buyer)
            ]);

            // Order and Paginate
            $transactions = $transactionQuery->orderBy('transaction_date', 'desc')->paginate(25);
            Log::info('Fetched agency expense transactions for buyer', ['count' => $transactions->count()]);

            // --- Calculate Total Amount (Sum of Credits) --- 
             if ($startDate && $endDate) {
                // Sum credits on the buyer's account lines for transactions *within the date range* that meet the agency criteria
                $totalAmount = TransactionLine::query()
                    ->where('account_id', $buyerAccount->id) // Line must be for buyer's account
                    ->where('credit', '>', 0) // Must be a credit (expense recognized)
                    ->whereHas('transaction', function($tQuery) use ($startDate, $endDate) { // Transaction must be in date range
                         $tQuery->whereBetween('transaction_date', [$startDate, $endDate])
                                ->where('operation_type', FundTransfer::class) // Must be a FundTransfer transaction
                                ->whereHasMorph( // FundTransfer must involve debiting an agency
                                    'operation', 
                                    [FundTransfer::class], 
                                    function ($ftQuery) {
                                        $ftQuery->whereHas('fromAccount.user', function ($uQuery) { 
                                            $uQuery->where('role', 'agency');
                                        });
                                    }
                                );
                    })
                    ->sum('credit'); // Sum the credit amount

                Log::info('Calculated total expense amount via agency transfers', [
                    'account_id' => $buyerAccount->id,
                    'from' => $startDate,
                    'to' => $endDate,
                    'total' => $totalAmount
                ]);
            }

        } else {
             Log::warning('Could not find account for authenticated buyer (agency transfers)', ['user_id' => $user->id]);
             $transactions = new LengthAwarePaginator([], 0, 25);
        }

        // Pass buyerAccountId to the view (needed for consistency, though not directly used in agency loop logic)
        $buyerAccountId = $buyerAccount?->id;

        // Change view name to reuse the main statement index view
        return view('buyer.statement.index', [ 
            'viewMode' => 'agency',
            'transactions' => $transactions,
            'totalAmount' => $totalAmount,
            'dateFrom' => $dateFrom ?? ($startDate ? $startDate->format('Y-m-d') : null),
            'dateTo' => $dateTo ?? ($endDate ? $endDate->format('Y-m-d') : null),
            'buyerAccountId' => $buyerAccountId,
        ]);
    }
} 