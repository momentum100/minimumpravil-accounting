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

class UserController extends Controller
{
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
        $teams = Team::orderBy('name')->get(); // For assigning buyers
        $roles = ['owner', 'finance', 'buyer', 'agency'];
        return view('admin.users.create', compact('teams', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // Ensure null if not provided
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

        $user = User::create($validated);

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

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
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
        $teams = Team::orderBy('name')->get();
        $roles = ['owner', 'finance', 'buyer', 'agency'];
        return view('admin.users.edit', compact('user', 'teams', 'roles'));
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