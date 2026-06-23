<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\LearningPath;
use App\Models\LearningPathItem;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningPathTest extends TestCase
{
    use RefreshDatabase;

    private LearningPath $path;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $topic = Topic::factory()->create(['category_id' => $category->id]);
        $article = Article::factory()->create([
            'topic_id' => $topic->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->path = LearningPath::create([
            'author_id' => $admin->id,
            'title' => 'Web Fundamentals',
            'slug' => 'web-fundamentals',
            'description' => 'Learn web basics',
            'difficulty' => 'beginner',
            'estimated_hours' => 10,
            'is_published' => true,
        ]);

        LearningPathItem::create([
            'learning_path_id' => $this->path->id,
            'article_id' => $article->id,
            'sort_order' => 0,
            'is_required' => true,
        ]);
    }

    public function test_can_list_published_learning_paths(): void
    {
        $response = $this->getJson('/api/v1/learning-paths');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_show_learning_path(): void
    {
        $response = $this->getJson("/api/v1/learning-paths/{$this->path->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'web-fundamentals')
            ->assertJsonStructure(['data' => ['items']]);
    }

    public function test_user_can_enroll_in_learning_path(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/v1/learning-paths/{$this->path->slug}/enroll");

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_learning_progress', [
            'user_id' => $user->id,
            'learning_path_id' => $this->path->id,
        ]);
    }
}
