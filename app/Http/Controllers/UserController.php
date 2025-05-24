<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function update(UpdateUserRequest $request, $id_user)
    {
        // $user = User::where('id_user', $id_user)->firstOrFail();

        /** @var \App\Models\User|null $user  */
        $authUser = Auth::user();
        $user = User::where('id_user', $id_user)->firstOrFail();

        if ($authUser->role->name !== 'superadmin' && $authUser->id_user !== $user->id_user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // $validated = $request->validate([
        //     'name' => 'sometimes|string|max:255',
        //     'email' => 'sometimes|email|unique:users,email,' . $user->id,
        //     'password' => 'sometimes|string|min:6|confirmed',
        // ]);

        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json(['message' => 'User updated successfully']);
    }

    public function delete(Request $request, $id_user)
    {
        $authUser = Auth::user();
        $user = User::where('id_user', $id_user)->firstOrFail();

        if ($authUser->role->name !== "superadmin" && $authUser->id_user !== $user->id_user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Prevent superadmin delete itself
        if ($authUser->id_user === $user->id_user && $authUser->role->name === 'superadmin') {
            return response()->json(['error' => 'Superadmin tidak bisa menghapus dirinya sendiri'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function index(Request $request)
    {
        // $authUser = Auth::user();

        // Only superadmin who can view all users
        // if ($authUser->role->name !== 'superadmin') {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $users = User::with('role')->get();

        return response()->json($users->makeHidden(['password', 'remember_token']));
    }

    public function show($id_user)
    {
        $authUser = Auth::user();
        $user = User::where('id_user', $id_user)->firstOrFail();

        // If not superadmin and logged in users, access will be denied
        if ($authUser->role->name !== 'superadmin' && $authUser->id_user !== $user->id_user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id_user' => $user->id_user,
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'role_name' => $user->role->name ?? null,
        ]);
    }
}
