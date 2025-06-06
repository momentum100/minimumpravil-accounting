<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     */
    public function index(): View
    {
        $transactions = Transaction::with([
                                      'operation.creator', // Eager load the operation and its creator
                                      // Eager load lines and their account + user for display
                                      'lines.account.user'
                                  ])
                                  ->orderBy('id', 'desc') // Order by ID descending
                                  ->paginate(200); // Paginate with 200 items

        return view('admin.transactions.index', compact('transactions'));
    }

    /**
     * Display the specified transaction and its lines.
     */
    public function show(Transaction $transaction): View
    {
        // Eager load the operation that this transaction belongs to,
        // the creator of that operation, and the transaction lines with their accounts.
        $transaction->load([
            'operation.creator', // Load the operation (FundTransfer, etc.) and its creator
            'lines.account'      // Load the lines and their associated accounts
        ]);

        return view('admin.transactions.show', compact('transaction'));
    }

    // No create, store, edit, update, destroy methods needed here for now
    // Other operations like adjustments, expenses will have their own controllers/routes
} 