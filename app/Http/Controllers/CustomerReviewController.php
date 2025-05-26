<?php

namespace App\Http\Controllers;

use App\Models\CustomerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerReview::query();

        if ($request->has('search')) {
            $keyword = $request->input('search');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "$keyword")
                  ->orWhere('message', 'like', "$keyword")
                  ->orWhere('instansi', 'like', "$keyword");
            });
        }

        $perPage = $request->input('per_page', 8);

        $review = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($review, 200);
    }

    public function show($id)
    {
        $review = CustomerReview::findOrFail($id);
        return response()->json($review, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'message' => 'required|string',
            'instansi' => 'required|string',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $review = CustomerReview::create($validated);

        return response()->json($review, 201);
    }

    public function destroy(Request $request, $id)
    {
        $authUser = Auth::user();

        if ($authUser->role->name !== "superadmin") {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $review = CustomerReview::findOrFail($id);

        if ($review->avatar) {
            Storage::disk('public')->delete($review->avatar);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }
}
