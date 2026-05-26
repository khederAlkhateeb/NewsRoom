<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Services\Contracts\NotificationServiceInterface;

class EmailNotificationService implements NotificationServiceInterface
{
    /**
     * Dispatch email notification for Writer/Reader context.
     */
    public function send(User $user, string $message): void
    {
        logger()->info("Email Notification sent to Writer {$user->id}: {$message}");
    }
}