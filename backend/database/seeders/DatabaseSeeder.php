<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Topic;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@thaidevdocs.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create demo user
        $user = User::firstOrCreate(
            ['email' => 'user@thaidevdocs.local'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // Create categories
        $categories = [
            [
                'name' => 'เว็บพัฒนา',
                'name_en' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'เรียนรู้การพัฒนาเว็บไซต์ตั้งแต่พื้นฐานจนถึงขั้นสูง',
                'icon' => 'globe',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'JavaScript',
                'name_en' => 'JavaScript',
                'slug' => 'javascript',
                'description' => 'ภาษาโปรแกรมยอดนิยมสำหรับการพัฒนาเว็บ',
                'icon' => 'code',
                'color' => '#F7DF1E',
                'is_active' => true,
            ],
            [
                'name' => 'CSS',
                'name_en' => 'CSS',
                'slug' => 'css',
                'description' => 'การจัดรูปแบบและตกแต่งเว็บไซต์ด้วย CSS',
                'icon' => 'paint-brush',
                'color' => '#264DE4',
                'is_active' => true,
            ],
            [
                'name' => 'PHP',
                'name_en' => 'PHP',
                'slug' => 'php',
                'description' => 'ภาษาโปรแกรมฝั่งเซิร์ฟเวอร์ยอดนิยม',
                'icon' => 'server',
                'color' => '#777BB4',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(['slug' => $categoryData['slug']], $categoryData);
        }

        // Create topics
        $webDevCategory = Category::where('slug', 'web-development')->first();
        $jsCategory = Category::where('slug', 'javascript')->first();
        $cssCategory = Category::where('slug', 'css')->first();
        $phpCategory = Category::where('slug', 'php')->first();

        $topics = [
            [
                'category_id' => $webDevCategory->id,
                'name' => 'HTML พื้นฐาน',
                'name_en' => 'HTML Basics',
                'slug' => 'html-basics',
                'description' => 'เรียนรู้โครงสร้างพื้นฐานของ HTML',
                'is_active' => true,
            ],
            [
                'category_id' => $jsCategory->id,
                'name' => 'JavaScript เริ่มต้น',
                'name_en' => 'JavaScript Fundamentals',
                'slug' => 'javascript-fundamentals',
                'description' => 'พื้นฐาน JavaScript ที่ควรรู้',
                'is_active' => true,
            ],
            [
                'category_id' => $cssCategory->id,
                'name' => 'CSS Flexbox',
                'name_en' => 'CSS Flexbox',
                'slug' => 'css-flexbox',
                'description' => 'การจัดวาง Layout ด้วย Flexbox',
                'is_active' => true,
            ],
            [
                'category_id' => $phpCategory->id,
                'name' => 'Laravel เบื้องต้น',
                'name_en' => 'Laravel Basics',
                'slug' => 'laravel-basics',
                'description' => 'เริ่มต้นกับ Laravel Framework',
                'is_active' => true,
            ],
        ];

        foreach ($topics as $topicData) {
            Topic::firstOrCreate(['slug' => $topicData['slug']], $topicData);
        }

        // Create tags
        $tags = [
            ['name' => 'เริ่มต้น', 'slug' => 'beginner', 'color' => '#22C55E'],
            ['name' => 'บทเรียน', 'slug' => 'tutorial', 'color' => '#3B82F6'],
            ['name' => 'ตัวอย่าง', 'slug' => 'example', 'color' => '#F59E0B'],
            ['name' => 'อ้างอิง', 'slug' => 'reference', 'color' => '#8B5CF6'],
            ['name' => 'เคล็ดลับ', 'slug' => 'tips', 'color' => '#EC4899'],
        ];

        foreach ($tags as $tagData) {
            Tag::firstOrCreate(['slug' => $tagData['slug']], $tagData);
        }

        // Create sample articles
        $htmlTopic = Topic::where('slug', 'html-basics')->first();
        $jsTopic = Topic::where('slug', 'javascript-fundamentals')->first();

        $articles = [
            [
                'topic_id' => $htmlTopic->id,
                'author_id' => $admin->id,
                'title' => 'เริ่มต้นกับ HTML',
                'slug' => 'getting-started-with-html',
                'summary' => 'บทความแนะนำ HTML สำหรับผู้เริ่มต้น',
                'content' => "# เริ่มต้นกับ HTML\n\nHTML (HyperText Markup Language) เป็นภาษามาร์กอัปมาตรฐานสำหรับสร้างหน้าเว็บ\n\n## โครงสร้างพื้นฐาน\n\n```html\n<!DOCTYPE html>\n<html lang=\"th\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>หน้าแรก</title>\n</head>\n<body>\n    <h1>สวัสดีชาวโลก!</h1>\n</body>\n</html>\n```\n\n## องค์ประกอบหลัก\n\n- `<!DOCTYPE html>` - ประกาศประเภทเอกสาร\n- `<html>` - องค์ประกอบราก\n- `<head>` - ข้อมูล metadata\n- `<body>` - เนื้อหาที่แสดงผล",
                'difficulty' => 'beginner',
                'article_type' => 'guide',
                'reading_time' => 5,
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now(),
            ],
            [
                'topic_id' => $jsTopic->id,
                'author_id' => $admin->id,
                'title' => 'ตัวแปรใน JavaScript',
                'slug' => 'javascript-variables',
                'summary' => 'เรียนรู้เกี่ยวกับตัวแปรและชนิดข้อมูลใน JavaScript',
                'content' => "# ตัวแปรใน JavaScript\n\nใน JavaScript มีวิธีการประกาศตัวแปร 3 แบบ: `var`, `let`, และ `const`\n\n## let และ const\n\n```javascript\nlet name = 'สมชาย';\nconst PI = 3.14159;\n\nname = 'สมหญิง'; // ได้\n// PI = 3.14; // Error! const ไม่สามารถเปลี่ยนค่าได้\n```\n\n## ชนิดข้อมูล\n\n- **String** - ข้อความ\n- **Number** - ตัวเลข\n- **Boolean** - true/false\n- **Array** - อาร์เรย์\n- **Object** - อ็อบเจกต์",
                'difficulty' => 'beginner',
                'article_type' => 'tutorial',
                'reading_time' => 8,
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now(),
            ],
        ];

        foreach ($articles as $articleData) {
            Article::firstOrCreate(['slug' => $articleData['slug']], $articleData);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@thaidevdocs.local / password');
        $this->command->info('User login: user@thaidevdocs.local / password');
    }
}
