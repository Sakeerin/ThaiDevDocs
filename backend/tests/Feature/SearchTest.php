<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_requires_minimum_query_length(): void
    {
        $response = $this->getJson('/api/v1/search?q=a');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    public function test_search_returns_matching_articles(): void
    {
        $category = Category::factory()->create();
        $topic = Topic::factory()->create(['category_id' => $category->id]);

        Article::factory()->create([
            'topic_id' => $topic->id,
            'title' => 'JavaScript Async Guide',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/search?q=JavaScript');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'hits',
                    'query',
                    'estimated_total_hits',
                ],
            ]);
    }

    public function test_popular_searches_endpoint(): void
    {
        $response = $this->getJson('/api/v1/search/popular');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
