<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Article extends Model
{
    use HasFactory, SoftDeletes, HasSlug, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'topic_id',
        'author_id',
        'reviewer_id',
        'title',
        'slug',
        'summary',
        'content',
        'content_html',
        'table_of_contents',
        'difficulty',
        'article_type',
        'reading_time',
        'view_count',
        'bookmark_count',
        'status',
        'is_featured',
        'is_pinned',
        'allow_comments',
        'published_at',
        'last_reviewed_at',
        'meta_title',
        'meta_description',
        'canonical_url',
        'current_version',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'table_of_contents' => 'array',
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
        'allow_comments' => 'boolean',
        'published_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
        'reading_time' => 'integer',
        'view_count' => 'integer',
        'bookmark_count' => 'integer',
        'current_version' => 'integer',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => strip_tags($this->content_html ?? $this->content),
            'category_id' => $this->topic->category_id,
            'category_name' => $this->topic->category->name,
            'topic_id' => $this->topic_id,
            'topic_name' => $this->topic->name,
            'tags' => $this->tags->pluck('name')->toArray(),
            'difficulty' => $this->difficulty,
            'article_type' => $this->article_type,
            'status' => $this->status,
            'published_at' => $this->published_at?->timestamp,
            'view_count' => $this->view_count,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Get the topic this article belongs to.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Get the author of the article.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the reviewer of the article.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the tags for this article.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tags');
    }

    /**
     * Get the code examples for this article.
     */
    public function codeExamples(): HasMany
    {
        return $this->hasMany(CodeExample::class)->orderBy('sort_order');
    }

    /**
     * Get the revisions for this article.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class)->orderByDesc('version');
    }

    /**
     * Get the current revision.
     */
    public function currentRevision()
    {
        return $this->revisions()->where('is_current', true)->first();
    }

    /**
     * Get bookmarks for this article.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get comments for this article.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    /**
     * Get all comments including replies.
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get ratings for this article.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(ArticleRating::class);
    }

    /**
     * Get related articles.
     */
    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(
            Article::class,
            'related_articles',
            'article_id',
            'related_article_id'
        )->withPivot('relation_type', 'sort_order');
    }

    /**
     * Get browser compatibility data.
     */
    public function browserCompatibility(): HasMany
    {
        return $this->hasMany(BrowserCompatibility::class);
    }

    /**
     * Scope a query to only include published articles.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include featured articles.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by difficulty.
     */
    public function scopeDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('article_type', $type);
    }

    /**
     * Get the full path of the article.
     */
    public function getPathAttribute(): string
    {
        return $this->topic->path . '/' . $this->slug;
    }

    /**
     * Get the URL of the article.
     */
    public function getUrlAttribute(): string
    {
        return '/docs/' . $this->path;
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Calculate reading time based on content.
     */
    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content_html ?? $this->content));
        $readingSpeed = 200; // words per minute

        return max(1, (int) ceil($wordCount / $readingSpeed));
    }

    /**
     * Get average rating.
     */
    public function getAverageRatingAttribute(): ?float
    {
        $avg = $this->ratings()->avg('rating');
        return $avg ? round($avg, 1) : null;
    }
}
