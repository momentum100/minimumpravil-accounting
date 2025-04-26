<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BulkExpenseController; // Or a dedicated API controller

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
}); 