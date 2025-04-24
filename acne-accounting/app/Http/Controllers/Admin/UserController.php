<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team; // Needed for assigning buyers to teams
use App\Models\Account; // <-- Add this
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // Define available roles centrally
    protected $availableRoles = ['owner', 'finance', 'buyer', 'agency'];
    // Define available sub2 tags centrally
    protected $availableSub2Tags = ['1', '2', '3'];

    /**
     * Display a listing of the resource.
     * We can filter by role here.
     */
    public function index(Request $request): View
    {
        $validRoles = ['owner', 'finance', 'buyer', 'agency'];
        $roleFilter = $request->input('role');

        $usersQuery = User::with(['team', 'accounts']) // Eager load team and accounts
                        ->latest();

        if ($roleFilter && in_array($roleFilter, $validRoles)) {
            $usersQuery->where('role', $roleFilter);
        }

        $users = $usersQuery->paginate(15)->withQueryString(); // Append filter to pagination links

        return view('admin.users.index', compact('users', 'roleFilter', 'validRoles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Log::info('UserController@create accessed.');
        $roles = $this->availableRoles;
        $availableSub2Tags = $this->availableSub2Tags; // Use the class property
        // Load teams if needed for non-agency roles
        $teams = Team::orderBy('name')->get(); // Example: Load teams

        return view('admin.users.create', compact('roles', 'availableSub2Tags', 'teams'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        Log::info('UserController@store: Received request');

        $validatedData = $request->validated();
        Log::info('UserController@store: Validation passed', ['validated_data' => $validatedData]);

        try {
            // Hash password only if it's present (i.e., not an agency user)
            if (!empty($validatedData['password'])) {
                 Log::info('UserController@store: Hashing password.');
                 $validatedData['password'] = Hash::make($validatedData['password']);
            } else {
                Log::info('UserController@store: No password provided (likely agency role).');
                unset($validatedData['password']); // Ensure password is not set if empty
            }

            // Ensure boolean fields have defaults if not present
            $validatedData['is_virtual'] = $validatedData['is_virtual'] ?? false;
            $validatedData['active'] = $validatedData['active'] ?? true; // Default active to true?
            Log::info('UserController@store: Setting boolean defaults', ['is_virtual' => $validatedData['is_virtual'], 'active' => $validatedData['active']]);

            // Ensure sub2 is an empty array if null/not provided (Form Request handles array conversion)
            $validatedData['sub2'] = $validatedData['sub2'] ?? [];
            Log::info('UserController@store: Final data before create', $validatedData);

            $user = User::create($validatedData);
            Log::info('UserController@store: User created successfully', ['user_id' => $user->id]);

            // Automatically create a default USD account if user is Buyer or Agency
            if (in_array($user->role, ['buyer', 'agency'])) {
                $accountType = ($user->role === 'buyer') ? 'BUYER_MAIN' : 'AGENCY_MAIN';
                Account::create([
                    'user_id' => $user->id,
                    'account_type' => $accountType,
                    'currency' => 'USD',
                    'description' => $user->name . ' Main USD Account',
                ]);
            }

            // Redirect to the user's detail page or index page
            return redirect()->route('admin.users.index')->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            Log::error('UserController@store: Error creating user: ' . $e->getMessage(), [
                'exception' => $e,
                'validated_data' => $validatedData // Log validated data on error
            ]);
            return back()->with('error', 'Failed to create user. Please check logs.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $user->load(['team', 'accounts']); // Eager load team and accounts
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $roles = $this->availableRoles;
        $availableSub2Tags = $this->availableSub2Tags;
        $teams = Team::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles', 'availableSub2Tags', 'teams'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // Hash password if provided and changed
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // Don't update password if field is empty
        }

        // Set is_virtual based on role
        $validated['is_virtual'] = ($validated['role'] === 'agency');

        // Clear team_id if not a buyer
        if ($validated['role'] !== 'buyer') {
            $validated['team_id'] = null;
        }
         // Clear contact_info if not an agency
        if ($validated['role'] !== 'agency') {
            $validated['contact_info'] = null;
        }
         // Clear sub2 if not a buyer
         if ($validated['role'] !== 'buyer') {
            $validated['sub2'] = null;
        }

        // Prevent admin from deactivating themselves or changing their own role easily
        if ($user->id === auth()->id()) {
             if(isset($validated['active']) && !$validated['active']) {
                 return back()->with('error', 'You cannot deactivate yourself.');
             }
             if(isset($validated['role']) && $validated['role'] !== $user->role) {
                 // Potentially allow role change but add extra confirmation or logic
                 // For now, prevent self-role change to avoid lockout
                 // return back()->with('error', 'Changing your own role is restricted.');
                 unset($validated['role']); // Or just ignore the change
             }
        }


        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        // Add checks here if needed (e.g., cannot delete user with active accounts/transactions?)
        // if ($user->accounts()->exists() || ...) {
        //     return back()->with('error', 'Cannot delete user with associated financial records.');
        // }


        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
} 