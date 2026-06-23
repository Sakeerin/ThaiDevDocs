<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateArticleViewCounts extends Command
{
    protected $signature = 'articles:update-view-counts';

    protected $description = 'Aggregate page_views into article view_count totals';

    public function handle(): int
    {
        $aggregated = DB::table('page_views')
            ->whereNotNull('article_id')
            ->select('article_id', DB::raw('COUNT(*) as total'))
            ->groupBy('article_id')
            ->pluck('total', 'article_id');

        if ($aggregated->isEmpty()) {
            $this->info('No page view records to aggregate.');

            return Command::SUCCESS;
        }

        $updated = 0;

        foreach ($aggregated as $articleId => $total) {
            $affected = Article::where('id', $articleId)
                ->where('view_count', '<', $total)
                ->update(['view_count' => $total]);

            $updated += $affected;
        }

        $this->info("Updated view counts for {$updated} article(s).");

        return Command::SUCCESS;
    }
}
