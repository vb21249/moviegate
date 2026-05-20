<?php

declare(strict_types=1);

namespace App\Modules\Notification\Transformers;

/**
 * Builds notification-specific API payload fragments.
 */
final class NotificationTransformer
{
    /**
     * @return array<string, int|bool>
     */
    public function markAllReadPayload(int $markedCount, int $unreadCount): array
    {
        return [
            'marked_read' => $markedCount,
            'unread_count' => $unreadCount,
        ];
    }
}
