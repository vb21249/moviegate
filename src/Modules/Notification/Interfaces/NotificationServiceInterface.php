<?php

declare(strict_types=1);

namespace App\Modules\Notification\Interfaces;

use App\Modules\Notification\Requests\NotificationRequest;
use App\Modules\Notification\Responses\NotificationResponse;

/**
 * Notification application service contract.
 */
interface NotificationServiceInterface
{
    public function index(NotificationRequest $request, int $userId): NotificationResponse;

    public function view(int $notificationId, int $userId): NotificationResponse;

    public function markRead(int $notificationId, int $userId): NotificationResponse;

    public function markAllRead(NotificationRequest $request, int $userId): NotificationResponse;
}
