<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@thaidevdocs.local')->first()
            ?? User::factory()->admin()->create([
                'email' => 'admin@thaidevdocs.local',
                'name' => 'Admin',
            ]);

        $articles = [
            // HTML (6)
            ['topic' => 'html', 'title' => 'เริ่มต้นกับ HTML', 'slug' => 'getting-started-with-html', 'type' => 'guide'],
            ['topic' => 'html', 'title' => 'HTML Elements', 'slug' => 'html-elements', 'type' => 'reference'],
            ['topic' => 'html', 'title' => 'HTML Forms', 'slug' => 'html-forms', 'type' => 'tutorial'],
            ['topic' => 'html', 'title' => 'HTML Tables', 'slug' => 'html-tables', 'type' => 'tutorial'],
            ['topic' => 'html', 'title' => 'HTML Media', 'slug' => 'html-media', 'type' => 'guide'],
            ['topic' => 'html', 'title' => 'HTML5 APIs', 'slug' => 'html5-apis', 'type' => 'reference'],
            // CSS (8)
            ['topic' => 'css', 'title' => 'CSS พื้นฐาน', 'slug' => 'css-basics', 'type' => 'guide'],
            ['topic' => 'css', 'title' => 'CSS Selectors', 'slug' => 'css-selectors', 'type' => 'reference'],
            ['topic' => 'css', 'title' => 'CSS Box Model', 'slug' => 'css-box-model', 'type' => 'tutorial'],
            ['topic' => 'css', 'title' => 'CSS Flexbox', 'slug' => 'css-flexbox', 'type' => 'tutorial'],
            ['topic' => 'css', 'title' => 'CSS Grid', 'slug' => 'css-grid', 'type' => 'tutorial'],
            ['topic' => 'css', 'title' => 'Responsive Design', 'slug' => 'css-responsive-design', 'type' => 'guide'],
            ['topic' => 'css', 'title' => 'CSS Animations', 'slug' => 'css-animations', 'type' => 'tutorial'],
            ['topic' => 'css', 'title' => 'CSS Variables', 'slug' => 'css-variables', 'type' => 'reference'],
            // JavaScript (7)
            ['topic' => 'javascript', 'title' => 'JavaScript พื้นฐาน', 'slug' => 'javascript-basics', 'type' => 'guide'],
            ['topic' => 'javascript', 'title' => 'ตัวแปรใน JavaScript', 'slug' => 'javascript-variables', 'type' => 'tutorial'],
            ['topic' => 'javascript', 'title' => 'DOM Manipulation', 'slug' => 'dom-manipulation', 'type' => 'tutorial'],
            ['topic' => 'javascript', 'title' => 'JavaScript Events', 'slug' => 'javascript-events', 'type' => 'tutorial'],
            ['topic' => 'javascript', 'title' => 'Async Programming', 'slug' => 'async-javascript', 'type' => 'guide'],
            ['topic' => 'javascript', 'title' => 'ES6+ Features', 'slug' => 'es6-features', 'type' => 'reference'],
            ['topic' => 'javascript', 'title' => 'Error Handling', 'slug' => 'javascript-error-handling', 'type' => 'tutorial'],
            // Web APIs (4)
            ['topic' => 'web-apis', 'title' => 'Fetch API', 'slug' => 'fetch-api', 'type' => 'guide'],
            ['topic' => 'web-apis', 'title' => 'Storage API', 'slug' => 'storage-api', 'type' => 'tutorial'],
            ['topic' => 'web-apis', 'title' => 'Canvas API', 'slug' => 'canvas-api', 'type' => 'tutorial'],
            ['topic' => 'web-apis', 'title' => 'Web Components', 'slug' => 'web-components', 'type' => 'reference'],
        ];

        foreach ($articles as $index => $data) {
            $topic = Topic::where('slug', $data['topic'])->first();

            if (!$topic) {
                continue;
            }

            Article::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'topic_id' => $topic->id,
                    'author_id' => $admin->id,
                    'title' => $data['title'],
                    'summary' => 'บทความเกี่ยวกับ ' . $data['title'] . ' สำหรับนักพัฒนาไทย',
                    'content' => "# {$data['title']}\n\nเนื้อหาบทความ {$data['title']} — ตัวอย่างและคำอธิบายภาษาไทย\n\n```html\n<!-- ตัวอย่างโค้ด -->\n<div>Hello ThaiDevDocs</div>\n```",
                    'difficulty' => $index < 8 ? 'beginner' : ($index < 18 ? 'intermediate' : 'advanced'),
                    'article_type' => $data['type'],
                    'reading_time' => 5 + ($index % 6),
                    'status' => 'published',
                    'is_featured' => $index < 4,
                    'allow_comments' => true,
                    'published_at' => now()->subDays(25 - min($index, 24)),
                ]
            );
        }

        $this->command?->info('Seeded ' . count($articles) . ' articles.');
    }
}
