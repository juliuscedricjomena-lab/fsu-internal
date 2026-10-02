<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Administrator landing: user management + role overview.
     */
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $roleFilter = $request->integer('role_id');

        $users = User::with('role:id,name')
            ->when($search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter, fn ($q, $roleId) => $q->where('role_id', $roleId))
            ->orderBy('name')
            ->get();

        $roles = Role::withCount('users')->orderBy('id')->get();

        return Inertia::render('Modules/Admin/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'role_id' => $roleFilter ?: null,
            ],
        ]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'designation' => ['nullable', 'string', 'max:255'],
            'rank' => ['nullable', 'string', 'max:100'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'designation' => $validated['designation'] ?? null,
            'rank' => $validated['rank'] ?? null,
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.index')->with('success', 'User created successfully.');
    }

    /**
     * Update an existing user (profile, role, status).
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'designation' => ['nullable', 'string', 'max:255'],
            'rank' => ['nullable', 'string', 'max:100'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        // Guard: an admin cannot deactivate or demote their own account here,
        // which could lock them out mid-session.
        if ($user->id === $request->user()->id && $validated['status'] !== 'active') {
            return back()->withErrors(['status' => 'You cannot change your own account status.']);
        }

        $user->update($validated);

        return redirect()->route('admin.index')->with('success', 'User updated successfully.');
    }

    /**
     * Reset a user's password.
     */
    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('admin.index')->with('success', "Password reset for {$user->name}.");
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, User $user)
    {
        // Guard: cannot delete your own account.
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.index')->with('success', 'User deleted.');
    }
}
