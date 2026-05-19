<?php

declare(strict_types=1);

namespace App\Modules\Feed\DTO;

/**
 * Public feed event DTO.
 */
final class FeedDto
{
    /**
     * @param array<string, mixed>|list<mixed>|null $payload
     * @param array<string, mixed>|null $user
     */
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $eventType,
        public readonly string $entityType,
        public readonly ?int $entityId,
        public readonly ?array $payload,
        public readonly ?string $occurredAt,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?array $user = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'event_type' => $this->eventType,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'payload' => $this->payload,
            'occurred_at' => $this->occurredAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'user' => $this->user,
        ];
    }
}
