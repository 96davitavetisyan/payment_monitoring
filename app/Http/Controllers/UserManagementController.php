<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index()
    {
        // Only super-admin can access
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $users = User::with(['roles', 'contracts'])->get();
//dd($users);
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        // Only super-admin can create users
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Password::min(8)],
            'role' => 'required|string|exists:roles,name',
            'contracts' => 'array',
            'contracts.*' => 'exists:contracts,id'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        // Assign contracts
        if (isset($validated['contracts'])) {
            $user->contracts()->sync($validated['contracts']);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load(['roles', 'contracts'])
        ], 201);
    }

    public function show(User $user)
    {
        // Only super-admin can view users
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        return response()->json([
            'success' => true,
            'data' => $user->load(['roles', 'contracts'])
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Only super-admin can update users
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['sometimes', 'nullable', Password::min(8)],
            'role' => 'sometimes|required|string|exists:roles,name',
            'contracts' => 'sometimes|array',
            'contracts.*' => 'exists:contracts,id'
        ]);

        // Update basic info
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password']) && $validated['password']) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Update role
        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        // Update contracts
        if (isset($validated['contracts'])) {
            $user->contracts()->sync($validated['contracts']);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->fresh()->load(['roles', 'contracts'])
        ]);
    }

    public function destroy(User $user)
    {
        // Only super-admin can delete users
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete yourself'
            ], 400);
        }
dd($user);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    public function getRoles()
    {
        // Only super-admin can view roles
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $roles = Role::all();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }
}
