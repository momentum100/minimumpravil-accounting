<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyExpense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class DailyExpenseController extends Controller
{
    /**
     * Display a listing of the resource along with the form to add new ones.
     */
    public function index(Request $request): View | JsonResponse
    {
        // Fetch buyers (non-virtual users with role 'buyer') for the dropdown
        $buyers = User::where('is_virtual', false)->where('role', 'buyer')->orderBy('name')->get(['id', 'name']);

        // Handle AJAX request for fetching paginated expenses
        if ($request->ajax()) {
            $query = DailyExpense::with('buyer:id,name', 'creator:id,name') // Eager load relationships
                                    ->orderBy('operation_date', 'desc'); // Explicit order by
                                   // ->latest('operation_date') // Replaced latest with orderBy
            
            // Log the SQL before pagination
            Log::debug('DailyExpense Index SQL Query: ' . $query->toSql()); 
            
            $expenses = $query->paginate(15); // Adjust page size as needed
            
            return response()->json($expenses);
        }

        // Initial page load
        return view('admin.daily-expenses.index', compact('buyers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage via AJAX.
     */
    public function store(Request $request): JsonResponse
    {
        // Basic validation (replace with StoreDailyExpenseRequest later)
        // Use json()->all() to ensure JSON payload is parsed correctly
        $validator = Validator::make($request->json()->all(), [
            'operation_date' => ['required', 'date'],
            'buyer_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'buyer')->where('is_virtual', false)],
            'category' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'tariff' => ['required', 'numeric', 'gte:0'],
            // 'total' => ['required', 'numeric', 'gte:0'], // Calculated
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Calculate total using correct keys
        $validated['total'] = $validated['quantity'] * $validated['tariff'];
        // Set creator
        $validated['created_by'] = auth()->id();

        try {
            $expense = DailyExpense::create($validated);

            // Eager load relationships for the response
            $expense->load('buyer:id,name', 'creator:id,name');

            // *** Transaction logic via Observer/Service will be triggered here later ***

            return response()->json($expense, 201); // Return created expense

        } catch (\Exception $e) {
            // Log::error('Failed to create daily expense', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create expense. Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
