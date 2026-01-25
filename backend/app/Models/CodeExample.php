<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeExample extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'article_id',
        'title',
        'description',
        'language',
        'code',
        'output',
        'output_type',
        'is_runnable',
        'is_editable',
        'sandbox_config',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_runnable' => 'boolean',
        'is_editable' => 'boolean',
        'sandbox_config' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the article this example belongs to.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
