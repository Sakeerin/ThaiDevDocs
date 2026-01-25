<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class LearningPath extends Model
{
    use HasFactory, HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'description',
        'difficulty',
        'estimated_hours',
        'thumbnail',
        'is_featured',
        'is_published',
        'enrollment_count',
        'completion_count',
        'average_rating',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'estimated_hours' => 'integer',
        'enrollment_count' => 'integer',
        'completion_count' => 'integer',
        'average_rating' => 'decimal:2',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the author.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the items in this learning path.
     */
    public function items(): HasMany
    {
        return $this->hasMany(LearningPathItem::class)->orderBy('sort_order');
    }

    /**
     * Get user progress records.
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserLearningProgress::class);
    }

    /**
     * Scope published paths.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope featured paths.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get the article count.
     */
    public function getArticleCountAttribute(): int
    {
        return $this->items()->count();
    }
}
