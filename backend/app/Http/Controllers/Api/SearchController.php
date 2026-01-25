<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SearchLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search articles.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
            'category' => ['sometimes', 'string'],
            'difficulty' => ['sometimes', 'in:beginner,intermediate,advanced'],
            'type' => ['sometimes', 'in:guide,reference,tutorial,example,glossary'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'sort' => ['sometimes', 'in:relevance,published_at,view_count'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'offset' => ['sometimes', 'integer', 'min:0'],
        ]);

        $query = $validated['q'];
        $limit = $validated['limit'] ?? 20;
        $offset = $validated['offset'] ?? 0;

        // Build search options
        $searchOptions = [
            'limit' => $limit,
            'offset' => $offset,
            'attributesToHighlight' => ['title', 'summary', 'content'],
            'highlightPreTag' => '<mark>',
            'highlightPostTag' => '</mark>',
            'attributesToCrop' => ['content'],
            'cropLength' => 200,
        ];

        // Build filters
        $filters = ['status = "published"'];

        if (isset($validated['category'])) {
            $filters[] = "category_name = \"{$validated['category']}\"";
        }

        if (isset($validated['difficulty'])) {
            $filters[] = "difficulty = \"{$validated['difficulty']}\"";
        }

        if (isset($validated['type'])) {
            $filters[] = "article_type = \"{$validated['type']}\"";
        }

        if (!empty($validated['tags'])) {
            $tagFilters = collect($validated['tags'])
                ->map(fn ($tag) => "tags = \"{$tag}\"")
                ->join(' OR ');
            $filters[] = "({$tagFilters})";
        }

        $searchOptions['filter'] = implode(' AND ', $filters);

        // Sorting
        if (isset($validated['sort']) && $validated['sort'] !== 'relevance') {
            $searchOptions['sort'] = [$validated['sort'] . ':desc'];
        }

        try {
            $results = Article::search($query)->options($searchOptions)->raw();

            // Log search query
            SearchLog::create([
                'user_id' => $request->user()?->id,
                'query' => $query,
                'filters' => array_filter($validated, fn ($key) => $key !== 'q', ARRAY_FILTER_USE_KEY),
                'results_count' => $results['estimatedTotalHits'] ?? 0,
                'session_id' => $request->session()->getId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->success([
                'hits' => collect($results['hits'])->map(fn ($hit) => [
                    'id' => $hit['id'],
                    'title' => $hit['title'],
                    'slug' => $hit['slug'] ?? null,
                    'summary' => $hit['summary'] ?? null,
                    'difficulty' => $hit['difficulty'] ?? null,
                    'article_type' => $hit['article_type'] ?? null,
                    'category_name' => $hit['category_name'] ?? null,
                    'topic_name' => $hit['topic_name'] ?? null,
                    'tags' => $hit['tags'] ?? [],
                    '_formatted' => $hit['_formatted'] ?? null,
                ]),
                'query' => $query,
                'processing_time_ms' => $results['processingTimeMs'],
                'estimated_total_hits' => $results['estimatedTotalHits'],
            ]);
        } catch (\Exception $e) {
            // Fallback to database search if Meilisearch fails
            $articles = Article::published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('summary', 'like', "%{$query}%")
                      ->orWhere('content', 'like', "%{$query}%");
                })
                ->with(['topic:id,name,slug', 'tags:id,name,slug'])
                ->limit($limit)
                ->get();

            return $this->success([
                'hits' => $articles->map(fn ($article) => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'summary' => $article->summary,
                    'difficulty' => $article->difficulty,
                    'article_type' => $article->article_type,
                    'topic_name' => $article->topic?->name,
                ]),
                'query' => $query,
                'estimated_total_hits' => $articles->count(),
            ]);
        }
    }

    /**
     * Get search suggestions/autocomplete.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        $query = $validated['q'];

        try {
            $results = Article::search($query)
                ->options([
                    'limit' => 5,
                    'attributesToRetrieve' => ['id', 'title', 'slug', 'topic_name'],
                ])
                ->raw();

            return $this->success([
                'suggestions' => collect($results['hits'])->map(fn ($hit) => [
                    'id' => $hit['id'],
                    'title' => $hit['title'],
                    'slug' => $hit['slug'] ?? null,
                    'topic_name' => $hit['topic_name'] ?? null,
                ]),
            ]);
        } catch (\Exception $e) {
            $articles = Article::published()
                ->where('title', 'like', "%{$query}%")
                ->select('id', 'title', 'slug')
                ->limit(5)
                ->get();

            return $this->success([
                'suggestions' => $articles,
            ]);
        }
    }

    /**
     * Get popular search queries.
     */
    public function popular(Request $request): JsonResponse
    {
        $popularQueries = SearchLog::select('query')
            ->selectRaw('COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->where('results_count', '>', 0)
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($request->input('limit', 10))
            ->get();

        return $this->success([
            'queries' => $popularQueries->map(fn ($item) => [
                'query' => $item->query,
                'count' => $item->count,
            ]),
        ]);
    }
}
