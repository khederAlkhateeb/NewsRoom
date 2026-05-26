<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Carbon\Carbon;

class ArchiveArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     * {days? : The number of days to look back for un-published articles, default is 30}
     */
    protected $signature = 'articles:archive {days? : Optional threshold for archive days}';

    /**
     * The console command description.
     */
    protected $description = 'Archive articles that have not been published and exceed a specified age';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        //Accept an optional argument for days, defaulting to 30 days
        $days = $this->argument('days') ? (int) $this->argument('days') : 30;

        $targetDate = Carbon::now()->subDays($days);

        // Fetch articles where status is not 'published' and created before the target date
        $query = Article::where('status', '!=', 'published')
            ->where('created_at', '<', $targetDate);

        $count = $query->count();

        if ($count === 0) {
            $this->info("No un-published articles found older than {$days} days.");
            return Command::SUCCESS;
        }

        // Updating status to 'archived'
        $query->update(['status' => 'archived']);

        $this->info("Successfully archived {$count} articles that were older than {$days} days.");
        return Command::SUCCESS;
    }
}