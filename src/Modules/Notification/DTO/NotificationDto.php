<?php

declare(strict_types=1);

namespace App\Modules\Notification\DTO;

/**
 * Public notification DTO.
 */
final class NotificationDto
{
    /**
     * @param array<string, mixed>|list<mixed> $payload
     */
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $type,
        public readonly array $payload,
        public readonly ?string $readAt,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
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
            'type' => $this->type,
            'payload' => $this->payload,
            'read_at' => $this->readAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'is_read' => $this->readAt !== null,
        ];
    }
}
