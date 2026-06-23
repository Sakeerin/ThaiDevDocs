<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
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
            'allow_comments' => true,
        ]);
    }

    public function test_can_list_approved_comments(): void
    {
        Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'content' => 'Great article!',
            'is_approved' => true,
        ]);

        $response = $this->getJson("/api/v1/articles/{$this->article->slug}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_authenticated_user_can_post_comment(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/articles/{$this->article->slug}/comments", [
                'content' => 'Thanks for this tutorial!',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_guest_cannot_post_comment(): void
    {
        $response = $this->postJson("/api/v1/articles/{$this->article->slug}/comments", [
            'content' => 'Guest comment',
        ]);

        $response->assertStatus(401);
    }
}
