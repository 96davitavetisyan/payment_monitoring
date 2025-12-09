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

        $roles = Role::with('permissions')->get();

        // Add users count manually
        $roles->each(function ($role) {
            $role->users_count = User::role($role->name)->count();
        });

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    public function getPermissions()
    {
        // Only super-admin can view permissions
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $permissions = \Spatie\Permission\Models\Permission::all();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function createRole(Request $request)
    {
        // Only super-admin can create roles
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500'
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        // If description is provided, we might need to add it to metadata or ignore it
        // Spatie's Role doesn't have description field by default

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    public function updateRole(Request $request, Role $role)
    {
        // Only super-admin can update roles
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500'
        ]);

        $role->update([
            'name' => $validated['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    public function deleteRole(Role $role)
    {
        // Only super-admin can delete roles
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        // Check if role has users
        $usersCount = User::role($role->name)->count();
        if ($usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role with assigned users'
            ], 400);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        // Only super-admin can update role permissions
//        if (!auth()->user()->hasRole('super-admin')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Sync permissions to the role
        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'success' => true,
            'message' => 'Role permissions updated successfully',
            'data' => $role->load('permissions')
        ]);
    }
}
