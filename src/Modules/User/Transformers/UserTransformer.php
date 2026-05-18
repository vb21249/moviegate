<?php

declare(strict_types=1);

namespace App\Modules\User\Transformers;

/**
 * Transforms user read model rows into API payload fragments.
 */
final class UserTransformer
{
    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function activityItem(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'event_type' => (string) $row['event_type'],
            'entity_type' => (string) $row['entity_type'],
            'entity_id' => $row['entity_id'] !== null ? (int) $row['entity_id'] : null,
            'payload' => $this->decodePayload($row['payload_json'] ?? null),
            'occurred_at' => $row['occurred_at'] !== null ? (string) $row['occurred_at'] : null,
        ];
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function ratingItem(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'score' => (int) $row['score'],
            'review_text' => $row['review_text'] !== null ? (string) $row['review_text'] : null,
            'rated_at' => $row['rated_at'] !== null ? (string) $row['rated_at'] : null,
            'movie' => [
                'id' => $row['movie_id'] !== null ? (int) $row['movie_id'] : null,
                'slug' => $row['movie_slug'] !== null ? (string) $row['movie_slug'] : null,
                'title' => $row['movie_title'] !== null ? (string) $row['movie_title'] : null,
                'poster_url' => $row['movie_poster_url'] !== null ? (string) $row['movie_poster_url'] : null,
                'release_date' => $row['movie_release_date'] !== null ? (string) $row['movie_release_date'] : null,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function postItem(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'body' => (string) $row['body'],
            'likes_count' => (int) $row['likes_count'],
            'published_at' => $row['published_at'] !== null ? (string) $row['published_at'] : null,
            'created_at' => $row['created_at'] !== null ? (string) $row['created_at'] : null,
            'movie' => [
                'id' => $row['movie_id'] !== null ? (int) $row['movie_id'] : null,
                'slug' => $row['movie_slug'] !== null ? (string) $row['movie_slug'] : null,
                'title' => $row['movie_title'] !== null ? (string) $row['movie_title'] : null,
                'poster_url' => $row['movie_poster_url'] !== null ? (string) $row['movie_poster_url'] : null,
                'release_date' => $row['movie_release_date'] !== null ? (string) $row['movie_release_date'] : null,
            ],
        ];
    }

    /**
     * @param mixed $payload
     *
     * @return array<string, mixed>|list<mixed>|null
     */
    private function decodePayload(mixed $payload): array|null
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
