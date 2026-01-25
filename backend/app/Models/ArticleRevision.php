<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleRevision extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'article_id',
        'user_id',
        'content',
        'content_html',
        'change_summary',
        'version',
        'is_current',
        'is_major',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_current' => 'boolean',
        'is_major' => 'boolean',
        'version' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the article this revision belongs to.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the user who created this revision.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark this revision as current.
     */
    public function markAsCurrent(): void
    {
        // Unmark all other revisions as current
        $this->article->revisions()->update(['is_current' => false]);
        
        // Mark this one as current
        $this->update(['is_current' => true]);
    }
}
