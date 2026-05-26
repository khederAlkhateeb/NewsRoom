<?php

namespace App\Listeners;

use App\Events\ArticlePublished;
use App\Models\User;
use App\Services\Contracts\NotificationServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendArticleNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;
    public $tries = 3;
    public $queue = 'high';
    /**
     * Inject the Notification Service via Service Container.
     * * Since this runs outside the controller context, Laravel will resolve
     * the default binding or we can safely handle email dispatching here.
     */
    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event in the background queue.
     */
    public function handle(ArticlePublished $event): void
    {
        $article = $event->article;

        // Fetch all system readers to notify them about the new broadcast
        $readers = User::where('role', 'reader')->get();

        foreach ($readers as $reader) {
            //Dispatching email notifications asynchronously
            $this->notificationService->send($reader, "New Article Published: {$article->title}");
        }
    }
}
