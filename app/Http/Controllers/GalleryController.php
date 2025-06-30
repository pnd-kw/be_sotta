<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = Gallery::with('categories');

        if ($request->has('search')) {
            $keyword = $request->input('search');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('caption', 'like', "%$keyword%")
                    ->orWhere('alt', 'like', "%$keyword%")
                    ->orWhereHas('category', function ($cat) use ($keyword) {
                        $cat->where('name', 'like', "%$keyword%");
                    });
            });
        }

        if ($request->has('published')) {
            $published = filter_var($request->input('published'), FILTER_VALIDATE_BOOLEAN);
            $query->where('published', $published);
        }

        if ($request->has('category_ids')) {
            $categoryIds = explode(',', $request->input('category_ids'));

            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        $perPage = $request->input('per_page', 8);

        $galleries = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($galleries, 200);
    }

    public function show($id)
    {
        $gallery = Gallery::with('categories')->findOrFail($id);
        return response()->json($gallery, 200);
    }

    public function store(Request $request)
    {
        // $validated = $request->validate([
        //     'name' => 'required|string',
        //     'published' => 'required|boolean',
        //     'image' => 'required|image|max:5120',
        //     'alt' => 'required|string',
        //     'caption' => 'required|string',
        //     'tags' => 'required|array',
        //     'category_id' => 'nullable|exists:categories,id',
        // ]);

        $validated = $request->validate([
            'name' => 'required|string',
            'published' => 'required|boolean',
            'images.*' => 'required|image|max:5120',
            'caption' => 'required|string',
            'tags' => 'required|array',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'createdBy' => 'required|string',
            'updatedBy' => 'required|string',
        ]);

        $images = [];

        // $uploadedFile = $request->file('image');

        // $uploaded = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
        //     'folder' => 'gallery'
        // ]);

        foreach ($request->file('images', []) as $file) {
            try {
                $uploaded = Cloudinary::uploadApi()->upload($file->getRealPath(), ['folder' => 'gallery']);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Upload failed', 'error' => $e->getMessage()], 500);
            }

            $images[] = [
                'imageUrl' => $uploaded['secure_url'],
                'public_id' => $uploaded['public_id'],
                'alt' => $file->getClientOriginalName(),
                'mimeType' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        // $gallery = Gallery::create([
        //     'imageUrl' => $uploaded['secure_url'],
        //     'public_id' => $uploaded['public_id'],
        //     'name' => $request->input('name') ?? $uploadedFile->getClientOriginalName(),
        //     'mimeType' => $uploadedFile->getMimeType(),
        //     'size' => $uploadedFile->getSize(),
        //     'alt' => $request->input('alt'),
        //     'caption' => $request->input('caption'),
        //     'tags' => $request->input('tags'),
        //     'category_id' => $request->input('category_id'),
        //     'published' => filter_var($request->input('published'), FILTER_VALIDATE_BOOLEAN),
        //     'createdBy' => $request->input('createdBy'),
        //     'updatedBy' => $request->input('updatedBy'),
        // ]);

        $gallery = Gallery::create([
            'name' => $request->name,
            'published' => $request->published,
            'caption' => $request->caption,
            'tags' => $request->tags,
            'images' => $images,
            'thumbnailUrl' => $images[0]['imageUrl'] ?? null,
            'createdBy' => $request->createdBy,
            'updatedBy' => $request->updatedBy,
        ]);

        $gallery->categories()->attach($request->categories);

        return response()->json($gallery->load('categories'), 201);
    }

    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        // $validated = $request->validate([
        //     'name' => 'sometimes|required|string',
        //     'published' => 'sometimes|required|boolean',
        //     'image' => 'sometimes|image|max:5120',
        //     'alt' => 'sometimes|required|string',
        //     'caption' => 'sometimes|required|string',
        //     'tags' => 'sometimes|required|array',
        //     'category_id' => 'sometimes|nullable|exists:categories,id',
        //     'updatedBy' => 'required|string',
        // ]);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'published' => 'sometimes|boolean',
            'images.*' => 'sometimes|image|max:5120',
            'images_data' => 'sometimes|array',
            'caption' => 'sometimes|string',
            'tags' => 'sometimes|array',
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
            'updatedBy' => 'required|string',
        ]);

        $existingImages = collect($request->input('images_data', []))->values();
        $newImages = collect();

        // if ($request->hasFile('image')) {
        //     if (!empty($gallery->public_id)) {
        //         Cloudinary::destroy($gallery->public_id);
        //     }

        //     $uploadedFile = $request->file('image');

        //     $uploaded = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
        //         'folder' => 'gallery'
        //     ]);

        //     // Tambahkan informasi file yang baru di update
        //     $validated['imageUrl'] = $uploaded['secure_url'];
        //     $validated['public_id'] = $uploaded['public_id'];
        //     $validated['mimeType'] = $uploadedFile->getMimeType();
        //     $validated['size'] = $uploadedFile->getSize();
        // }

        if ($request->hasFile('images')) {
            foreach ($request->file('images', []) as $file) {
                try {
                    $uploaded = Cloudinary::uploadApi()->upload($file->getRealPath(), ['folder' => 'gallery']);
                } catch (\Exception $e) {
                    return response()->json(['message' => 'Upload failed', 'error' => $e->getMessage()], 500);
                }

                $newImages->push([
                    'imageUrl' => $uploaded['secure_url'],
                    'public_id' => $uploaded['public_id'],
                    'alt' => $file->getClientOriginalName(),
                    'mimeType' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        $finalImages = $existingImages->merge($newImages)->values();

        $oldImages = collect($gallery->images ?? []);

        $deletedImages = $oldImages->filter(function ($old) use ($finalImages) {
            return !$finalImages->contains(function ($current) use ($old) {
                return $current['public_id'] === $old['public_id'];
            });
        });

        foreach ($deletedImages as $img) {
            if (!empty($img['public_id'])) {
                Cloudinary::uploadApi()->destroy($img['public_id']);
            }
        }

        $validated['thumbnailUrl'] = $finalImages[0]['imageUrl'] ?? $gallery->thumbnailUrl;

        if (!isset($validated['thumbnailUrl'])) {
            $validated['thumbnailUrl'] = $gallery->thumbnailUrl;
        }

        $validated['images'] = $finalImages;
        $gallery->update($validated);

        if ($request->has('categories')) {
            $gallery->categories()->sync($request->categories);
        }

        return response()->json($gallery->load('categories'));
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

    // public function deleteImage($galleryId, $publicId)
    // {
    //     $gallery = Gallery::findOrFail($galleryId);
    //     $images = collect($gallery->images);

    //     $image = $images->firstWhere('public_id', $publicId);

    //     if (!$image) {
    //         return response()->json(['message' => 'Image not found.'], 404);
    //     }

    //     Cloudinary::uploadApi()->destroy($publicId);

    //     $gallery->images = $images->reject(fn($img) => $img['public_id'] === $publicId)->values();
    //     $gallery->save();

    //     return response()->json(['message' => 'Image deleted.']);
    // }

    public function delete($id)
    {
        $gallery = Gallery::findOrFail($id);

        // if ($gallery->public_id) {
        //     Cloudinary::uploadApi()->destroy($gallery->public_id);
        // }

        foreach ($gallery->images ?? [] as $image) {
            if (!empty($image['public_id'])) {
                Cloudinary::uploadApi()->destroy($image['public_id']);
            }
        }

        $gallery->delete();

        return response()->json(['message' => 'Gallery item deleted successfully.'], 200);
    }
}
