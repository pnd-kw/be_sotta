<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

        // $galleries = Gallery::all();
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
            // 'imageUrl' => 'required|image|max:2048',
            'image' => 'required|image|max:5120',
            'alt' => 'required|string',
            'caption' => 'required|string',
            'tags' => 'required|array',
            // 'mimeType' => 'required|string',
            // 'size' => 'required|integer',
            // 'createdBy' => 'required|string',
            // 'updatedBy' => 'required|string',
        ]);

        $uploadedFile = $request->file('image');

        // $imagePath = $request->file('imageUrl')->store('gallery', 'public');
        // $image = Cloudinary::upload($request->file('imageUrl')->getRealPath(), [
        //     'folder' => 'gallery'
        // ]);
        // $validated['imageUrl'] = $image->getSecurePath();
        // $validated['public_id'] = $image->getPublicId();

        $uploaded = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
            'folder' => 'gallery'
        ]);

        // $gallery = Gallery::create($validated);

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
        Log::info('PATCH request received:', $request->all());
        // Log::info('Request method: ' . $request->method());
        // Log::info('Content-Type: ' . $request->header('Content-Type'));
        // Log::info('Raw content:', [$request->getContent()]);
        // Log::info('Request all:', $request->all());

        $gallery = Gallery::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'published' => 'sometimes|required|boolean',
            // 'imageUrl' => 'sometimes|image|max:2048',
            'image' => 'sometimes|image|max:5120',
            'alt' => 'sometimes|required|string',
            'caption' => 'sometimes|required|string',
            'tags' => 'sometimes|required|array',
            // 'mimeType' => 'sometimes|required|string',
            // 'size' => 'sometimes|required|integer',
            'updatedBy' => 'required|string',
        ]);

        // if ($request->hasFile('imageUrl')) {
        //     // if ($gallery->imageUrl && Storage::disk('public')->exists($gallery->imageUrl)) {
        //     //     Storage::disk('public')->delete($gallery->imageUrl);
        //     // }

        //     // $validated['imageUrl'] = $request->file('imageUrl')->store('gallery', 'public');
        //     Cloudinary::destroy($gallery->public_id);

        //     $image = Cloudinary::upload($request->file('imageUrl')->getRealPath(), [
        //         'folder' => 'gallery'
        //     ]);

        //     $validated['imageUrl'] = $image->getSecurePath();
        //     $validated['public_id'] = $image->getPublicId();
        // }

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

        // if ($gallery->imageUrl && Storage::disk('public')->exists($gallery->imageUrl)) {
        //     Storage::disk('public')->delete($gallery->imageUrl);
        // }

        if ($gallery->public_id) {
            Cloudinary::destroy($gallery->public_id);
        }

        $gallery->delete();

        return response()->json(['message' => 'Gallery item deleted successfully.'], 200);
    }
}
