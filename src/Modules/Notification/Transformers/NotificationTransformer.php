<?php

declare(strict_types=1);

namespace App\Modules\Notification\Transformers;

use App\Modules\Notification\Responses\NotificationResponse;

/**
 * Notification transformer placeholder.
 */
final class NotificationTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): NotificationResponse
    {
        return new NotificationResponse($payload);
    }
}