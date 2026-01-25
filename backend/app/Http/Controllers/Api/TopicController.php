<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Get topic by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $topic = Topic::where('slug', $slug)
            ->active()
            ->with(['category' => function ($q) {
                $q->select('id', 'name', 'name_en', 'slug', 'icon', 'color');
            }])
            ->first();

        if (!$topic) {
            return $this->notFound('Topic not found.');
        }

        return $this->success([
            'topic' => $this->formatTopic($topic),
        ]);
    }

    /**
     * Get articles in a topic.
     */
    public function articles(Request $request, string $slug): JsonResponse
    {
        $topic = Topic::where('slug', $slug)->active()->first();

        if (!$topic) {
            return $this->notFound('Topic not found.');
        }

        $query = $topic->articles()
            ->published()
            ->with(['author:id,name,avatar', 'tags:id,name,slug,color']);

        // Apply filters
        if ($difficulty = $request->input('difficulty')) {
            $query->difficulty($difficulty);
        }

        if ($type = $request->input('type')) {
            $query->type($type);
        }

        // Sorting
        $sortBy = $request->input('sort', 'published_at');
        $sortOrder = $request->input('order', 'desc');

        $allowedSorts = ['published_at', 'view_count', 'title', 'reading_time'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $articles = $query->paginate($request->input('per_page', 20));

        return $this->success([
            'topic' => [
                'id' => $topic->id,
                'name' => $topic->name,
                'slug' => $topic->slug,
                'category' => $topic->category ? [
                    'id' => $topic->category->id,
                    'name' => $topic->category->name,
                    'slug' => $topic->category->slug,
                ] : null,
            ],
            'articles' => collect($articles->items())->map(fn ($article) => $this->formatArticleSummary($article)),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'last_page' => $articles->lastPage(),
            ],
        ]);
    }

    /**
     * Format topic for response.
     */
    protected function formatTopic(Topic $topic): array
    {
        return [
            'id' => $topic->id,
            'name' => $topic->name,
            'name_en' => $topic->name_en,
            'slug' => $topic->slug,
            'description' => $topic->description,
            'icon' => $topic->icon,
            'article_count' => $topic->article_count,
            'category' => $topic->category ? [
                'id' => $topic->category->id,
                'name' => $topic->category->name,
                'slug' => $topic->category->slug,
                'icon' => $topic->category->icon,
                'color' => $topic->category->color,
            ] : null,
            'meta_title' => $topic->meta_title,
            'meta_description' => $topic->meta_description,
        ];
    }

    /**
     * Format article summary for list.
     */
    protected function formatArticleSummary($article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'summary' => $article->summary,
            'difficulty' => $article->difficulty,
            'article_type' => $article->article_type,
            'reading_time' => $article->reading_time,
            'view_count' => $article->view_count,
            'is_featured' => $article->is_featured,
            'published_at' => $article->published_at?->toISOString(),
            'author' => $article->author ? [
                'id' => $article->author->id,
                'name' => $article->author->name,
                'avatar' => $article->author->avatar,
            ] : null,
            'tags' => $article->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color,
            ]),
        ];
    }
}
