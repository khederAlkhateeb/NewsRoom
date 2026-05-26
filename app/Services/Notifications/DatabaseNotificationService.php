<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Services\Contracts\NotificationServiceInterface;

class DatabaseNotificationService implements NotificationServiceInterface
{
    /**
     * Dispatch database notification for Admin context.
     */
    public function send(User $user, string $message): void
    {
        logger()->info("Database Notification sent to Admin {$user->id}: {$message}");
    }
}