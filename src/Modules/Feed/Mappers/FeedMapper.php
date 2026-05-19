<?php

declare(strict_types=1);

namespace App\Modules\Feed\Mappers;

use App\Modules\Feed\DTO\FeedDto;

/**
 * Maps persistence rows into feed DTOs.
 */
final class FeedMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public function mapEvent(array $row): FeedDto
    {
        return new FeedDto(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            eventType: (string) $row['event_type'],
            entityType: (string) $row['entity_type'],
            entityId: $row['entity_id'] !== null ? (int) $row['entity_id'] : null,
            payload: $this->decodePayload($row['payload_json'] ?? null),
            occurredAt: $row['occurred_at'] !== null ? (string) $row['occurred_at'] : null,
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
            user: [
                'id' => (int) $row['user_id'],
                'username' => $row['username'] !== null ? (string) $row['username'] : null,
                'avatar_url' => $row['user_avatar_url'] !== null ? (string) $row['user_avatar_url'] : null,
            ],
        );
    }

    /**
     * @param mixed $payload
     *
     * @return array<string, mixed>|list<mixed>|null
     */
    private function decodePayload(mixed $payload): ?array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (!is_string($payload) || $payload === '') {
            return null;
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : null;
    }
}
