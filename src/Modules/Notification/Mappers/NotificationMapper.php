<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mappers;

use App\Modules\Notification\DTO\NotificationDto;
use App\Modules\Notification\Entities\NotificationEntity;

/**
 * Notification mapper placeholder.
 */
final class NotificationMapper
{
    public function mapToDto(NotificationEntity $entity): NotificationDto
    {
        return new NotificationDto($entity->attributes);
    }
}