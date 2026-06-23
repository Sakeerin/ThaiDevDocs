<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Glossary;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
    }

    public function test_admin_can_list_glossary_terms(): void
    {
        Glossary::create([
            'term' => 'ตัวแปร',
            'definition' => 'ที่เก็บข้อมูล',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/glossary');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_create_glossary_term(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/glossary', [
                'term' => 'ฟังก์ชัน',
                'definition' => 'กลุ่มคำสั่งที่ทำงานเฉพาะอย่าง',
                'is_approved' => true,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('glossary', ['term' => 'ฟังก์ชัน']);
    }

    public function test_regular_user_cannot_access_admin_glossary(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/admin/glossary');

        $response->assertStatus(403);
    }

    public function test_admin_can_bulk_update_articles(): void
    {
        $category = Category::factory()->create();
        $topic = Topic::factory()->create(['category_id' => $category->id]);
        $article = \App\Models\Article::factory()->create([
            'topic_id' => $topic->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/articles/bulk', [
                'ids' => [$article->id],
                'action' => 'publish',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'published',
        ]);
    }
}
