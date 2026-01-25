<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    */

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    */

    'prefix' => env('SCOUT_PREFIX', 'thaidevdocs_'),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */

    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            App\Models\Article::class => [
                'searchableAttributes' => [
                    'title',
                    'summary',
                    'content',
                    'tags',
                    'category_name',
                    'topic_name',
                ],
                'filterableAttributes' => [
                    'category_id',
                    'topic_id',
                    'difficulty',
                    'article_type',
                    'status',
                    'tags',
                    'published_at',
                ],
                'sortableAttributes' => [
                    'published_at',
                    'view_count',
                    'created_at',
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                ],
                'typoTolerance' => [
                    'enabled' => true,
                    'minWordSizeForTypos' => [
                        'oneTypo' => 4,
                        'twoTypos' => 8,
                    ],
                ],
            ],
            App\Models\Glossary::class => [
                'searchableAttributes' => [
                    'term',
                    'term_en',
                    'definition',
                ],
                'filterableAttributes' => [
                    'is_approved',
                ],
                'sortableAttributes' => [
                    'term',
                    'view_count',
                ],
            ],
        ],
    ],

];
