<?php

declare(strict_types=1);

namespace App\Modules\Integration\Clients;

/**
 * TMDB REST client contract.
 */
interface TmdbClientInterface
{
    /**
     * @param int $tmdbId
     * @param string $language
     *
     * @return array<string, mixed>
     */
    public function movieDetails(int $tmdbId, string $language = 'en-US'): array;

    /**
     * @param string $query
     * @param string $language
     * @param bool $includeAdult
     *
     * @return array<string, mixed>
     */
    public function searchMovies(string $query, string $language = 'en-US', bool $includeAdult = false): array;

    /**
     * @param string $resource
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    public function get(string $resource, array $query = []): array;
}
