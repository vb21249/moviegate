<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Interfaces;

/**
 * Playlist repository contract.
 */
interface PlaylistRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}