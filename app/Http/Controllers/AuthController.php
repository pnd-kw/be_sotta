<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $uploadedFile = $request->file('avatar');
            $uploadedAvatar = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
                'folder' => 'avatars'
            ]);
            $validated['avatar'] = $uploadedAvatar['secure_url'];
            $validated['cloudinary_public_id'] = $uploadedAvatar['public_id'];
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role_id' => $validated['role_id'],
            'gender' => $validated['gender'],
            'avatar' => $validated['avatar'] ?? null,
            'cloudinary_public_id' => $validated['cloudinary_public_id'] ?? null,
            'phone_number' => $validated['phone_number']
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id_user' => $user->id_user,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role_name' => $user->role->name ?? null,
                'gender' => $user->gender,
                'avatar' => $user->avatar,
                'phone_number' => $user->phone_number,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
