<?php

declare(strict_types=1);

namespace App\Modules\Playlist\DTO;

/**
 * Playlist DTO.
 */
final class PlaylistDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}