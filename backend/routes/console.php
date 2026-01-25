<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Update article view counts (aggregate from page_views)
Schedule::command('articles:update-view-counts')->hourly();

// Clean up old search logs
Schedule::command('search:cleanup-logs')->daily();

// Generate sitemap
Schedule::command('sitemap:generate')->daily();

// Send weekly digest emails
Schedule::command('digest:send-weekly')->weeklyOn(1, '8:00');

// Clean up expired tokens
Schedule::command('sanctum:prune-expired --hours=24')->daily();

// Sync search indexes
Schedule::command('scout:sync-index-settings')->weekly();
