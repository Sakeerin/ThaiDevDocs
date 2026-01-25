<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Contribution;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'articles' => [
                'total' => Article::count(),
                'published' => Article::published()->count(),
                'drafts' => Article::where('status', 'draft')->count(),
                'pending_review' => Article::where('status', 'pending_review')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'new_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
                'contributors' => User::where('contribution_points', '>', 0)->count(),
            ],
            'comments' => [
                'total' => Comment::count(),
                'pending' => Comment::where('is_approved', false)->count(),
            ],
            'contributions' => [
                'total' => Contribution::count(),
                'pending' => Contribution::pending()->count(),
            ],
            'views' => [
                'total' => Article::sum('view_count'),
                'this_week' => DB::table('page_views')
                    ->where('created_at', '>=', now()->subWeek())
                    ->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get chart data.
     */
    public function charts(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);

        // Views over time
        $viewsData = DB::table('page_views')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // New articles over time
        $articlesData = Article::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // New users over time
        $usersData = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'views' => $viewsData,
                'articles' => $articlesData,
                'users' => $usersData,
            ],
        ]);
    }

    /**
     * Get recent activity.
     */
    public function recentActivity(): JsonResponse
    {
        $activities = collect();

        // Recent articles
        $recentArticles = Article::with('author:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($article) => [
                'type' => 'article_created',
                'message' => "{$article->author?->name} created \"{$article->title}\"",
                'timestamp' => $article->created_at,
                'data' => [
                    'article_id' => $article->id,
                    'article_title' => $article->title,
                ],
            ]);

        // Recent comments
        $recentComments = Comment::with(['user:id,name', 'article:id,title'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($comment) => [
                'type' => 'comment_added',
                'message' => "{$comment->user?->name} commented on \"{$comment->article?->title}\"",
                'timestamp' => $comment->created_at,
                'data' => [
                    'comment_id' => $comment->id,
                    'article_id' => $comment->article_id,
                ],
            ]);

        // Recent contributions
        $recentContributions = Contribution::with(['user:id,name', 'article:id,title'])
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($contrib) => [
                'type' => 'contribution_approved',
                'message' => "Approved contribution from {$contrib->user?->name}",
                'timestamp' => $contrib->reviewed_at ?? $contrib->created_at,
                'data' => [
                    'contribution_id' => $contrib->id,
                    'article_id' => $contrib->article_id,
                ],
            ]);

        $activities = $activities
            ->merge($recentArticles)
            ->merge($recentComments)
            ->merge($recentContributions)
            ->sortByDesc('timestamp')
            ->take(15)
            ->values();

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }
}
