<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * List media files.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Media::with('user:id,name');

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('mime_type', 'like', $type . '/%');
        }

        // Search by filename
        if ($search = $request->input('search')) {
            $query->where('original_filename', 'like', "%{$search}%");
        }

        $media = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 30));

        return response()->json([
            'success' => true,
            'data' => collect($media->items())->map(fn ($m) => [
                'id' => $m->id,
                'filename' => $m->filename,
                'original_filename' => $m->original_filename,
                'url' => $m->url,
                'mime_type' => $m->mime_type,
                'size' => $m->size,
                'human_size' => $m->human_size,
                'width' => $m->width,
                'height' => $m->height,
                'alt_text' => $m->alt_text,
                'created_at' => $m->created_at?->toISOString(),
                'user' => $m->user ? [
                    'id' => $m->user->id,
                    'name' => $m->user->name,
                ] : null,
            ]),
            'meta' => [
                'current_page' => $media->currentPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
                'last_page' => $media->lastPage(),
            ],
        ]);
    }

    /**
     * Upload media file.
     */
    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
            'alt_text' => ['sometimes', 'nullable', 'string', 'max:255'],
            'caption' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Generate unique filename
        $filename = Str::uuid() . '.' . $extension;
        $folder = date('Y/m');
        $path = $folder . '/' . $filename;

        // Store file
        $disk = config('filesystems.default', 'public');
        Storage::disk($disk)->put($path, file_get_contents($file));

        // Get dimensions if image
        $width = null;
        $height = null;
        if (str_starts_with($mimeType, 'image/')) {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        $media = Media::create([
            'user_id' => $request->user()->id,
            'filename' => $filename,
            'original_filename' => $originalName,
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $mimeType,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'alt_text' => $validated['alt_text'] ?? null,
            'caption' => $validated['caption'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $media->id,
                'url' => $media->url,
                'filename' => $media->original_filename,
            ],
            'message' => 'File uploaded successfully.',
        ], 201);
    }

    /**
     * Delete media file.
     */
    public function destroy(int $id): JsonResponse
    {
        $media = Media::find($id);

        if (!$media) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Media not found.'],
            ], 404);
        }

        // Delete from storage
        Storage::disk($media->disk)->delete($media->path);

        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media deleted successfully.',
        ]);
    }
}
