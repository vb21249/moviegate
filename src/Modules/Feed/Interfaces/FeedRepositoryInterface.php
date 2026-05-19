<?php

declare(strict_types=1);

namespace App\Modules\Feed\Interfaces;

/**
 * Feed repository contract.
 */
interface FeedRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function findEvents(
        int $limit,
        int $offset,
        ?int $userId = null,
        ?string $eventType = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): array;

    public function countEvents(
        ?int $userId = null,
        ?string $eventType = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): int;

    /**
     * @return array<string, mixed>|null
     */
    public function findEvent(int $eventId): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function findUserEvents(
        int $userId,
        int $limit,
        int $offset,
        ?string $eventType = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): array;

    public function countUserEvents(
        int $userId,
        ?string $eventType = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): int;

    /**
     * @param array<string, mixed>|list<mixed>|null $payload
     *
     * @return array<string, mixed>
     */
    public function appendEvent(
        int $userId,
        string $eventType,
        string $entityType,
        ?int $entityId = null,
        ?array $payload = null
    ): array;
}
