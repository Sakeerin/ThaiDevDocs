<?php

namespace App\Http\Controllers\Api;

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
        $query = Tag::query();

        if ($request->boolean('popular')) {
            $query->popular()->where('usage_count', '>', 0);
        }

        $tags = $query->orderBy('name')->get();

        return $this->success([
            'tags' => $tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
                'color' => $tag->color,
                'usage_count' => $tag->usage_count,
            ]),
        ]);
    }

    /**
     * Get tag by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)->first();

        if (!$tag) {
            return $this->notFound('Tag not found.');
        }

        return $this->success([
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
                'color' => $tag->color,
                'usage_count' => $tag->usage_count,
            ],
        ]);
    }

    /**
     * Get articles with tag.
     */
    public function articles(Request $request, string $slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)->first();

        if (!$tag) {
            return $this->notFound('Tag not found.');
        }

        $articles = $tag->publishedArticles()
            ->with([
                'topic:id,name,slug',
                'author:id,name,avatar',
            ])
            ->orderByDesc('published_at')
            ->paginate($request->input('per_page', 20));

        return $this->success([
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color,
            ],
            'articles' => collect($articles->items())->map(fn ($article) => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'summary' => $article->summary,
                'difficulty' => $article->difficulty,
                'reading_time' => $article->reading_time,
                'published_at' => $article->published_at?->toISOString(),
                'topic' => $article->topic ? [
                    'id' => $article->topic->id,
                    'name' => $article->topic->name,
                    'slug' => $article->topic->slug,
                ] : null,
                'author' => $article->author ? [
                    'id' => $article->author->id,
                    'name' => $article->author->name,
                    'avatar' => $article->author->avatar,
                ] : null,
            ]),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'last_page' => $articles->lastPage(),
            ],
        ]);
    }
}
