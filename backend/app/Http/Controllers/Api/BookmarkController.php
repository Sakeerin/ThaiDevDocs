<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Bookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Get user's bookmarks.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->bookmarks()
            ->with([
                'article:id,title,slug,summary,difficulty,reading_time,published_at',
                'article.topic:id,name,slug',
                'collection:id,name,slug',
            ]);

        if ($collectionId = $request->input('collection_id')) {
            $query->where('collection_id', $collectionId);
        }

        $bookmarks = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return $this->successWithPagination($bookmarks->through(fn ($bookmark) => [
            'id' => $bookmark->id,
            'notes' => $bookmark->notes,
            'created_at' => $bookmark->created_at?->toISOString(),
            'article' => $bookmark->article ? [
                'id' => $bookmark->article->id,
                'title' => $bookmark->article->title,
                'slug' => $bookmark->article->slug,
                'summary' => $bookmark->article->summary,
                'difficulty' => $bookmark->article->difficulty,
                'reading_time' => $bookmark->article->reading_time,
                'topic' => $bookmark->article->topic ? [
                    'name' => $bookmark->article->topic->name,
                    'slug' => $bookmark->article->topic->slug,
                ] : null,
            ] : null,
            'collection' => $bookmark->collection ? [
                'id' => $bookmark->collection->id,
                'name' => $bookmark->collection->name,
            ] : null,
        ]));
    }

    /**
     * Add a bookmark.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:articles,id'],
            'collection_id' => ['sometimes', 'nullable', 'exists:collections,id'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        // Verify article is published
        $article = Article::where('id', $validated['article_id'])->published()->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        // Check if collection belongs to user
        if (isset($validated['collection_id'])) {
            $collection = $request->user()->collections()->find($validated['collection_id']);
            if (!$collection) {
                return $this->error('Collection not found.', 'COLLECTION_NOT_FOUND', 404);
            }
        }

        $bookmark = Bookmark::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'article_id' => $validated['article_id'],
            ],
            [
                'collection_id' => $validated['collection_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Update article bookmark count
        $article->increment('bookmark_count');

        return $this->success([
            'bookmark' => [
                'id' => $bookmark->id,
                'article_id' => $bookmark->article_id,
                'collection_id' => $bookmark->collection_id,
            ],
        ], 'Article bookmarked successfully.', 201);
    }

    /**
     * Remove a bookmark.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $bookmark = $request->user()->bookmarks()->find($id);

        if (!$bookmark) {
            return $this->notFound('Bookmark not found.');
        }

        // Update article bookmark count
        if ($bookmark->article) {
            $bookmark->article->decrement('bookmark_count');
        }

        $bookmark->delete();

        return $this->success(null, 'Bookmark removed.');
    }

    /**
     * Check bookmark status for an article.
     */
    public function status(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        $bookmark = $request->user()->bookmarks()
            ->where('article_id', $article->id)
            ->first();

        return $this->success([
            'is_bookmarked' => $bookmark !== null,
            'bookmark_id' => $bookmark?->id,
            'collection_id' => $bookmark?->collection_id,
        ]);
    }
}
