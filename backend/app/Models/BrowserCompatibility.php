<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrowserCompatibility extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'article_id',
        'browser_id',
        'support_status',
        'version_added',
        'version_removed',
        'notes',
        'flags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'flags' => 'array',
    ];

    /**
     * Get the article.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the browser.
     */
    public function browser(): BelongsTo
    {
        return $this->belongsTo(Browser::class);
    }
}
