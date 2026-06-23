<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\LearningPath;
use App\Models\LearningPathItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class LearningPathSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@thaidevdocs.local')->first();

        if (!$admin) {
            return;
        }

        $path = LearningPath::firstOrCreate(
            ['slug' => 'web-fundamentals'],
            [
                'author_id' => $admin->id,
                'title' => 'เริ่มต้นเขียนเว็บ',
                'description' => 'เส้นทางเรียนรู้ HTML, CSS และ JavaScript สำหรับผู้เริ่มต้น',
                'difficulty' => 'beginner',
                'estimated_hours' => 20,
                'is_featured' => true,
                'is_published' => true,
            ]
        );

        $slugs = [
            'getting-started-with-html',
            'html-elements',
            'css-basics',
            'css-flexbox',
            'javascript-basics',
            'javascript-variables',
            'dom-manipulation',
            'fetch-api',
        ];

        foreach ($slugs as $index => $slug) {
            $article = Article::where('slug', $slug)->first();

            if (!$article) {
                continue;
            }

            LearningPathItem::firstOrCreate(
                [
                    'learning_path_id' => $path->id,
                    'article_id' => $article->id,
                ],
                [
                    'sort_order' => $index,
                    'is_required' => true,
                ]
            );
        }
    }
}
