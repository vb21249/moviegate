<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Interfaces;

/**
 * Playlist application service contract.
 */
interface PlaylistServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}