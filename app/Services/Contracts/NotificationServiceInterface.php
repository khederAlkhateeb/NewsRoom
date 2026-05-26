<?php

namespace App\Services\Contracts;

use App\Models\User;

interface NotificationServiceInterface
{
    /**
     * Send notification to a specific user.
     * * @param User $user
     * @param string $message
     * @return void
     */
    public function send(User $user, string $message): void;
}