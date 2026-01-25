<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleRating;
use App\Models\EditSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * List articles with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::published()
            ->with([
                'topic:id,name,slug,category_id',
                'topic.category:id,name,slug',
                'author:id,name,avatar',
                'tags:id,name,slug,color',
            ]);

        // Filters
        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('topic', fn ($q) => $q->where('category_id', $categoryId));
        }

        if ($topicId = $request->input('topic_id')) {
            $query->where('topic_id', $topicId);
        }

        if ($difficulty = $request->input('difficulty')) {
            $query->difficulty($difficulty);
        }

        if ($type = $request->input('type')) {
            $query->type($type);
        }

        if ($tagSlug = $request->input('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
        }

        // Sorting
        $sortBy = $request->input('sort', 'published_at');
        $sortOrder = $request->input('order', 'desc');

        $allowedSorts = ['published_at', 'view_count', 'title', 'reading_time'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $articles = $query->paginate($request->input('per_page', 20));

        return $this->successWithPagination($articles->through(fn ($article) => $this->formatArticleSummary($article)));
    }

    /**
     * Get featured articles.
     */
    public function featured(Request $request): JsonResponse
    {
        $articles = Article::published()
            ->featured()
            ->with(['topic:id,name,slug', 'author:id,name,avatar'])
            ->orderByDesc('published_at')
            ->limit($request->input('limit', 6))
            ->get();

        return $this->success([
            'articles' => $articles->map(fn ($article) => $this->formatArticleSummary($article)),
        ]);
    }

    /**
     * Get popular articles.
     */
    public function popular(Request $request): JsonResponse
    {
        $articles = Article::published()
            ->with(['topic:id,name,slug', 'author:id,name,avatar'])
            ->orderByDesc('view_count')
            ->limit($request->input('limit', 10))
            ->get();

        return $this->success([
            'articles' => $articles->map(fn ($article) => $this->formatArticleSummary($article)),
        ]);
    }

    /**
     * Get recent articles.
     */
    public function recent(Request $request): JsonResponse
    {
        $articles = Article::published()
            ->with(['topic:id,name,slug', 'author:id,name,avatar', 'tags:id,name,slug'])
            ->orderByDesc('published_at')
            ->limit($request->input('limit', 10))
            ->get();

        return $this->success([
            'articles' => $articles->map(fn ($article) => $this->formatArticleSummary($article)),
        ]);
    }

    /**
     * Get single article by slug.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->published()
            ->with([
                'topic:id,name,name_en,slug,category_id,description',
                'topic.category:id,name,name_en,slug',
                'author:id,name,avatar,bio',
                'tags:id,name,slug,color',
                'codeExamples',
            ])
            ->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        // Increment view count (could be queued)
        $article->incrementViewCount();

        // Record reading history if authenticated
        if ($request->user()) {
            $request->user()->readingHistory()->updateOrCreate(
                ['article_id' => $article->id],
                ['last_read_at' => now()]
            );
        }

        return $this->success([
            'article' => $this->formatArticle($article),
        ]);
    }

    /**
     * Get related articles.
     */
    public function related(string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)->published()->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        // Get explicitly related articles
        $related = $article->relatedArticles()
            ->published()
            ->with(['topic:id,name,slug'])
            ->limit(5)
            ->get();

        // If not enough, get articles from same topic
        if ($related->count() < 5) {
            $additionalArticles = Article::where('topic_id', $article->topic_id)
                ->where('id', '!=', $article->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->published()
                ->with(['topic:id,name,slug'])
                ->limit(5 - $related->count())
                ->get();

            $related = $related->merge($additionalArticles);
        }

        return $this->success([
            'articles' => $related->map(fn ($art) => [
                'id' => $art->id,
                'title' => $art->title,
                'slug' => $art->slug,
                'summary' => $art->summary,
                'difficulty' => $art->difficulty,
                'reading_time' => $art->reading_time,
            ]),
        ]);
    }

    /**
     * Get article revisions.
     */
    public function revisions(string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)->published()->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        $revisions = $article->revisions()
            ->with('user:id,name,avatar')
            ->orderByDesc('version')
            ->limit(20)
            ->get();

        return $this->success([
            'revisions' => $revisions->map(fn ($rev) => [
                'id' => $rev->id,
                'version' => $rev->version,
                'change_summary' => $rev->change_summary,
                'is_current' => $rev->is_current,
                'is_major' => $rev->is_major,
                'created_at' => $rev->created_at?->toISOString(),
                'user' => [
                    'id' => $rev->user->id,
                    'name' => $rev->user->name,
                    'avatar' => $rev->user->avatar,
                ],
            ]),
        ]);
    }

    /**
     * Get code examples for article.
     */
    public function examples(string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)->published()->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        $examples = $article->codeExamples()->orderBy('sort_order')->get();

        return $this->success([
            'examples' => $examples->map(fn ($ex) => [
                'id' => $ex->id,
                'title' => $ex->title,
                'description' => $ex->description,
                'language' => $ex->language,
                'code' => $ex->code,
                'output' => $ex->output,
                'output_type' => $ex->output_type,
                'is_runnable' => $ex->is_runnable,
                'is_editable' => $ex->is_editable,
            ]),
        ]);
    }

    /**
     * Get browser compatibility for article.
     */
    public function compatibility(string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->published()
            ->with(['browserCompatibility.browser'])
            ->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        return $this->success([
            'compatibility' => $article->browserCompatibility->map(fn ($compat) => [
                'browser' => [
                    'id' => $compat->browser->id,
                    'name' => $compat->browser->name,
                    'slug' => $compat->browser->slug,
                    'icon' => $compat->browser->icon,
                ],
                'support_status' => $compat->support_status,
                'version_added' => $compat->version_added,
                'version_removed' => $compat->version_removed,
                'notes' => $compat->notes,
            ]),
        ]);
    }

    /**
     * Rate an article.
     */
    public function rate(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)->published()->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'is_helpful' => ['sometimes', 'boolean'],
        ]);

        ArticleRating::updateOrCreate(
            [
                'article_id' => $article->id,
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $validated['rating'],
                'is_helpful' => $validated['is_helpful'] ?? null,
            ]
        );

        return $this->success(null, 'Thank you for your rating!');
    }

    /**
     * Submit feedback for article.
     */
    public function feedback(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)->published()->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        $validated = $request->validate([
            'is_helpful' => ['required', 'boolean'],
            'feedback' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        ArticleRating::updateOrCreate(
            [
                'article_id' => $article->id,
                'user_id' => $request->user()->id,
            ],
            [
                'is_helpful' => $validated['is_helpful'],
                'feedback' => $validated['feedback'] ?? null,
            ]
        );

        return $this->success(null, 'Thank you for your feedback!');
    }

    /**
     * Suggest an edit for article.
     */
    public function suggestEdit(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)->published()->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        $validated = $request->validate([
            'original_content' => ['required', 'string'],
            'suggested_content' => ['required', 'string'],
            'line_start' => ['sometimes', 'integer', 'min:1'],
            'line_end' => ['sometimes', 'integer', 'min:1'],
            'reason' => ['sometimes', 'string', 'max:500'],
        ]);

        $suggestion = EditSuggestion::create([
            'article_id' => $article->id,
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return $this->success([
            'suggestion_id' => $suggestion->id,
        ], 'Edit suggestion submitted for review.', 201);
    }

    /**
     * Format article summary for lists.
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
            'topic' => $article->topic ? [
                'id' => $article->topic->id,
                'name' => $article->topic->name,
                'slug' => $article->topic->slug,
                'category' => $article->topic->category ? [
                    'id' => $article->topic->category->id,
                    'name' => $article->topic->category->name,
                    'slug' => $article->topic->category->slug,
                ] : null,
            ] : null,
            'author' => $article->author ? [
                'id' => $article->author->id,
                'name' => $article->author->name,
                'avatar' => $article->author->avatar,
            ] : null,
            'tags' => $article->tags ? $article->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color,
            ]) : [],
        ];
    }

    /**
     * Format full article details.
     */
    protected function formatArticle(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'summary' => $article->summary,
            'content' => $article->content,
            'content_html' => $article->content_html,
            'table_of_contents' => $article->table_of_contents,
            'difficulty' => $article->difficulty,
            'article_type' => $article->article_type,
            'reading_time' => $article->reading_time,
            'view_count' => $article->view_count,
            'is_featured' => $article->is_featured,
            'allow_comments' => $article->allow_comments,
            'published_at' => $article->published_at?->toISOString(),
            'updated_at' => $article->updated_at?->toISOString(),
            'current_version' => $article->current_version,
            'topic' => $article->topic ? [
                'id' => $article->topic->id,
                'name' => $article->topic->name,
                'name_en' => $article->topic->name_en,
                'slug' => $article->topic->slug,
                'description' => $article->topic->description,
                'category' => $article->topic->category ? [
                    'id' => $article->topic->category->id,
                    'name' => $article->topic->category->name,
                    'name_en' => $article->topic->category->name_en,
                    'slug' => $article->topic->category->slug,
                ] : null,
            ] : null,
            'author' => $article->author ? [
                'id' => $article->author->id,
                'name' => $article->author->name,
                'avatar' => $article->author->avatar,
                'bio' => $article->author->bio,
            ] : null,
            'tags' => $article->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color,
            ]),
            'code_examples' => $article->codeExamples->map(fn ($ex) => [
                'id' => $ex->id,
                'title' => $ex->title,
                'description' => $ex->description,
                'language' => $ex->language,
                'code' => $ex->code,
                'output' => $ex->output,
                'output_type' => $ex->output_type,
                'is_runnable' => $ex->is_runnable,
                'is_editable' => $ex->is_editable,
            ]),
            'meta_title' => $article->meta_title,
            'meta_description' => $article->meta_description,
            'canonical_url' => $article->canonical_url,
        ];
    }
}
