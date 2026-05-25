<?php

declare(strict_types=1);

namespace App\Modules\Movie\DTO;

/**
 * Public movie catalog DTO.
 */
final class MovieDto
{
    /**
     * @param int $id
     * @param int|null $tmdbId
     * @param string $slug
     * @param string $title
     * @param string|null $originalTitle
     * @param string|null $overview
     * @param string|null $posterUrl
     * @param string|null $backdropUrl
     * @param string|null $releaseDate
     * @param int|null $runtimeMinutes
     * @param string $status
     * @param string|null $createdAt
     * @param string|null $updatedAt
     * @param string|null $language
     * @param array<string, int|float|null> $stats
     */
    public function __construct(
        public readonly int $id,
        public readonly ?int $tmdbId,
        public readonly string $slug,
        public readonly string $title,
        public readonly ?string $originalTitle,
        public readonly ?string $overview,
        public readonly ?string $posterUrl,
        public readonly ?string $backdropUrl,
        public readonly ?string $releaseDate,
        public readonly ?int $runtimeMinutes,
        public readonly string $status,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?string $language = null,
        public readonly array $stats = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tmdb_id' => $this->tmdbId,
            'slug' => $this->slug,
            'title' => $this->title,
            'original_title' => $this->originalTitle,
            'overview' => $this->overview,
            'poster_url' => $this->posterUrl,
            'backdrop_url' => $this->backdropUrl,
            'release_date' => $this->releaseDate,
            'runtime_minutes' => $this->runtimeMinutes,
            'status' => $this->status,
            'language' => $this->language,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'stats' => $this->stats,
        ];
    }
}
