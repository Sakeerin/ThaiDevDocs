<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Browser;
use App\Models\BrowserCompatibility;
use Illuminate\Database\Seeder;

class BrowserSeeder extends Seeder
{
    public function run(): void
    {
        $browsers = [
            ['name' => 'Chrome', 'slug' => 'chrome', 'color' => '#4285F4', 'sort_order' => 0],
            ['name' => 'Firefox', 'slug' => 'firefox', 'color' => '#FF7139', 'sort_order' => 1],
            ['name' => 'Safari', 'slug' => 'safari', 'color' => '#006CFF', 'sort_order' => 2],
            ['name' => 'Edge', 'slug' => 'edge', 'color' => '#0078D7', 'sort_order' => 3],
        ];

        foreach ($browsers as $browser) {
            Browser::firstOrCreate(['slug' => $browser['slug']], array_merge($browser, ['is_active' => true]));
        }

        $article = Article::where('slug', 'fetch-api')->first();

        if (!$article) {
            return;
        }

        $statuses = ['yes', 'yes', 'yes', 'yes'];

        foreach (Browser::all() as $index => $browser) {
            BrowserCompatibility::firstOrCreate(
                [
                    'article_id' => $article->id,
                    'browser_id' => $browser->id,
                ],
                [
                    'support_status' => $statuses[$index] ?? 'partial',
                    'version_added' => '1.0',
                ]
            );
        }
    }
}
