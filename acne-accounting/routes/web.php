<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FundTransferController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\DailyExpenseController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Admin Routes
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function() { // Basic Admin Dashboard
            return view('admin.dashboard'); // TODO: Create admin dashboard view
        })->name('dashboard');

        Route::resource('teams', TeamController::class);
        Route::resource('users', UserController::class);

        // Fund Transfers
        Route::get('fund-transfers/create', [FundTransferController::class, 'create'])->name('fund-transfers.create');
        Route::post('fund-transfers', [FundTransferController::class, 'store'])->name('fund-transfers.store');

        // API endpoint for user accounts (used by fund transfer form)
        Route::get('users/{user}/accounts', [FundTransferController::class, 'getAccountsForUser'])->name('users.accounts');

        // Transactions
        Route::resource('transactions', TransactionController::class)->only(['index', 'show']);

        // Daily Expenses (using index for view/add, store for AJAX add)
        Route::get('daily-expenses', [DailyExpenseController::class, 'index'])->name('daily-expenses.index');
        Route::post('daily-expenses', [DailyExpenseController::class, 'store'])->name('daily-expenses.store');
    });
