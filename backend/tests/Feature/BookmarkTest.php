<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        $category = Category::factory()->create();
        $topic = Topic::factory()->create(['category_id' => $category->id]);
        $this->article = Article::factory()->create([
            'topic_id' => $topic->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_user_can_bookmark_article(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookmarks', [
                'article_id' => $this->article->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
        ]);
    }

    public function test_user_cannot_bookmark_same_article_twice(): void
    {
        Bookmark::factory()->create([
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookmarks', [
                'article_id' => $this->article->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_user_can_list_bookmarks(): void
    {
        Bookmark::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/bookmarks');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_remove_bookmark(): void
    {
        $bookmark = Bookmark::factory()->create([
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/bookmarks/{$bookmark->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('bookmarks', [
            'id' => $bookmark->id,
        ]);
    }

    public function test_user_can_create_collection(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/collections', [
                'name' => 'My Collection',
                'description' => 'A test collection',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('collections', [
            'user_id' => $this->user->id,
            'name' => 'My Collection',
        ]);
    }

    public function test_user_can_add_bookmark_to_collection(): void
    {
        $collection = Collection::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookmarks', [
                'article_id' => $this->article->id,
                'collection_id' => $collection->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
            'collection_id' => $collection->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_bookmark(): void
    {
        $response = $this->postJson('/api/v1/bookmarks', [
            'article_id' => $this->article->id,
        ]);

        $response->assertStatus(401);
    }
}
