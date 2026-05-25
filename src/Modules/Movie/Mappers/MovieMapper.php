<?php

declare(strict_types=1);

namespace App\Modules\Movie\Mappers;

use App\Modules\Movie\DTO\MovieDto;

/**
 * Maps persistence rows into movie DTOs.
 */
final class MovieMapper
{
    /**
     * @param array<string, mixed> $row
     * @param array<string, int|float|null> $stats
     *
     * @return MovieDto
     */
    public function mapMovie(array $row, array $stats = []): MovieDto
    {
        return new MovieDto(
            id: (int) $row['id'],
            tmdbId: $row['tmdb_id'] !== null ? (int) $row['tmdb_id'] : null,
            slug: (string) $row['slug'],
            title: (string) $row['title'],
            originalTitle: $row['original_title'] !== null ? (string) $row['original_title'] : null,
            overview: $row['overview'] !== null ? (string) $row['overview'] : null,
            posterUrl: $row['poster_url'] !== null ? (string) $row['poster_url'] : null,
            backdropUrl: $row['backdrop_url'] !== null ? (string) $row['backdrop_url'] : null,
            releaseDate: $row['release_date'] !== null ? (string) $row['release_date'] : null,
            runtimeMinutes: $row['runtime_minutes'] !== null ? (int) $row['runtime_minutes'] : null,
            status: (string) $row['status'],
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
            language: isset($row['language']) && $row['language'] !== null ? (string) $row['language'] : null,
            stats: $stats,
        );
    }
}
