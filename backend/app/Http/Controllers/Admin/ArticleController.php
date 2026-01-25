<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\Contribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\MarkdownConverter;

class ArticleController extends Controller
{
    /**
     * List all articles with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::with([
            'topic:id,name,slug,category_id',
            'topic.category:id,name,slug',
            'author:id,name,avatar',
            'tags:id,name,slug',
        ])->withTrashed();

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Category filter
        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('topic', fn ($q) => $q->where('category_id', $categoryId));
        }

        // Topic filter
        if ($topicId = $request->input('topic_id')) {
            $query->where('topic_id', $topicId);
        }

        // Author filter
        if ($authorId = $request->input('author_id')) {
            $query->where('author_id', $authorId);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $articles = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $articles->items(),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'last_page' => $articles->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new article.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic_id' => ['required', 'exists:topics,id'],
            'title' => ['required', 'string', 'max:500'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'difficulty' => ['sometimes', 'in:beginner,intermediate,advanced'],
            'article_type' => ['sometimes', 'in:guide,reference,tutorial,example,glossary'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['exists:tags,id'],
            'is_featured' => ['sometimes', 'boolean'],
            'allow_comments' => ['sometimes', 'boolean'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        // Render markdown to HTML
        $contentHtml = $this->renderMarkdown($validated['content']);

        // Generate table of contents
        $toc = $this->generateTableOfContents($contentHtml);

        $article = Article::create([
            'topic_id' => $validated['topic_id'],
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'content_html' => $contentHtml,
            'table_of_contents' => $toc,
            'difficulty' => $validated['difficulty'] ?? 'beginner',
            'article_type' => $validated['article_type'] ?? 'guide',
            'is_featured' => $validated['is_featured'] ?? false,
            'allow_comments' => $validated['allow_comments'] ?? true,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'reading_time' => $this->calculateReadingTime($validated['content']),
            'status' => 'draft',
        ]);

        // Attach tags
        if (!empty($validated['tags'])) {
            $article->tags()->attach($validated['tags']);
        }

        // Create initial revision
        ArticleRevision::create([
            'article_id' => $article->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'content_html' => $contentHtml,
            'change_summary' => 'Initial version',
            'version' => 1,
            'is_current' => true,
        ]);

        // Record contribution
        Contribution::create([
            'user_id' => $request->user()->id,
            'article_id' => $article->id,
            'type' => 'create',
            'points' => 50,
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $article->load(['topic', 'author', 'tags']),
            'message' => 'Article created successfully.',
        ], 201);
    }

    /**
     * Get article details.
     */
    public function show(int $id): JsonResponse
    {
        $article = Article::with([
            'topic.category',
            'author',
            'reviewer',
            'tags',
            'codeExamples',
            'revisions' => fn ($q) => $q->orderByDesc('version')->limit(10),
        ])->withTrashed()->find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Article not found.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }

    /**
     * Update an article.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Article not found.'],
            ], 404);
        }

        $validated = $request->validate([
            'topic_id' => ['sometimes', 'exists:topics,id'],
            'title' => ['sometimes', 'string', 'max:500'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'content' => ['sometimes', 'string'],
            'difficulty' => ['sometimes', 'in:beginner,intermediate,advanced'],
            'article_type' => ['sometimes', 'in:guide,reference,tutorial,example,glossary'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['exists:tags,id'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_pinned' => ['sometimes', 'boolean'],
            'allow_comments' => ['sometimes', 'boolean'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'change_summary' => ['sometimes', 'string', 'max:500'],
        ]);

        // Check if content changed
        $contentChanged = isset($validated['content']) && $validated['content'] !== $article->content;

        if ($contentChanged) {
            $validated['content_html'] = $this->renderMarkdown($validated['content']);
            $validated['table_of_contents'] = $this->generateTableOfContents($validated['content_html']);
            $validated['reading_time'] = $this->calculateReadingTime($validated['content']);
        }

        $article->update($validated);

        // Sync tags
        if (isset($validated['tags'])) {
            $article->tags()->sync($validated['tags']);
        }

        // Create new revision if content changed
        if ($contentChanged) {
            $newVersion = $article->current_version + 1;

            ArticleRevision::where('article_id', $article->id)->update(['is_current' => false]);

            ArticleRevision::create([
                'article_id' => $article->id,
                'user_id' => $request->user()->id,
                'content' => $validated['content'],
                'content_html' => $validated['content_html'],
                'change_summary' => $validated['change_summary'] ?? null,
                'version' => $newVersion,
                'is_current' => true,
            ]);

            $article->update(['current_version' => $newVersion]);

            // Record contribution
            Contribution::create([
                'user_id' => $request->user()->id,
                'article_id' => $article->id,
                'type' => 'edit',
                'points' => 20,
                'status' => 'approved',
                'reviewed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $article->fresh(['topic', 'author', 'tags']),
            'message' => 'Article updated successfully.',
        ]);
    }

    /**
     * Delete an article.
     */
    public function destroy(int $id): JsonResponse
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Article not found.'],
            ], 404);
        }

        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article deleted successfully.',
        ]);
    }

    /**
     * Update article status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Article not found.'],
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:draft,pending_review,published,archived'],
        ]);

        $article->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'data' => ['status' => $article->status],
            'message' => 'Status updated.',
        ]);
    }

    /**
     * Publish an article.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Article not found.'],
            ], 404);
        }

        $article->update([
            'status' => 'published',
            'published_at' => now(),
            'reviewer_id' => $request->user()->id,
            'last_reviewed_at' => now(),
        ]);

        // Update topic article count
        $article->topic?->updateArticleCount();

        return response()->json([
            'success' => true,
            'message' => 'Article published successfully.',
        ]);
    }

    /**
     * Duplicate an article.
     */
    public function duplicate(int $id): JsonResponse
    {
        $article = Article::with('tags')->find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Article not found.'],
            ], 404);
        }

        $newArticle = $article->replicate();
        $newArticle->title = $article->title . ' (Copy)';
        $newArticle->slug = null; // Will be regenerated
        $newArticle->status = 'draft';
        $newArticle->view_count = 0;
        $newArticle->bookmark_count = 0;
        $newArticle->published_at = null;
        $newArticle->current_version = 1;
        $newArticle->save();

        // Copy tags
        $newArticle->tags()->attach($article->tags->pluck('id'));

        return response()->json([
            'success' => true,
            'data' => $newArticle,
            'message' => 'Article duplicated successfully.',
        ], 201);
    }

    /**
     * Render markdown to HTML.
     */
    protected function renderMarkdown(string $markdown): string
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new TableExtension());
        $environment->addExtension(new AutolinkExtension());

        $converter = new MarkdownConverter($environment);

        return $converter->convert($markdown)->getContent();
    }

    /**
     * Generate table of contents from HTML.
     */
    protected function generateTableOfContents(string $html): array
    {
        $toc = [];
        preg_match_all('/<h([2-4])[^>]*id=["\']([^"\']+)["\'][^>]*>(.*?)<\/h\1>/i', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $toc[] = [
                'level' => (int) $match[1],
                'id' => $match[2],
                'text' => strip_tags($match[3]),
            ];
        }

        return $toc;
    }

    /**
     * Calculate reading time.
     */
    protected function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, (int) ceil($wordCount / 200));
    }
}
