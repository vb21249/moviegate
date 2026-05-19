<?php

declare(strict_types=1);

namespace App\Modules\Playlist\DTO;

/**
 * Public playlist DTO.
 */
final class PlaylistDto
{
    /**
     * @param int $id
     * @param int $userId
     * @param string $name
     * @param string $slug
     * @param string|null $description
     * @param string $visibility
     * @param bool $isDefaultWatchLater
     * @param string|null $createdAt
     * @param string|null $updatedAt
     * @param array<string, int> $stats
     * @param list<array<string, mixed>> $movies
     */
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly string $visibility,
        public readonly bool $isDefaultWatchLater,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly array $stats = [],
        public readonly array $movies = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'id' => $this->id,
            'user_id' => $this->userId,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'visibility' => $this->visibility,
            'is_default_watch_later' => $this->isDefaultWatchLater,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'stats' => $this->stats,
        ];

        if ($this->movies !== []) {
            $payload['movies'] = $this->movies;
        }

        return $payload;
    }
}
