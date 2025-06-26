<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function update(UpdateUserRequest $request, $id_user)
    {

        /** @var \App\Models\User|null $user  */
        $authUser = Auth::user();
        $user = User::where('id_user', $id_user)->firstOrFail();

        if ($authUser->role->name !== 'superadmin' && $authUser->id_user !== $user->id_user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        // Tangani password hanya jika dikirim dan tidak kosong
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->input('password'));
        } else {
            unset($validated['password']);
        }

        // Upload avatar jika ada file baru
        if ($request->hasFile('avatar')) {
            Cloudinary::destroy($user->cloudinary_public_id);

            $uploadedFile = $request->file('avatar');
            $uploadedAvatar = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
                'folder' => 'avatars'
            ]);
            $validated['avatar'] = $uploadedAvatar['secure_url'];
            $validated['cloudinary_public_id'] = $uploadedAvatar['public_id'];
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

        if ($user->cloudinary_public_id) {
            Cloudinary::uploadApi()->destroy($user->cloudinary_public_id);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function index(Request $request)
    {
        $query = User::with('role');

        if ($keyword = $request->input('search')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage)->appends($request->all());

        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'id_user' => $user->id_user,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'gender' => $user->gender,
                'avatar' => $user->avatar,
                'cloudinary_public_id' => $user->cloudinary_public_id,
                'phone_number' => $user->phone_number,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'deleted_at' => $user->deleted_at,
                'role' => [
                    'id' => $user->role->id ?? null,
                    'name' => $user->role->name ?? null
                ],

            ];
        });

        return response()->json($users);
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
            'role' => [
                'id' => $user->role_id,
                'name' => $user->role->name ?? null,
            ],
            'gender' => ['key' => $user->gender, 'value' => User::GENDER_OPTIONS[$user->gender] ?? null],
            'avatar' => $user->avatar,
            'phone_number' => $user->phone_number,
        ]);
    }
}
