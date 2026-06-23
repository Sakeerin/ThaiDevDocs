<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class CategoryTopicSeeder extends Seeder
{
    public function run(): void
    {
        $webTech = Category::firstOrCreate(
            ['slug' => 'web-technologies'],
            [
                'name' => 'เทคโนโลยีเว็บ',
                'name_en' => 'Web Technologies',
                'description' => 'HTML, CSS, JavaScript และ Web APIs',
                'icon' => '📚',
                'color' => '#3B82F6',
                'sort_order' => 0,
                'is_active' => true,
            ]
        );

        $topics = [
            ['slug' => 'html', 'name' => 'HTML', 'name_en' => 'HTML', 'sort_order' => 0],
            ['slug' => 'css', 'name' => 'CSS', 'name_en' => 'CSS', 'sort_order' => 1],
            ['slug' => 'javascript', 'name' => 'JavaScript', 'name_en' => 'JavaScript', 'sort_order' => 2],
            ['slug' => 'web-apis', 'name' => 'Web APIs', 'name_en' => 'Web APIs', 'sort_order' => 3],
        ];

        foreach ($topics as $topic) {
            Topic::firstOrCreate(
                ['slug' => $topic['slug']],
                [
                    'category_id' => $webTech->id,
                    'name' => $topic['name'],
                    'name_en' => $topic['name_en'],
                    'description' => 'หัวข้อ' . $topic['name'],
                    'sort_order' => $topic['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // Legacy categories from early seeder
        Category::firstOrCreate(
            ['slug' => 'web-development'],
            [
                'name' => 'เว็บพัฒนา',
                'name_en' => 'Web Development',
                'description' => 'เรียนรู้การพัฒนาเว็บไซต์',
                'icon' => 'globe',
                'color' => '#6366F1',
                'is_active' => true,
            ]
        );
    }
}
