<?php

namespace Database\Factories;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_id' => Topic::factory(),
            'author_id' => User::factory(),
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'summary' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'content_html' => '<p>' . implode('</p><p>', fake()->paragraphs(5)) . '</p>',
            'difficulty' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'article_type' => fake()->randomElement(['guide', 'tutorial', 'reference', 'example']),
            'reading_time' => fake()->numberBetween(3, 20),
            'view_count' => fake()->numberBetween(0, 10000),
            'status' => 'draft',
            'is_featured' => fake()->boolean(10),
            'allow_comments' => true,
            'published_at' => null,
        ];
    }

    /**
     * Indicate that the article is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the article is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
