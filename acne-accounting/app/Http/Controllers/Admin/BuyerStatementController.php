<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionLine;
use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Add DB facade

class BuyerStatementController extends Controller
{
    /**
     * Display a listing of the buyer statements.
     */
    public function index(Request $request): View
    {
        Log::info('Accessing Buyer Statement Index', ['request_data' => $request->all()]);

        $buyers = User::where('role', 'buyer')->orderBy('name')->get();

        $selectedBuyerId = $request->input('buyer_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $period = $request->input('period');

        $transactions = null;
        $totalAmount = 0;
        $buyerAccount = null;

        if ($selectedBuyerId) {
            // Find the account linked to the buyer
            $buyerAccount = Account::where('user_id', $selectedBuyerId)->first();

            if ($buyerAccount) {
                Log::debug('Found buyer account', ['buyer_id' => $selectedBuyerId, 'account_id' => $buyerAccount->id]);

                // Determine date range
                $startDate = null;
                $endDate = null;
                if ($period === 'last_30_days') {
                    $startDate = Carbon::now()->subDays(30)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                } elseif ($period === 'last_60_days') {
                    $startDate = Carbon::now()->subDays(60)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                } elseif ($dateFrom && $dateTo) {
                    try {
                        $startDate = Carbon::parse($dateFrom)->startOfDay();
                        $endDate = Carbon::parse($dateTo)->endOfDay();
                    } catch (\Exception $e) {
                        Log::error('Invalid date format', ['from' => $dateFrom, 'to' => $dateTo, 'error' => $e->getMessage()]);
                        // Potentially flash error message
                        $startDate = null; $endDate = null; // Prevent further processing with invalid dates
                    }
                }

                // --- Revised Query Logic --- 
                $transactionQuery = Transaction::query();

                // Filter transactions based on the existence of a line for the buyer's account
                $transactionQuery->whereHas('lines', function ($q) use ($buyerAccount) {
                    $q->where('account_id', $buyerAccount->id);
                });

                // Apply date filter if valid dates are present
                if ($startDate && $endDate) {
                    $transactionQuery->whereBetween('transaction_date', [$startDate, $endDate]);
                     Log::debug('Applying date filter to transactions', ['from' => $startDate, 'to' => $endDate]);
                } else {
                     Log::debug('No date filter applied to transactions');
                }

                // Eager load lines and related data for the view
                $transactionQuery->with(['lines.account.user']);
                
                // Order and paginate
                $transactions = $transactionQuery->orderBy('transaction_date', 'desc')->paginate(25);
                 Log::info('Fetched transactions for buyer account', ['count' => $transactions->count()]);

                // --- Revised Total Calculation --- 
                if ($startDate && $endDate) {
                    // Sum credits on the buyer's account lines for transactions *within the date range*
                    $totalAmount = TransactionLine::where('account_id', $buyerAccount->id)
                        ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('transaction_date', [$startDate, $endDate]);
                        })
                        ->sum('credit');
                    
                    Log::info('Calculated total amount from transaction lines (credit)', [
                        'buyer_id' => $selectedBuyerId,
                        'account_id' => $buyerAccount->id,
                        'from' => $startDate,
                        'to' => $endDate,
                        'total' => $totalAmount
                    ]);
                } else {
                     Log::info('Total amount calculation skipped. Date range not specified.');
                }

            } else {
                Log::warning('Could not find account for selected buyer', ['buyer_id' => $selectedBuyerId]);
                // Ensure transactions is an empty paginator if account not found
                $transactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
            }
        } else {
            Log::debug('No buyer selected.');
            // Ensure transactions is an empty paginator if no buyer selected
            $transactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
        }


        return view('admin.buyer-statements.index', [
            'buyers' => $buyers,
            'transactions' => $transactions, // Pass the potentially empty paginator
            'totalAmount' => $totalAmount,
            'selectedBuyerId' => $selectedBuyerId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'period' => $period,
            // Pass buyerAccountId to the view for use in the loop (from previous step)
             'buyerAccountId' => $buyerAccount?->id 
        ]);
    }
} 