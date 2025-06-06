<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FundTransferController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\DailyExpenseController;
use App\Http\Controllers\Admin\BuyerStatementController as AdminBuyerStatementController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsAdminOrFinance;
use App\Http\Controllers\Buyer\StatementController as BuyerStatementController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('not.buyer')
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Admin Routes
Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureUserIsAdminOrFinance::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function() { // Basic Admin Dashboard
            return view('admin.dashboard'); // TODO: Create admin dashboard view
        })->name('dashboard');

        Route::resource('teams', TeamController::class);
        Route::resource('users', UserController::class);
        Route::post('users/check-existence', [UserController::class, 'checkExistence'])->name('users.check');

        // Fund Transfers
        Route::get('fund-transfers/create', [FundTransferController::class, 'create'])->name('fund-transfers.create');
        Route::post('fund-transfers', [FundTransferController::class, 'store'])->name('fund-transfers.store');

        // Fund Transfers (Bulk) - NEW
        Route::get('fund-transfers/bulk/create', [FundTransferController::class, 'bulkCreate'])->name('fund-transfers.bulk.create');
        Route::post('fund-transfers/bulk/store', [FundTransferController::class, 'bulkStore'])->name('fund-transfers.bulk.store'); // Endpoint for individual AJAX transfers

        // API endpoint for user accounts (used by fund transfer form)
        Route::get('users/{user}/accounts', [FundTransferController::class, 'getAccountsForUser'])->name('users.accounts');

        // Transactions
        Route::resource('transactions', TransactionController::class)->only(['index', 'show']);

        // Daily Expenses (using index for view/add, store for AJAX add)
        Route::get('daily-expenses', [DailyExpenseController::class, 'index'])->name('daily-expenses.index');
        Route::post('daily-expenses', [DailyExpenseController::class, 'store'])->name('daily-expenses.store');

        // Bulk Daily Expenses Entry (Admin/Finance only)
        Route::middleware('admin_or_finance')->group(function() {
            Route::get('bulk-expenses/create', [\App\Http\Controllers\Admin\BulkExpenseController::class, 'create'])->name('bulk-expenses.create');
            Route::post('bulk-expenses', [\App\Http\Controllers\Admin\BulkExpenseController::class, 'store'])->name('bulk-expenses.store');
        });

        // Add Buyer Statement Route (Admin)
        Route::get('/buyer-statements', [AdminBuyerStatementController::class, 'index'])->name('buyer-statements.index');
    });

// Buyer Routes
Route::middleware(['auth', 'verified', 'buyer'])
    ->prefix('buyer')
    ->name('buyer.')
    ->group(function () {
        // Point the main buyer route to the StatementController
        Route::get('dashboard', [BuyerStatementController::class, 'index'])->name('dashboard'); 
        // Add route for agency transfers
        Route::get('agency-transfers', [BuyerStatementController::class, 'agencyTransfers'])->name('agency-transfers.index');
        // Remove the separate statement route as it's now the dashboard
        // Route::get('statement', [BuyerStatementController::class, 'index'])->name('statement.index'); 
        // Add other buyer-specific routes here if needed
    });

// Admin route for agency transfers with buyer selection
Route::get('admin/agency-transfers/{buyer?}', [App\Http\Controllers\Admin\BuyerStatementController::class, 'adminAgencyTransfers'])
    ->name('admin.agency-transfers.index')
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureUserIsAdminOrFinance::class]);
