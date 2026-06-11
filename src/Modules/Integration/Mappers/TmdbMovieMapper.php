<?php

declare(strict_types=1);

namespace App\Modules\Integration\Mappers;

use App\Modules\Integration\Exceptions\IntegrationException;
use App\Modules\Movie\Enums\MovieStatus;
use yii\helpers\Inflector;

/**
 * Maps TMDB movie payloads into MovieGate catalog payloads.
 */
final class TmdbMovieMapper
{
    /**
     * @param array<string, mixed> $tmdbMovie
     *
     * @return array<string, mixed>
     */
    public function mapToCatalogMovie(array $tmdbMovie, string $imageBaseUri, ?string $language = null): array
    {
        $tmdbId = (int) ($tmdbMovie['id'] ?? 0);

        if ($tmdbId <= 0) {
            throw new IntegrationException(
                'TMDB returned movie without id.',
                502,
                IntegrationException::CODE_TMDB_REQUEST_FAILED
            );
        }

        $title = trim((string) ($tmdbMovie['title'] ?? $tmdbMovie['original_title'] ?? ''));
        $title = $title !== '' ? $title : sprintf('TMDB Movie %d', $tmdbId);
        $releaseDate = $this->normalizeReleaseDate($tmdbMovie['release_date'] ?? null);

        return [
            'source_provider' => 'tmdb',
            'external_id' => (string) $tmdbId,
            'tmdb_id' => $tmdbId,
            'imdb_id' => $this->imdbId($tmdbMovie['imdb_id'] ?? null),
            'slug' => $this->slug($title, $releaseDate, $tmdbId),
            'title' => $title,
            'original_title' => $this->nullableString($tmdbMovie['original_title'] ?? null),
            'overview' => $this->nullableString($tmdbMovie['overview'] ?? null),
            'poster_url' => $this->imageUrl($tmdbMovie['poster_path'] ?? null, $imageBaseUri),
            'backdrop_url' => $this->imageUrl($tmdbMovie['backdrop_path'] ?? null, $imageBaseUri),
            'release_date' => $releaseDate,
            'runtime_minutes' => $this->positiveInt($tmdbMovie['runtime'] ?? null),
            'status' => MovieStatus::Active->value,
            'language' => $language,
        ];
    }

    private function normalizeReleaseDate(mixed $releaseDate): ?string
    {
        if (!is_string($releaseDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $releaseDate)) {
            return null;
        }

        return $releaseDate;
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function positiveInt(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function imdbId(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        return preg_match('/^tt\d{7,10}$/', $value) === 1 ? $value : null;
    }

    private function imageUrl(mixed $path, string $imageBaseUri): ?string
    {
        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim($imageBaseUri, '/') . '/' . ltrim($path, '/');
    }

    private function slug(string $title, ?string $releaseDate, int $tmdbId): string
    {
        $baseSlug = Inflector::slug($title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'tmdb-movie';
        $year = $releaseDate !== null ? substr($releaseDate, 0, 4) : null;

        return implode('-', array_filter([$baseSlug, $year, (string) $tmdbId]));
    }
}
