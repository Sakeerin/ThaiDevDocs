<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Glossary extends Model
{
    use HasFactory, HasSlug, Searchable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glossary';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'term',
        'term_en',
        'slug',
        'definition',
        'definition_short',
        'pronunciation',
        'etymology',
        'related_terms',
        'external_links',
        'is_approved',
        'view_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'related_terms' => 'array',
        'external_links' => 'array',
        'is_approved' => 'boolean',
        'view_count' => 'integer',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('term_en')
            ->saveSlugsTo('slug');
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
            'term' => $this->term,
            'term_en' => $this->term_en,
            'definition' => $this->definition,
            'is_approved' => $this->is_approved,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->is_approved;
    }

    /**
     * Scope approved terms.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
