<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mappers;

use App\Modules\Notification\DTO\NotificationDto;

/**
 * Maps persistence rows into notification DTOs.
 */
final class NotificationMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public function mapNotification(array $row): NotificationDto
    {
        return new NotificationDto(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            type: (string) $row['type'],
            payload: $this->decodePayload($row['payload_json'] ?? null),
            readAt: $row['read_at'] !== null ? (string) $row['read_at'] : null,
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
        );
    }

    /**
     * @param mixed $payload
     *
     * @return array<string, mixed>|list<mixed>
     */
    private function decodePayload(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (!is_string($payload) || $payload === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }
}
