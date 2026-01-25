<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\Topic;
use App\Models\LearningPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml for SEO';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $baseUrl = config('app.frontend_url', 'https://thaidevdocs.com');
        $urls = [];

        // Static pages
        $staticPages = [
            ['loc' => '/', 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => '/search', 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => '/learn', 'changefreq' => 'weekly', 'priority' => '0.9'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = $this->formatUrl($baseUrl . $page['loc'], $page['changefreq'], $page['priority']);
        }

        // Categories
        $categories = Category::where('is_active', true)->get();
        foreach ($categories as $category) {
            $urls[] = $this->formatUrl(
                $baseUrl . '/docs/' . $category->slug,
                'weekly',
                '0.8',
                $category->updated_at
            );
        }

        // Topics
        $topics = Topic::where('is_active', true)->with('category')->get();
        foreach ($topics as $topic) {
            $urls[] = $this->formatUrl(
                $baseUrl . '/docs/' . $topic->category->slug . '/' . $topic->slug,
                'weekly',
                '0.7',
                $topic->updated_at
            );
        }

        // Articles
        $articles = Article::published()
            ->with(['topic.category'])
            ->orderBy('published_at', 'desc')
            ->get();

        foreach ($articles as $article) {
            $urls[] = $this->formatUrl(
                $baseUrl . '/docs/' . $article->slug,
                'weekly',
                '0.6',
                $article->updated_at
            );
        }

        // Learning Paths
        $learningPaths = LearningPath::where('is_published', true)->get();
        foreach ($learningPaths as $path) {
            $urls[] = $this->formatUrl(
                $baseUrl . '/learn/' . $path->slug,
                'monthly',
                '0.7',
                $path->updated_at
            );
        }

        // Generate XML
        $xml = $this->generateXml($urls);

        // Save to storage
        Storage::disk('public')->put('sitemap.xml', $xml);

        $this->info('Sitemap generated successfully with ' . count($urls) . ' URLs.');
        $this->info('Saved to: ' . Storage::disk('public')->path('sitemap.xml'));

        return Command::SUCCESS;
    }

    /**
     * Format a URL entry for the sitemap.
     */
    private function formatUrl(
        string $loc,
        string $changefreq = 'weekly',
        string $priority = '0.5',
        ?\DateTimeInterface $lastmod = null
    ): array {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    /**
     * Generate XML sitemap content.
     */
    private function generateXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
