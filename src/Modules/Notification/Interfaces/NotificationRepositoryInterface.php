<?php

declare(strict_types=1);

namespace App\Modules\Notification\Interfaces;

/**
 * Notification repository contract.
 */
interface NotificationRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function findUserNotifications(
        int $userId,
        int $limit,
        int $offset,
        ?string $type = null,
        bool $unreadOnly = false
    ): array;

    public function countUserNotifications(int $userId, ?string $type = null, bool $unreadOnly = false): int;

    public function countUnread(int $userId): int;

    /**
     * @return array<string, mixed>|null
     */
    public function findUserNotification(int $notificationId, int $userId): ?array;

    /**
     * @param array<string, mixed>|list<mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function createNotification(int $userId, string $type, array $payload): array;

    public function markRead(int $notificationId, int $userId): void;

    public function markAllRead(int $userId, ?string $type = null): int;
}
