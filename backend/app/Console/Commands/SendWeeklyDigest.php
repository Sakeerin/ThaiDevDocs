<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\User;
use App\Notifications\WeeklyDigestNotification;
use Illuminate\Console\Command;

class SendWeeklyDigest extends Command
{
    protected $signature = 'digest:send-weekly';

    protected $description = 'Send weekly digest emails to subscribed users';

    public function handle(): int
    {
        $articles = Article::published()
            ->orderByDesc('published_at')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'summary', 'published_at']);

        if ($articles->isEmpty()) {
            $this->warn('No published articles available for digest.');

            return Command::SUCCESS;
        }

        $users = User::query()
            ->whereNotNull('email_verified_at')
            ->whereHas('preferences', fn ($query) => $query->where('weekly_digest', true))
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            $user->notify(new WeeklyDigestNotification($articles));
            $sent++;
        }

        $this->info("Weekly digest sent to {$sent} user(s).");

        return Command::SUCCESS;
    }
}
