<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    /**
     * Get user's collections.
     */
    public function index(Request $request): JsonResponse
    {
        $collections = $request->user()->collections()
            ->withCount('bookmarks')
            ->orderBy('name')
            ->get();

        return $this->success([
            'collections' => $collections->map(fn ($collection) => [
                'id' => $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
                'description' => $collection->description,
                'is_public' => $collection->is_public,
                'bookmarks_count' => $collection->bookmarks_count,
                'created_at' => $collection->created_at?->toISOString(),
            ]),
        ]);
    }

    /**
     * Create a new collection.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        $collection = $request->user()->collections()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'description' => $validated['description'] ?? null,
            'is_public' => $validated['is_public'] ?? false,
        ]);

        return $this->success([
            'collection' => [
                'id' => $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
            ],
        ], 'Collection created successfully.', 201);
    }

    /**
     * Get collection details.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $collection = $request->user()->collections()
            ->with(['bookmarks.article:id,title,slug,summary,difficulty'])
            ->find($id);

        if (!$collection) {
            return $this->notFound('Collection not found.');
        }

        return $this->success([
            'collection' => [
                'id' => $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
                'description' => $collection->description,
                'is_public' => $collection->is_public,
                'created_at' => $collection->created_at?->toISOString(),
                'articles' => $collection->bookmarks->map(fn ($bookmark) => [
                    'bookmark_id' => $bookmark->id,
                    'notes' => $bookmark->notes,
                    'article' => $bookmark->article ? [
                        'id' => $bookmark->article->id,
                        'title' => $bookmark->article->title,
                        'slug' => $bookmark->article->slug,
                        'summary' => $bookmark->article->summary,
                        'difficulty' => $bookmark->article->difficulty,
                    ] : null,
                ]),
            ],
        ]);
    }

    /**
     * Update a collection.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $collection = $request->user()->collections()->find($id);

        if (!$collection) {
            return $this->notFound('Collection not found.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        $collection->update($validated);

        return $this->success([
            'collection' => [
                'id' => $collection->id,
                'name' => $collection->name,
                'description' => $collection->description,
                'is_public' => $collection->is_public,
            ],
        ], 'Collection updated.');
    }

    /**
     * Delete a collection.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $collection = $request->user()->collections()->find($id);

        if (!$collection) {
            return $this->notFound('Collection not found.');
        }

        // Remove bookmarks from collection (don't delete them)
        $collection->bookmarks()->update(['collection_id' => null]);

        $collection->delete();

        return $this->success(null, 'Collection deleted.');
    }

    /**
     * Add article to collection.
     */
    public function addArticle(Request $request, int $id): JsonResponse
    {
        $collection = $request->user()->collections()->find($id);

        if (!$collection) {
            return $this->notFound('Collection not found.');
        }

        $validated = $request->validate([
            'article_id' => ['required', 'exists:articles,id'],
        ]);

        // Get or create bookmark
        $bookmark = $request->user()->bookmarks()->updateOrCreate(
            ['article_id' => $validated['article_id']],
            ['collection_id' => $collection->id]
        );

        return $this->success([
            'bookmark_id' => $bookmark->id,
        ], 'Article added to collection.');
    }

    /**
     * Remove article from collection.
     */
    public function removeArticle(Request $request, int $id, int $articleId): JsonResponse
    {
        $collection = $request->user()->collections()->find($id);

        if (!$collection) {
            return $this->notFound('Collection not found.');
        }

        $bookmark = $request->user()->bookmarks()
            ->where('collection_id', $id)
            ->where('article_id', $articleId)
            ->first();

        if ($bookmark) {
            $bookmark->update(['collection_id' => null]);
        }

        return $this->success(null, 'Article removed from collection.');
    }
}
