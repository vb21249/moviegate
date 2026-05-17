<?php

declare(strict_types=1);

namespace App\Modules\Notification\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Notification\Interfaces\NotificationRepositoryInterface;

/**
 * Notification repository placeholder.
 */
final class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}