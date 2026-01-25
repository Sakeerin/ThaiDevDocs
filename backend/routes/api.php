<?php

use Illuminate\Support\Facades\Route;

// API Controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\GlossaryController;
use App\Http\Controllers\Api\LearningPathController;
use App\Http\Controllers\Api\UserController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\TopicController as AdminTopicController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API Version 1
Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    */

    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::post('social/{provider}', [AuthController::class, 'socialLogin']);
    });

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);
    Route::get('categories/{slug}/topics', [CategoryController::class, 'topics']);

    // Topics
    Route::get('topics/{slug}', [TopicController::class, 'show']);
    Route::get('topics/{slug}/articles', [TopicController::class, 'articles']);

    // Articles
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/featured', [ArticleController::class, 'featured']);
    Route::get('articles/popular', [ArticleController::class, 'popular']);
    Route::get('articles/recent', [ArticleController::class, 'recent']);
    Route::get('articles/{slug}', [ArticleController::class, 'show']);
    Route::get('articles/{slug}/related', [ArticleController::class, 'related']);
    Route::get('articles/{slug}/revisions', [ArticleController::class, 'revisions']);
    Route::get('articles/{slug}/examples', [ArticleController::class, 'examples']);
    Route::get('articles/{slug}/compatibility', [ArticleController::class, 'compatibility']);

    // Tags
    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{slug}', [TagController::class, 'show']);
    Route::get('tags/{slug}/articles', [TagController::class, 'articles']);

    // Search
    Route::get('search', [SearchController::class, 'search']);
    Route::get('search/suggestions', [SearchController::class, 'suggestions']);
    Route::get('search/popular', [SearchController::class, 'popular']);

    // Glossary
    Route::get('glossary', [GlossaryController::class, 'index']);
    Route::get('glossary/search', [GlossaryController::class, 'search']);
    Route::get('glossary/{slug}', [GlossaryController::class, 'show']);

    // Learning Paths
    Route::get('learning-paths', [LearningPathController::class, 'index']);
    Route::get('learning-paths/{slug}', [LearningPathController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('auth/user', [AuthController::class, 'user']);
        Route::patch('auth/user', [AuthController::class, 'updateProfile']);

        // User Profile
        Route::get('user/profile', [UserController::class, 'profile']);
        Route::patch('user/profile', [UserController::class, 'updateProfile']);
        Route::patch('user/preferences', [UserController::class, 'updatePreferences']);
        Route::patch('user/password', [UserController::class, 'updatePassword']);

        // Bookmarks
        Route::get('bookmarks', [BookmarkController::class, 'index']);
        Route::post('bookmarks', [BookmarkController::class, 'store']);
        Route::delete('bookmarks/{id}', [BookmarkController::class, 'destroy']);
        Route::get('articles/{slug}/bookmark-status', [BookmarkController::class, 'status']);

        // Collections
        Route::get('collections', [CollectionController::class, 'index']);
        Route::post('collections', [CollectionController::class, 'store']);
        Route::get('collections/{id}', [CollectionController::class, 'show']);
        Route::patch('collections/{id}', [CollectionController::class, 'update']);
        Route::delete('collections/{id}', [CollectionController::class, 'destroy']);
        Route::post('collections/{id}/articles', [CollectionController::class, 'addArticle']);
        Route::delete('collections/{id}/articles/{articleId}', [CollectionController::class, 'removeArticle']);

        // Reading History
        Route::get('history', [UserController::class, 'history']);
        Route::post('history', [UserController::class, 'recordHistory']);
        Route::delete('history', [UserController::class, 'clearHistory']);

        // Article Interactions
        Route::post('articles/{slug}/rate', [ArticleController::class, 'rate']);
        Route::post('articles/{slug}/feedback', [ArticleController::class, 'feedback']);
        Route::post('articles/{slug}/suggest-edit', [ArticleController::class, 'suggestEdit']);

        // Comments
        Route::get('articles/{slug}/comments', [CommentController::class, 'index']);
        Route::post('articles/{slug}/comments', [CommentController::class, 'store']);
        Route::patch('comments/{id}', [CommentController::class, 'update']);
        Route::delete('comments/{id}', [CommentController::class, 'destroy']);
        Route::post('comments/{id}/vote', [CommentController::class, 'vote']);

        // Learning Paths (Authenticated)
        Route::post('learning-paths/{slug}/enroll', [LearningPathController::class, 'enroll']);
        Route::patch('learning-paths/{slug}/progress', [LearningPathController::class, 'updateProgress']);
        Route::get('my-learning', [LearningPathController::class, 'myLearning']);

        // Contributions
        Route::get('contributions', [UserController::class, 'contributions']);

    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin|super_admin|editor'])->group(function () {

        // Dashboard
        Route::get('dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('dashboard/charts', [DashboardController::class, 'charts']);
        Route::get('dashboard/recent-activity', [DashboardController::class, 'recentActivity']);

        // Articles Management
        Route::get('articles', [AdminArticleController::class, 'index']);
        Route::post('articles', [AdminArticleController::class, 'store']);
        Route::get('articles/{id}', [AdminArticleController::class, 'show']);
        Route::put('articles/{id}', [AdminArticleController::class, 'update']);
        Route::delete('articles/{id}', [AdminArticleController::class, 'destroy']);
        Route::patch('articles/{id}/status', [AdminArticleController::class, 'updateStatus']);
        Route::patch('articles/{id}/publish', [AdminArticleController::class, 'publish']);
        Route::post('articles/{id}/duplicate', [AdminArticleController::class, 'duplicate']);

        // Categories Management
        Route::get('categories', [AdminCategoryController::class, 'index']);
        Route::post('categories', [AdminCategoryController::class, 'store']);
        Route::get('categories/{id}', [AdminCategoryController::class, 'show']);
        Route::put('categories/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('categories/{id}', [AdminCategoryController::class, 'destroy']);
        Route::patch('categories/reorder', [AdminCategoryController::class, 'reorder']);

        // Topics Management
        Route::get('topics', [AdminTopicController::class, 'index']);
        Route::post('topics', [AdminTopicController::class, 'store']);
        Route::get('topics/{id}', [AdminTopicController::class, 'show']);
        Route::put('topics/{id}', [AdminTopicController::class, 'update']);
        Route::delete('topics/{id}', [AdminTopicController::class, 'destroy']);
        Route::patch('topics/reorder', [AdminTopicController::class, 'reorder']);

        // Tags Management
        Route::get('tags', [AdminTagController::class, 'index']);
        Route::post('tags', [AdminTagController::class, 'store']);
        Route::put('tags/{id}', [AdminTagController::class, 'update']);
        Route::delete('tags/{id}', [AdminTagController::class, 'destroy']);
        Route::post('tags/merge', [AdminTagController::class, 'merge']);

        // User Management (Admin only)
        Route::middleware('role:admin|super_admin')->group(function () {
            Route::get('users', [AdminUserController::class, 'index']);
            Route::get('users/{id}', [AdminUserController::class, 'show']);
            Route::patch('users/{id}/role', [AdminUserController::class, 'updateRole']);
            Route::patch('users/{id}/status', [AdminUserController::class, 'updateStatus']);
        });

        // Comment Moderation
        Route::get('comments', [AdminCommentController::class, 'index']);
        Route::get('comments/pending', [AdminCommentController::class, 'pending']);
        Route::patch('comments/{id}/approve', [AdminCommentController::class, 'approve']);
        Route::patch('comments/{id}/reject', [AdminCommentController::class, 'reject']);

        // Media Management
        Route::get('media', [AdminMediaController::class, 'index']);
        Route::post('media', [AdminMediaController::class, 'upload']);
        Route::delete('media/{id}', [AdminMediaController::class, 'destroy']);

        // Settings (Super Admin only)
        Route::middleware('role:super_admin')->group(function () {
            Route::get('settings', [AdminSettingsController::class, 'index']);
            Route::put('settings', [AdminSettingsController::class, 'update']);
        });

        // Analytics
        Route::get('analytics/overview', [AdminAnalyticsController::class, 'overview']);
        Route::get('analytics/articles', [AdminAnalyticsController::class, 'articles']);
        Route::get('analytics/search', [AdminAnalyticsController::class, 'search']);
        Route::get('analytics/users', [AdminAnalyticsController::class, 'users']);

    });

});
