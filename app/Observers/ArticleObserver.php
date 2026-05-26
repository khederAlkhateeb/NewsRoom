<?php

namespace App\Observers;

use App\Models\Article;
use App\Events\ArticlePublished;
use Illuminate\Support\Facades\Cache;

class ArticleObserver
{
    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        $this->clearArticlesCache();

        // Trigger event if published directly upon creation
        if ($article->status === 'published') {
            event(new ArticlePublished($article));
        }
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        $this->clearArticlesCache();

        // Dispatch background jobs if status changed to published
        if ($article->isDirty('status') && $article->status === 'published') {
            event(new ArticlePublished($article));
        }
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        $this->clearArticlesCache();
    }

    /**
     * Requirement 15 & 16: Automated and targeted cache invalidation using Redis Tags
     */
    private function clearArticlesCache(): void
    {
        // Clear only dashboard statistics cache
        Cache::tags(['dashboard'])->flush();
        
        // Clear articles listing cache simultaneously
        Cache::tags(['articles'])->flush();
    }
}