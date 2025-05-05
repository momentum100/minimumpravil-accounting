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

class StatementController extends Controller
{
    /**
     * Display the statement for the authenticated buyer.
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
            'transactions' => $transactions,
            'totalAmount' => $totalAmount,
            'dateFrom' => $dateFrom ?? ($startDate ? $startDate->format('Y-m-d') : null), // Pass resolved dates back
            'dateTo' => $dateTo ?? ($endDate ? $endDate->format('Y-m-d') : null),
            'buyerAccountId' => $buyerAccountId, // Needed for view logic
        ]);
    }
} 