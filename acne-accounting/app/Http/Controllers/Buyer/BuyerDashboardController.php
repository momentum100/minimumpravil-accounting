<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Import DB facade if needed for complex queries, though Eloquent is preferred
use Carbon\Carbon;

class BuyerDashboardController extends Controller
{
    /**
     * Display the buyer's dashboard with their transactions for a selected month.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $accountIds = $user->accounts()->pluck('id'); // Assuming User model has an 'accounts' relationship

        // Validate and set the target month (default to current month)
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        try {
            $targetDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        } catch (\Exception $e) {
            // Handle invalid date format, default to current month
            report($e); // Log the error
            $targetDate = Carbon::now()->startOfMonth();
            $selectedMonth = $targetDate->format('Y-m');
        }

        // Fetch transactions where at least one transaction line involves one of the buyer's accounts
        // and the transaction occurred within the selected month.
        // Eager load necessary relationships to avoid N+1 problems.
        $transactions = Transaction::whereHas('lines', function ($query) use ($accountIds) {
            $query->whereIn('account_id', $accountIds);
        })
        ->whereYear('transaction_date', $targetDate->year)
        ->whereMonth('transaction_date', $targetDate->month)
        ->with(['lines' => function ($query) use ($accountIds) {
            // Optionally filter lines shown to only those relevant to the buyer's accounts,
            // although showing all lines of the transaction might be needed for context.
             $query->with('account'); // Eager load account details for lines
        }, 'operation']) // Eager load the original operation (e.g., DailyExpense, FundTransfer)
        ->orderBy('transaction_date', 'desc')
        ->paginate(15); // Use pagination

        // Get a list of months with transactions for the dropdown
        // Consider optimizing this query if performance becomes an issue
        $availableMonths = Transaction::whereHas('lines', function ($query) use ($accountIds) {
                $query->whereIn('account_id', $accountIds);
            })
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month_value")
            ->distinct()
            ->orderBy('month_value', 'desc')
            ->pluck('month_value');

        return view('buyer.dashboard', [
            'transactions' => $transactions,
            'selectedMonth' => $selectedMonth,
            'availableMonths' => $availableMonths,
        ]);
    }
}
