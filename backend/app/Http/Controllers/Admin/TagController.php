<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * List all tags.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::withCount('articles');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $tags = $query->orderBy('name')->paginate($request->input('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $tags->items(),
            'meta' => [
                'current_page' => $tags->currentPage(),
                'per_page' => $tags->perPage(),
                'total' => $tags->total(),
                'last_page' => $tags->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new tag.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:tags,name'],
            'description' => ['sometimes', 'nullable', 'string'],
            'color' => ['sometimes', 'nullable', 'string', 'max:7'],
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'success' => true,
            'data' => $tag,
            'message' => 'Tag created successfully.',
        ], 201);
    }

    /**
     * Update a tag.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Tag not found.'],
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100', 'unique:tags,name,' . $id],
            'description' => ['sometimes', 'nullable', 'string'],
            'color' => ['sometimes', 'nullable', 'string', 'max:7'],
        ]);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'data' => $tag->fresh(),
            'message' => 'Tag updated successfully.',
        ]);
    }

    /**
     * Delete a tag.
     */
    public function destroy(int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Tag not found.'],
            ], 404);
        }

        // Detach from all articles
        $tag->articles()->detach();

        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully.',
        ]);
    }

    /**
     * Merge tags.
     */
    public function merge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_ids' => ['required', 'array', 'min:1'],
            'source_ids.*' => ['exists:tags,id'],
            'target_id' => ['required', 'exists:tags,id'],
        ]);

        $targetTag = Tag::find($validated['target_id']);
        $sourceIds = array_diff($validated['source_ids'], [$validated['target_id']]);

        if (empty($sourceIds)) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INVALID_MERGE', 'message' => 'No valid source tags to merge.'],
            ], 400);
        }

        // Get all articles from source tags
        $articleIds = \DB::table('article_tags')
            ->whereIn('tag_id', $sourceIds)
            ->pluck('article_id')
            ->unique();

        // Attach to target tag (ignore duplicates)
        foreach ($articleIds as $articleId) {
            \DB::table('article_tags')->insertOrIgnore([
                'article_id' => $articleId,
                'tag_id' => $validated['target_id'],
            ]);
        }

        // Delete source tags
        Tag::whereIn('id', $sourceIds)->delete();

        // Update usage count
        $targetTag->updateUsageCount();

        return response()->json([
            'success' => true,
            'message' => 'Tags merged successfully.',
            'data' => [
                'merged_count' => count($sourceIds),
                'target_tag' => $targetTag->fresh(),
            ],
        ]);
    }
}
