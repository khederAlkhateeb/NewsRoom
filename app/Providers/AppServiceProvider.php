<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Eloquent\EloquentArticleRepository;
use App\Services\Contracts\NotificationServiceInterface;
use App\Services\Notifications\DatabaseNotificationService;
use App\Services\Notifications\EmailNotificationService;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Writer\WriterArticleController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Observers\ArticleObserver;
use Illuminate\Support\Facades\Event;
use App\Events\ArticlePublished;
use App\Listeners\SendArticleNotifications;
use Illuminate\Auth\Events\Registered;
use App\Listeners\SendWelcomeNotification;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
{
    //Fallback / Global default binding for Listeners or any class outside Controllers
    $this->app->bind(NotificationServiceInterface::class, EmailNotificationService::class);

    //Default binding for the Repository
    $this->app->bind(ArticleRepositoryInterface::class, EloquentArticleRepository::class);

    //Contextual Bindings for specific Controllers
    $this->app->when(AdminDashboardController::class)
        ->needs(NotificationServiceInterface::class)
        ->give(DatabaseNotificationService::class);

    $this->app->when(WriterArticleController::class)
        ->needs(NotificationServiceInterface::class)
        ->give(EmailNotificationService::class);
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Article::observe(ArticleObserver::class);
        Event::listen(
            ArticlePublished::class,
            SendArticleNotifications::class
        );
        Event::listen(Registered::class, SendWelcomeNotification::class);
        // Define rate limiting pattern for API endpoints
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please throttle your API consumption.'
                ], 429);
            });
        });
    }
}
