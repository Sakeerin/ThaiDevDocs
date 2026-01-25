<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bookmark>
 */
class BookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'article_id' => Article::factory(),
            'collection_id' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the bookmark belongs to a collection.
     */
    public function inCollection(): static
    {
        return $this->state(fn (array $attributes) => [
            'collection_id' => Collection::factory(),
        ]);
    }
}
