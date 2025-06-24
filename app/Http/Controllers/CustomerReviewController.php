<?php

namespace App\Http\Controllers;

use App\Models\CustomerReview;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerReviewController extends Controller
{
    public function index(Request $request)
    {
        $userToken = $request->input('token');
        $authUser = Auth::user();

        $query = CustomerReview::query();

        if ($keyword = $request->input('search')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('message', 'like', "%$keyword%")
                    ->orWhere('instansi', 'like', "%$keyword%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $reviews = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $reviews->getCollection()->transform(function ($review) use ($userToken, $authUser) {
            $isOwner = $userToken && $review->token === $userToken && $review->token_expires_at > now();
            $isSuperadmin = $authUser && $authUser->role->name === 'superadmin';

            return [
                'id' => $review->id,
                'name' => $review->name,
                'message' => $review->message,
                'instansi' => $review->instansi,
                'gender' => $review->gender,
                'avatar' => $review->avatar,
                'token' => $review->token,
                'created_at' => $review->created_at,
                'can_edit' => $isOwner,
                'can_delete' => $isOwner || $isSuperadmin,
            ];
        });

        return response()->json($reviews, 200);
    }

    public function show(Request $request, $id)
    {
        $review = CustomerReview::findOrFail($id);
        $authUser = Auth::user();

        $request->validate([
            'token' => 'nullable|string',
        ]);

        $isOwner = $request->token && $review->token === $request->token && $review->token_expires_at > now();
        $isSuperadmin = $authUser && $authUser->role->name === 'superadmin';

        if (! $isOwner && ! $isSuperadmin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $review->toArray();
        $data['can_edit'] = $isOwner;
        $data['can_delete'] = $isOwner || $isSuperadmin;

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'message' => 'required|string',
            'instansi' => 'required|string',
            'gender' => 'required|string|in:male,female',
            'token' => 'required|string',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $existing = CustomerReview::where('token', $request->token)
            ->where('token_expires_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Review already submitted with this token'
            ], 409);
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $validated['token'] = $request->token;
        $validated['token_expires_at'] = Carbon::now()->addDay();

        $review = CustomerReview::create($validated);

        return response()->json($review, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'message' => 'required|string',
            'instansi' => 'sometimes|string',
            'gender' => 'sometimes|string|in:male,female',
            'token' => 'required|string',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $review = CustomerReview::findOrFail($id);

        if ($review->token !== $request->token || $review->token_expires_at < now()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->hasFile('avatar')) {
            if ($review->avatar) {
                Storage::disk('public')->delete($review->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        } else {
            unset($validated['avatar']);
        }

        $review->update($validated);

        return response()->json($review, 200);
    }

    public function destroy(Request $request, $id)
    {
        $review = CustomerReview::findOrFail($id);

        // Cek jika user login dan superadmin
        $authUser = Auth::user();
        if ($authUser && $authUser->role->name === "superadmin") {
            // Superadmin boleh hapus tanpa token
            if ($review->avatar) {
                Storage::disk('public')->delete($review->avatar);
            }

            $review->delete();
            return response()->json(['message' => 'Review deleted by superadmin']);
        }

        // Jika bukan superadmin, validasi token
        $request->validate([
            'token' => 'required|string',
        ]);

        if ($review->token !== $request->token || $review->token_expires_at < now()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($review->avatar) {
            Storage::disk('public')->delete($review->avatar);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }
}
