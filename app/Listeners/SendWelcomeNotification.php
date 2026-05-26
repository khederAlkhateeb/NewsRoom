<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Services\Contracts\NotificationServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeNotification implements ShouldQueue
{
    protected $notificationService;

    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the system event when a user registers.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;
        
        $this->notificationService->send($user, "Welcome to our NewsRoom platform!");
    }
}