<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SearchLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get analytics overview.
     */
    public function overview(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        // Total views
        $totalViews = DB::table('page_views')
            ->where('created_at', '>=', $startDate)
            ->count();

        // Unique visitors (by session)
        $uniqueVisitors = DB::table('page_views')
            ->where('created_at', '>=', $startDate)
            ->distinct('session_id')
            ->count('session_id');

        // Average time on page
        $avgTimeOnPage = DB::table('page_views')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('time_on_page')
            ->avg('time_on_page');

        // Top countries
        $topCountries = DB::table('page_views')
            ->select('country_code', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period_days' => $days,
                'total_views' => $totalViews,
                'unique_visitors' => $uniqueVisitors,
                'avg_time_on_page' => round($avgTimeOnPage ?? 0),
                'top_countries' => $topCountries,
            ],
        ]);
    }

    /**
     * Get article analytics.
     */
    public function articles(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        // Top articles by views
        $topArticles = Article::select('id', 'title', 'slug', 'view_count')
            ->published()
            ->orderByDesc('view_count')
            ->limit(20)
            ->get();

        // Articles with most views in period
        $trendingArticles = DB::table('page_views')
            ->select('article_id', DB::raw('COUNT(*) as views'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('article_id')
            ->groupBy('article_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $trendingArticleIds = $trendingArticles->pluck('article_id');
        $articles = Article::whereIn('id', $trendingArticleIds)
            ->get()
            ->keyBy('id');

        $trending = $trendingArticles->map(function ($item) use ($articles) {
            $article = $articles->get($item->article_id);
            return [
                'article_id' => $item->article_id,
                'title' => $article?->title,
                'slug' => $article?->slug,
                'views' => $item->views,
            ];
        });

        // Articles by difficulty
        $byDifficulty = Article::published()
            ->select('difficulty', DB::raw('COUNT(*) as count'))
            ->groupBy('difficulty')
            ->get();

        // Articles by type
        $byType = Article::published()
            ->select('article_type', DB::raw('COUNT(*) as count'))
            ->groupBy('article_type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'top_articles' => $topArticles,
                'trending_articles' => $trending,
                'by_difficulty' => $byDifficulty,
                'by_type' => $byType,
            ],
        ]);
    }

    /**
     * Get search analytics.
     */
    public function search(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        // Total searches
        $totalSearches = SearchLog::where('created_at', '>=', $startDate)->count();

        // Searches with no results
        $noResults = SearchLog::where('created_at', '>=', $startDate)
            ->where('results_count', 0)
            ->count();

        // Top search queries
        $topQueries = SearchLog::select('query', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit(20)
            ->get();

        // Failed searches (no results)
        $failedQueries = SearchLog::select('query', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->where('results_count', 0)
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Click-through rate
        $totalWithClicks = SearchLog::where('created_at', '>=', $startDate)
            ->whereNotNull('clicked_article_id')
            ->count();
        $ctr = $totalSearches > 0 ? round(($totalWithClicks / $totalSearches) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_searches' => $totalSearches,
                'no_results_count' => $noResults,
                'click_through_rate' => $ctr,
                'top_queries' => $topQueries,
                'failed_queries' => $failedQueries,
            ],
        ]);
    }

    /**
     * Get user analytics.
     */
    public function users(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        // New users
        $newUsers = User::where('created_at', '>=', $startDate)->count();

        // Active users (logged in)
        $activeUsers = User::where('last_login_at', '>=', $startDate)->count();

        // User growth over time
        $userGrowth = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Users by role
        $byRole = User::select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->get();

        // Top contributors
        $topContributors = User::select('id', 'name', 'avatar', 'contribution_points')
            ->where('contribution_points', '>', 0)
            ->orderByDesc('contribution_points')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'new_users' => $newUsers,
                'active_users' => $activeUsers,
                'user_growth' => $userGrowth,
                'by_role' => $byRole,
                'top_contributors' => $topContributors,
            ],
        ]);
    }
}
