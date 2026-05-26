<?php

namespace App\Repositories\Eloquent;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EloquentArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Fetch published articles with advanced caching and Cache Stampede protection.
     * * @return Collection
     */
    public function getAllPublished(): \Illuminate\Support\Collection
    {
        $cacheKey = 'articles:published:all';
        $lockKey = 'locks:articles:published';

        // Attempt to fetch data from the cache
        $cachedData = Cache::tags(['articles'])->get($cacheKey);

        if ($cachedData !== null) {
            // Return data as a Collection of arrays (safe and prevents Incomplete Class issues)
            return collect($cachedData);
        }

        // Use Atomic Lock to prevent high concurrency conflicts (Cache Stampede Protection)
        return Cache::lock($lockKey, 10)->block(3, function () use ($cacheKey) {
            // Secondary check (Double-checked locking pattern)
            $secondaryCheck = Cache::tags(['articles'])->get($cacheKey);
            if ($secondaryCheck !== null) {
                return collect($secondaryCheck);
            }

            // Fetch fresh data from the database with relationships (Eager Loading)
            $freshArticles = Article::where('status', 'published')
                ->with(['writer', 'comments.user', 'tags'])
                ->latest('published_at')
                ->get();

            // Convert the Eloquent Collection to a raw array for secure caching
            $articlesArray = $freshArticles->toArray();

            // Store the array in the cache for 24 hours
            Cache::tags(['articles'])->put($cacheKey, $articlesArray, now()->addHours(24));

            return collect($articlesArray);
        });
    }

    /**
     *Fetch Dashboard Stats with Lock & Redis Tags
     */
    public function getDashboardStats(): array
    {
        $cacheKey = 'dashboard:stats';
        $lockKey = 'locks:dashboard:stats';

        // Use Redis Tags to group dashboard data independently (Requirement 15)
        $stats = Cache::tags(['dashboard'])->get($cacheKey);

        if ($stats !== null) {
            return $stats;
        }

        // Prevent 300 concurrent users from killing the DB when cache expires
        return Cache::lock($lockKey, 10)->block(3, function () use ($cacheKey) {
            $secondaryCheck = Cache::tags(['dashboard'])->get($cacheKey);
            if ($secondaryCheck !== null) {
                return $secondaryCheck;
            }

            // Heavy Aggregation Queries
            $freshStats = [
                'total_articles' => Article::count(),
                'total_comments' => Comment::count(),
                'top_writers'    => User::where('role', 'writer')
                    ->withCount('articles')
                    ->orderBy('articles_count', 'desc')
                    ->take(5)
                    ->get()
                    ->toArray(),
                'popular_tags'   => Tag::withCount('articles')
                    ->orderBy('articles_count', 'desc')
                    ->take(10)
                    ->get()
                    ->toArray()
            ];

            Cache::tags(['dashboard'])->put($cacheKey, $freshStats, now()->addHours(24));

            return $freshStats;
        });
    }

    public function create(array $data): Article
    {
        return Article::create($data);
    }
}
