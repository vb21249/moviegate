<?php

declare(strict_types=1);

namespace App\Modules\Notification\Factories;

use App\Modules\Notification\Entities\NotificationEntity;

/**
 * Notification factory placeholder.
 */
final class NotificationFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): NotificationEntity
    {
        return new NotificationEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}