<?php

namespace App\Console\Commands;

use App\Models\SearchLog;
use Illuminate\Console\Command;

class CleanupSearchLogs extends Command
{
    protected $signature = 'search:cleanup-logs {--days=90 : Delete logs older than this many days}';

    protected $description = 'Remove old search log entries';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = SearchLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} search log(s) older than {$days} days.");

        return Command::SUCCESS;
    }
}
