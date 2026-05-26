<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\User;
use App\Services\Contracts\NotificationServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWeeklyAdminReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job will be attempted if it fails.
     */
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Set the queue to 'reports' (Low priority compared to notifications)
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationServiceInterface $notificationService): void
    {
        //Fetch all system administrators
        $admins = User::where('role', 'admin')->get();
        
        //Count articles published during the last 7 days
        $weeklyArticlesCount = Article::where('status', 'published')
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        //Send the report notification to each admin contextually
        foreach ($admins as $admin) {
            $notificationService->send($admin, "Weekly Report: {$weeklyArticlesCount} new articles published this week.");
        }
    }
}