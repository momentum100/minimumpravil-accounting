<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BulkExpenseController; // Or a dedicated API controller
use App\Http\Controllers\Admin\FundTransferController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Example route (usually comes by default)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// v1 Routes
Route::prefix('v1')->group(function () {
    // Bulk expense entry endpoint
    Route::post('/bulk-expenses', [BulkExpenseController::class, 'storeApi'])
        ->middleware('auth.internal_api') // Protect with our API key middleware
        ->name('api.v1.bulk-expenses.store');
    
    // Bulk transfers endpoint
    Route::post('/bulk-transfers', [FundTransferController::class, 'bulkStoreApi'])
        ->middleware('auth.internal_api') // Protect with our API key middleware
        ->name('api.v1.bulk-transfers.store');
        
    // Single transfer endpoint
    Route::post('/transfer', [FundTransferController::class, 'singleTransferApi'])
        ->middleware('auth.internal_api') // Protect with our API key middleware
        ->name('api.v1.transfer.store');
}); 