<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private Topic $topic;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
        $this->topic = Topic::factory()->create([
            'category_id' => $this->category->id,
        ]);
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_can_list_published_articles(): void
    {
        Article::factory()->count(3)->create([
            'topic_id' => $this->topic->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        Article::factory()->create([
            'topic_id' => $this->topic->id,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_single_article(): void
    {
        $article = Article::factory()->create([
            'topic_id' => $this->topic->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'summary',
                    'content',
                    'topic',
                    'author',
                ],
            ]);
    }

    public function test_cannot_get_draft_article(): void
    {
        $article = Article::factory()->create([
            'topic_id' => $this->topic->id,
            'status' => 'draft',
        ]);

        $response = $this->getJson("/api/v1/articles/{$article->slug}");

        $response->assertStatus(404);
    }

    public function test_admin_can_create_article(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/articles', [
                'title' => 'Test Article',
                'content' => 'This is test content.',
                'topic_id' => $this->topic->id,
                'difficulty' => 'beginner',
                'article_type' => 'guide',
                'status' => 'draft',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                ],
            ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
        ]);
    }

    public function test_admin_can_update_article(): void
    {
        $article = Article::factory()->create([
            'topic_id' => $this->topic->id,
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/articles/{$article->id}", [
                'title' => 'Updated Title',
                'content' => $article->content,
                'topic_id' => $article->topic_id,
                'difficulty' => $article->difficulty,
                'article_type' => $article->article_type,
                'status' => $article->status,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_delete_article(): void
    {
        $article = Article::factory()->create([
            'topic_id' => $this->topic->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/admin/articles/{$article->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_regular_user_cannot_create_article(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/admin/articles', [
                'title' => 'Test Article',
                'content' => 'This is test content.',
                'topic_id' => $this->topic->id,
                'difficulty' => 'beginner',
                'article_type' => 'guide',
                'status' => 'draft',
            ]);

        $response->assertStatus(403);
    }

    public function test_can_filter_articles_by_category(): void
    {
        $otherCategory = Category::factory()->create();
        $otherTopic = Topic::factory()->create([
            'category_id' => $otherCategory->id,
        ]);

        Article::factory()->count(2)->create([
            'topic_id' => $this->topic->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        Article::factory()->count(3)->create([
            'topic_id' => $otherTopic->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/articles?category_id={$this->category->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_articles_by_difficulty(): void
    {
        Article::factory()->count(2)->create([
            'topic_id' => $this->topic->id,
            'difficulty' => 'beginner',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Article::factory()->count(3)->create([
            'topic_id' => $this->topic->id,
            'difficulty' => 'advanced',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/articles?difficulty=beginner');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_article_view_count_increments(): void
    {
        $article = Article::factory()->create([
            'topic_id' => $this->topic->id,
            'status' => 'published',
            'published_at' => now(),
            'view_count' => 0,
        ]);

        $this->getJson("/api/v1/articles/{$article->slug}");
        $this->getJson("/api/v1/articles/{$article->slug}");

        $article->refresh();
        $this->assertEquals(2, $article->view_count);
    }
}
