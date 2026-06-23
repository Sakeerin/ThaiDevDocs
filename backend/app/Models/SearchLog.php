<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'query',
        'filters',
        'results_count',
        'clicked_article_id',
        'session_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'filters' => 'array',
        'results_count' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clickedArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'clicked_article_id');
    }
}
