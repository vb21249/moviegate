<?php

declare(strict_types=1);

namespace App\Modules\Integration\Clients;

/**
 * TMDB REST client contract.
 */
interface TmdbClientInterface
{
    /**
     * @param string $resource
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    public function get(string $resource, array $query = []): array;
}
