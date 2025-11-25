<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get list of all users with their roles
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('roles:id,name')
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
}
