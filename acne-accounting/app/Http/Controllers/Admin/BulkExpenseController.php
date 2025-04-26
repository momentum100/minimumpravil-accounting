<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyExpense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class BulkExpenseController extends Controller
{
    /**
     * Show the form for creating bulk daily expenses.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.bulk-expenses.create');
    }

    /**
     * Store bulk daily expenses entered via textarea.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $rawData = $request->input('expense_data');
        if (empty($rawData)) {
            return back()->withErrors(['expense_data' => 'Expense data cannot be empty.'])->withInput();
        }

        // Normalize line endings and split into lines, removing empty lines
        $lines = preg_split('/\r\n|\r|\n/', $rawData);
        $lines = array_filter(array_map('trim', $lines), function($line) {
            return !empty($line);
        });
        $lines = array_values($lines); // Re-index array

        if (count($lines) % 4 !== 0) {
             return back()->withErrors(['expense_data' => 'Input data must have a multiple of 4 non-empty lines.'])->withInput();
        }

        $chunks = array_chunk($lines, 4);
        $errors = [];
        $successCount = 0;
        $logEntries = [];

        foreach ($chunks as $index => $chunk) {
            $lineNumberStart = ($index * 4) + 1;

            // Basic validation for 4 lines
            if (count($chunk) !== 4) {
                $errors[] = "Record starting near line {$lineNumberStart}: Incomplete record (expected 4 lines).";
                // Log the invalid chunk attempt to the raw log as well
                Log::info("[Bulk Web] Attempting to log invalid raw chunk (size: " . count($chunk) . ") to bulk_expenses_raw_input.log");
                Storage::disk('local')->append('logs/bulk_expenses_raw_input.log', json_encode($chunk));
                continue;
            }

            // Log raw input chunk immediately after basic validation
            Log::info("[Bulk Web] Attempting to log valid raw chunk (size: 4) to bulk_expenses_raw_input.log", ['chunk' => $chunk]);
            try {
                Storage::disk('local')->append('logs/bulk_expenses_raw_input.log', json_encode($chunk));
                Log::info("[Bulk Web] Successfully appended raw chunk to bulk_expenses_raw_input.log");
            } catch (\Exception $e) {
                Log::error("[Bulk Web] FAILED to append raw chunk to bulk_expenses_raw_input.log", ['error' => $e->getMessage(), 'exception' => $e]);
                // Add error to main error list as well
                $errors[] = "Record starting near line {$lineNumberStart}: CRITICAL - Failed to write to raw input log.";
                continue; // Skip further processing for this chunk if logging failed
            }

            [$buyerUsername, $category, $quantity, $tariff] = $chunk;

            // Validate numeric fields
            $validator = Validator::make([
                'quantity' => $quantity,
                'tariff' => $tariff,
            ], [
                'quantity' => 'required|numeric|min:0',
                'tariff' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                     $errors[] = "Record starting near line {$lineNumberStart} (User: {$buyerUsername}): {$message}";
                }
                continue;
            }

            // Find Buyer User
            $buyer = User::where('name', $buyerUsername)->where('role', 'buyer')->first();
            if (!$buyer) {
                $errorMessage = "Record starting near line {$lineNumberStart}: Buyer user '{$buyerUsername}' not found.";
                $errors[] = $errorMessage;
                Log::warning("[Bulk Web] Validation Error: {$errorMessage}"); // Log the specific error
                continue;
            }

            // Prepare Log Entry for processing status log (bulk_expenses.log)
            $logEntry = [
                'submitted_at' => Carbon::now()->toISOString(),
                'submitted_by' => Auth::id(),
                'status' => 'pending', // Will be updated later
                'buyer_username' => $buyerUsername,
                'category' => $category,
                'quantity' => $quantity,
                'tariff' => $tariff,
                'raw_input_ref' => $chunk, // Reference the input
                'error' => null,
            ];

            try {
                // Create Daily Expense - Use DB column names
                DailyExpense::create([
                    'buyer_id' => $buyer->id,
                    'operation_date' => Carbon::now()->toDateString(), // Use operation_date, format as date string
                    'category' => $category,
                    'quantity' => $quantity,
                    'tariff' => $tariff,
                    'total' => $quantity * $tariff, // Use total
                    'created_by' => Auth::id(),
                    'comment' => "Bulk entry via web form", // Add comment if needed
                ]);
                $successCount++;
                $logEntry['status'] = 'success';
            } catch (\Exception $e) {
                $errorMessage = "Record starting near line {$lineNumberStart} (User: {$buyerUsername}): Failed to create expense - " . $e->getMessage();
                $errors[] = $errorMessage;
                Log::error("Bulk Expense Error: " . $errorMessage, ['exception' => $e, 'chunk' => $chunk]);
                $logEntry['status'] = 'failed';
                $logEntry['error'] = $e->getMessage();
            }
            $logEntries[] = $logEntry;
        }

        // Log all processing status entries to bulk_expenses.log
        if (!empty($logEntries)) {
            $logContent = implode("\n", array_map('json_encode', $logEntries)) . "\n";
            Storage::disk('local')->append('logs/bulk_expenses.log', $logContent);
        }

        if (!empty($errors)) {
            // If there were errors, redirect back with errors and retain input
            return back()->withErrors($errors)->withInput();
        } else {
            // If all successful, redirect back with success message
            return redirect()->route('admin.bulk-expenses.create')->with('success', "Successfully processed {$successCount} expense records.");
        }
    }

    /**
     * Store bulk daily expenses submitted via API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeApi(Request $request)
    {
        // Validate the main request structure
        $validatedRequest = Validator::make($request->all(), [
            'expense_records' => 'required|array|min:1',
            'expense_records.*.buyer_username' => 'required|string|max:255',
            'expense_records.*.category' => 'required|string|max:50', // Match DB column length
            'expense_records.*.quantity' => 'required|numeric|min:0',
            'expense_records.*.tariff' => 'required|numeric|min:0',
            'expense_records.*.comment' => 'nullable|string',
        ]);

        if ($validatedRequest->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validatedRequest->errors()
            ], 422);
        }

        $records = $validatedRequest->validated()['expense_records'];
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        $logEntries = [];
        $apiUserId = config('services.internal_api.user_id'); // Get user ID for created_by

        if (!$apiUserId) {
            Log::error('Bulk Expense API Error: INTERNAL_API_USER_ID is not configured in .env or config/services.php');
            return response()->json(['message' => 'Internal Server Configuration Error.'], 500);
        }

        foreach ($records as $index => $record) {
            $recordIdentifier = "Record #" . ($index + 1) . " (User: {$record['buyer_username']})";

            // Find Buyer User
            $buyer = User::where('name', $record['buyer_username'])->where('role', 'buyer')->first();
            if (!$buyer) {
                $errorMessage = "Buyer user '{$record['buyer_username']}' not found.";
                $results[] = ['status' => 'failed', 'input' => $record, 'error' => $errorMessage];
                $errorCount++;
                $logEntries[] = $this->prepareApiLogEntry($record, $apiUserId, 'failed', $errorMessage);
                continue;
            }

            // Prepare Log Entry (before potential DB error)
             $logEntry = $this->prepareApiLogEntry($record, $apiUserId, 'pending');

            try {
                // Create Daily Expense
                DailyExpense::create([
                    'buyer_id' => $buyer->id,
                    'operation_date' => Carbon::now()->toDateString(),
                    'category' => $record['category'],
                    'quantity' => $record['quantity'],
                    'tariff' => $record['tariff'],
                    'total' => $record['quantity'] * $record['tariff'],
                    'created_by' => $apiUserId,
                    'comment' => Arr::get($record, 'comment'), // Use Arr::get for optional field
                ]);
                $results[] = ['status' => 'success', 'input' => $record];
                $successCount++;
                $logEntry['status'] = 'success';
            } catch (\Exception $e) {
                $errorMessage = "Failed to create expense - " . $e->getMessage();
                $results[] = ['status' => 'failed', 'input' => $record, 'error' => $errorMessage];
                $errorCount++;
                Log::error("Bulk Expense API Error: " . $recordIdentifier . ' - ' . $errorMessage, ['exception' => $e, 'record' => $record]);
                $logEntry['status'] = 'failed';
                $logEntry['error'] = $e->getMessage();
            }
             $logEntries[] = $logEntry;
        }

        // Log all processed entries to a specific API log file
        if (!empty($logEntries)) {
            $logContent = implode("\n", array_map('json_encode', $logEntries)) . "\n";
            Storage::disk('local')->append('logs/bulk_expenses_api.log', $logContent);
        }

        if ($errorCount > 0) {
            return response()->json([
                'message' => "Processed {$successCount} records with {$errorCount} errors.",
                'processed_count' => $successCount,
                'error_count' => $errorCount,
                'results' => $results
            ], 422); // Unprocessable Entity for partial failures
        } else {
            return response()->json([
                'message' => "Successfully processed {$successCount} expense records.",
                'processed_count' => $successCount,
                'results' => $results
            ], 200);
        }
    }

    /**
     * Helper function to prepare a log entry for the API.
     */
    private function prepareApiLogEntry(array $record, $apiUserId, string $status, ?string $error = null): array
    {
        return [
            'processed_at' => Carbon::now()->toISOString(),
            'api_user_id' => $apiUserId,
            'status' => $status,
            'input_record' => $record,
            'error' => $error,
        ];
    }
}
