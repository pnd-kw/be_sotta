<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = Gallery::query();

        if ($request->has('search')) {
            $keyword = $request->input('search');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('caption', 'like', "%$keyword%")
                    ->orWhere('alt', 'like', "%$keyword%");
            });
        }

        $perPage = $request->input('per_page', 8);

        $galleries = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($galleries, 200);
    }

    public function show($id)
    {
        $gallery = Gallery::findOrFail($id);
        return response()->json($gallery, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'published' => 'required|boolean',
            'image' => 'required|image|max:5120',
            'alt' => 'required|string',
            'caption' => 'required|string',
            'tags' => 'required|array',
        ]);

        $uploadedFile = $request->file('image');

        $uploaded = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
            'folder' => 'gallery'
        ]);

        $gallery = Gallery::create([
            'imageUrl' => $uploaded['secure_url'],
            'public_id' => $uploaded['public_id'],
            'name' => $request->input('name') ?? $uploadedFile->getClientOriginalName(),
            'mimeType' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'alt' => $request->input('alt'),
            'caption' => $request->input('caption'),
            'tags' => $request->input('tags'),
            'published' => filter_var($request->input('published'), FILTER_VALIDATE_BOOLEAN),
            'createdBy' => $request->input('createdBy'),
            'updatedBy' => $request->input('updatedBy'),
        ]);

        return response()->json($gallery, 201);
    }

    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'published' => 'sometimes|required|boolean',
            'image' => 'sometimes|image|max:5120',
            'alt' => 'sometimes|required|string',
            'caption' => 'sometimes|required|string',
            'tags' => 'sometimes|required|array',
            'updatedBy' => 'required|string',
        ]);

        if ($request->hasFile('image')) {
            if (!empty($gallery->public_id)) {
                Cloudinary::destroy($gallery->public_id);
            }

            $uploadedFile = $request->file('image');

            $uploaded = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
                'folder' => 'gallery'
            ]);

            // Tambahkan informasi file yang baru di update
            $validated['imageUrl'] = $uploaded['secure_url'];
            $validated['public_id'] = $uploaded['public_id'];
            $validated['mimeType'] = $uploadedFile->getMimeType();
            $validated['size'] = $uploadedFile->getSize();
        }

        $gallery->update($validated);

        return response()->json($gallery, 200);
    }

    public function updatePublished(Request $request, $id)
    {
        $request->validate([
            'published' => 'required|boolean',
            'updatedBy' => 'required|string',
        ]);

        $gallery = Gallery::findOrFail($id);

        $gallery->update([
            'published' => $request->published,
            'updatedBy' => $request->updatedBy,
        ]);

        return response()->json($gallery);
    }

    public function delete($id)
    {
        $gallery = Gallery::findOrFail($id);

        if ($gallery->public_id) {
            Cloudinary::uploadApi()->destroy($gallery->public_id);
        }

        $gallery->delete();

        return response()->json(['message' => 'Gallery item deleted successfully.'], 200);
    }

}
