<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Glossary;
use Illuminate\Console\Command;
use Meilisearch\Client;

class ConfigureMeilisearch extends Command
{
    protected $signature = 'meilisearch:configure';

    protected $description = 'Apply Meilisearch index settings for articles and glossary';

    public function handle(): int
    {
        $host = config('scout.meilisearch.host');
        $key = config('scout.meilisearch.key');

        if (!$host) {
            $this->error('MEILISEARCH_HOST is not configured.');

            return Command::FAILURE;
        }

        $client = new Client($host, $key);
        $prefix = config('scout.prefix', '');

        $this->configureArticlesIndex($client, $prefix . (new Article)->searchableAs());
        $this->configureGlossaryIndex($client, $prefix . (new Glossary)->searchableAs());

        $this->info('Meilisearch index settings applied successfully.');

        return Command::SUCCESS;
    }

    protected function configureArticlesIndex(Client $client, string $indexName): void
    {
        $client->index($indexName)->updateSettings([
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
                'view_count:desc',
            ],
            'typoTolerance' => [
                'enabled' => true,
                'minWordSizeForTypos' => [
                    'oneTypo' => 4,
                    'twoTypos' => 8,
                ],
            ],
        ]);

        $this->line("Configured index: {$indexName}");
    }

    protected function configureGlossaryIndex(Client $client, string $indexName): void
    {
        $client->index($indexName)->updateSettings([
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
        ]);

        $this->line("Configured index: {$indexName}");
    }
}
